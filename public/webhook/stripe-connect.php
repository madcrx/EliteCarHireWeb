<?php
/**
 * Stripe Connect Webhook Handler
 *
 * Handles webhook events from Stripe Connect for account status updates
 * This ensures the local database stays in sync with Stripe account statuses
 */

// Disable session for webhook (not needed)
define('NO_SESSION', true);

require __DIR__ . '/../../app/Database.php';
require __DIR__ . '/../../app/helpers.php';
require __DIR__ . '/../../app/helpers/stripe_helper.php';

// Set headers
header('Content-Type: application/json');

// Get the raw POST body
$payload = @file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

// Get webhook secret from config
$config = getStripeConfig();
$webhookSecret = $config['webhook_secret'] ?? '';

if (empty($webhookSecret)) {
    error_log('Stripe Connect webhook secret not configured');
    http_response_code(500);
    echo json_encode(['error' => 'Webhook not configured']);
    exit;
}

try {
    // Initialize Stripe
    initStripe();

    // Verify webhook signature
    $event = \Stripe\Webhook::constructEvent(
        $payload,
        $sigHeader,
        $webhookSecret
    );

    // Handle the event
    switch ($event->type) {
        case 'account.updated':
            handleAccountUpdated($event->data->object);
            break;

        case 'account.external_account.created':
        case 'account.external_account.updated':
            // External account (bank account) added/updated
            handleAccountUpdated($event->data->object->account);
            break;

        case 'capability.updated':
            // Capability status changed (charges_enabled, payouts_enabled, etc.)
            $accountId = $event->data->object->account;
            refreshAccountStatus($accountId);
            break;

        case 'transfer.created':
        case 'transfer.updated':
        case 'transfer.reversed':
        case 'transfer.failed':
            // Transfer events - log for monitoring
            handleTransferEvent($event);
            break;

        default:
            // Log unknown event types
            error_log("Unhandled Stripe Connect webhook event: {$event->type}");
    }

    // Return 200 to acknowledge receipt
    http_response_code(200);
    echo json_encode(['received' => true, 'event' => $event->type]);

} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    error_log('Stripe webhook signature verification failed: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);

} catch (Exception $e) {
    // Other errors
    error_log('Stripe Connect webhook error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Webhook processing failed']);
}

/**
 * Handle account.updated event
 * Syncs Stripe account status to local database
 */
function handleAccountUpdated($account) {
    try {
        // Find user by stripe_account_id
        $user = db()->fetch(
            "SELECT id FROM users WHERE stripe_account_id = ?",
            [$account->id]
        );

        if (!$user) {
            error_log("Stripe Connect webhook: User not found for account {$account->id}");
            return;
        }

        // Get account status
        $accountStatus = [
            'account_id' => $account->id,
            'details_submitted' => $account->details_submitted,
            'charges_enabled' => $account->charges_enabled,
            'payouts_enabled' => $account->payouts_enabled,
            'requirements' => $account->requirements->currently_due ?? [],
        ];

        // Update user record
        $success = updateUserStripeConnectStatus($user['id'], $accountStatus);

        if ($success) {
            error_log("Stripe Connect webhook: Updated status for user {$user['id']}, account {$account->id}");

            // If payouts were just enabled, notify the owner
            if ($account->payouts_enabled) {
                createNotification(
                    $user['id'],
                    'stripe_verified',
                    'Stripe Account Verified',
                    'Your Stripe account is now verified! You will receive automatic payouts for future bookings.'
                );
            }
        }

    } catch (Exception $e) {
        error_log("Error handling account.updated webhook: " . $e->getMessage());
    }
}

/**
 * Refresh account status from Stripe
 */
function refreshAccountStatus($accountId) {
    try {
        $accountStatus = getStripeConnectAccountStatus($accountId);

        if ($accountStatus) {
            $user = db()->fetch(
                "SELECT id FROM users WHERE stripe_account_id = ?",
                [$accountId]
            );

            if ($user) {
                updateUserStripeConnectStatus($user['id'], $accountStatus);
            }
        }

    } catch (Exception $e) {
        error_log("Error refreshing account status: " . $e->getMessage());
    }
}

/**
 * Handle transfer events (for monitoring and debugging)
 */
function handleTransferEvent($event) {
    $transfer = $event->data->object;
    $eventType = $event->type;

    try {
        // Find payout by stripe_transfer_id
        $payout = db()->fetch(
            "SELECT * FROM payouts WHERE stripe_transfer_id = ?",
            [$transfer->id]
        );

        if ($payout) {
            // Log transfer status changes
            error_log("Stripe Transfer {$transfer->id}: {$eventType} - Amount: " . ($transfer->amount / 100) . " AUD");

            // Handle failed transfers
            if ($eventType === 'transfer.failed') {
                db()->execute(
                    "UPDATE payouts SET
                        status = 'failed',
                        failure_code = ?,
                        failure_message = ?,
                        updated_at = NOW()
                     WHERE stripe_transfer_id = ?",
                    [
                        $transfer->failure_code ?? 'unknown',
                        $transfer->failure_message ?? 'Transfer failed',
                        $transfer->id
                    ]
                );

                // Notify admin
                $admins = db()->fetchAll("SELECT id FROM users WHERE role = 'admin'");
                foreach ($admins as $admin) {
                    createNotification(
                        $admin['id'],
                        'transfer_failed',
                        'Stripe Transfer Failed',
                        "Transfer {$transfer->id} failed: " . ($transfer->failure_message ?? 'Unknown error')
                    );
                }
            }

            // Handle reversed transfers
            if ($eventType === 'transfer.reversed') {
                db()->execute(
                    "UPDATE payouts SET
                        status = 'reversed',
                        notes = CONCAT(COALESCE(notes, ''), '\nTransfer reversed on " . date('Y-m-d H:i:s') . "'),
                        updated_at = NOW()
                     WHERE stripe_transfer_id = ?",
                    [$transfer->id]
                );
            }
        }

    } catch (Exception $e) {
        error_log("Error handling transfer event: " . $e->getMessage());
    }
}

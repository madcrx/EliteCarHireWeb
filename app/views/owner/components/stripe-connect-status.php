<?php
// Stripe Connect Status Component for Owner Dashboard
// Shows connection status and provides connect/manage buttons

require_once __DIR__ . '/../../helpers/stripe_helper.php';

// Get user's Stripe Connect status
$user = db()->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
$connectEnabled = isStripeConnectEnabled();
?>

<div class="card" style="margin-bottom: 2rem; border-left: 4px solid var(--primary-gold);">
    <h3 style="margin-top: 0; color: var(--primary-gold);">
        <i class="fas fa-credit-card"></i> Payment Settings
    </h3>

    <?php if (!$connectEnabled): ?>
        <!-- Connect Not Enabled -->
        <div style="padding: 1rem; background: #fff3cd; border-radius: var(--border-radius); border: 1px solid #ffc107;">
            <p style="margin: 0; color: #856404;">
                <i class="fas fa-info-circle"></i>
                <strong>Stripe Connect is not yet enabled.</strong> Contact the administrator to enable automatic payouts.
            </p>
        </div>

    <?php elseif (empty($user['stripe_account_id'])): ?>
        <!-- Not Connected - REQUIRED -->
        <div style="padding: 1.5rem; background: #fff3cd; border-radius: var(--border-radius); border: 2px solid #ff9800;">
            <h4 style="margin-top: 0; color: #e65100;">
                <i class="fas fa-exclamation-triangle"></i> ACTION REQUIRED: Connect Your Bank Account
            </h4>
            <p style="color: #e65100; margin-bottom: 1rem; font-weight: 600;">
                ⚠️ You MUST connect your Stripe account to confirm bookings and receive payments.
            </p>
            <p style="color: #5d4037; margin-bottom: 1rem;">
                <strong>Without Stripe Connect:</strong>
            </p>
            <ul style="color: #5d4037; margin-bottom: 1.5rem;">
                <li>❌ You cannot confirm bookings</li>
                <li>❌ Customers cannot pay for your vehicles</li>
                <li>❌ You will not receive any payouts</li>
            </ul>
            <p style="color: #1976d2; margin-bottom: 1.5rem;">
                <strong>After connecting:</strong> You'll receive <strong>85% of each booking</strong> paid weekly on Mondays,
                minimum 4 days after booking completion. All transfers are secure and direct to your bank account.
            </p>
            <a href="/owner/stripe/connect" class="btn btn-primary" style="font-size: 1.1rem; background: #ff9800; border-color: #ff9800;">
                <i class="fas fa-plus-circle"></i> Connect Stripe Account Now
            </a>
        </div>

    <?php elseif ($user['stripe_account_status'] === 'verified' && $user['stripe_payouts_enabled']): ?>
        <!-- Connected & Verified -->
        <div style="padding: 1.5rem; background: #e8f5e9; border-radius: var(--border-radius); border: 1px solid #4caf50;">
            <h4 style="margin-top: 0; color: #2e7d32;">
                <i class="fas fa-check-circle"></i> Account Connected & Verified
            </h4>
            <div style="margin-bottom: 1rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <p style="margin: 0.5rem 0; color: #1b5e20;">
                            <i class="fas fa-check" style="color: #4caf50;"></i>
                            <strong>Charges:</strong> Enabled
                        </p>
                        <p style="margin: 0.5rem 0; color: #1b5e20;">
                            <i class="fas fa-check" style="color: #4caf50;"></i>
                            <strong>Payouts:</strong> Enabled
                        </p>
                    </div>
                    <div>
                        <p style="margin: 0.5rem 0; color: #1b5e20;">
                            <i class="fas fa-check" style="color: #4caf50;"></i>
                            <strong>Details:</strong> Verified
                        </p>
                        <p style="margin: 0.5rem 0; color: #1b5e20;">
                            <i class="fas fa-check" style="color: #4caf50;"></i>
                            <strong>Status:</strong> Active
                        </p>
                    </div>
                </div>
            </div>
            <p style="color: #2e7d32; margin-bottom: 1rem; font-size: 0.9rem;">
                Your bank account is connected. You'll receive weekly payouts every <strong>Monday</strong> for bookings completed at least 4 days prior.
            </p>
            <a href="/owner/stripe/settings" class="btn" style="background: #4caf50; color: white;">
                <i class="fas fa-cog"></i> Manage Stripe Account
            </a>
        </div>

    <?php elseif ($user['stripe_account_status'] === 'pending'): ?>
        <!-- Pending Verification -->
        <div style="padding: 1.5rem; background: #fff3e0; border-radius: var(--border-radius); border: 1px solid #ff9800;">
            <h4 style="margin-top: 0; color: #e65100;">
                <i class="fas fa-clock"></i> Verification Pending
            </h4>
            <p style="color: #bf360c; margin-bottom: 1rem;">
                <?php if (!$user['stripe_details_submitted']): ?>
                    Your Stripe account setup is incomplete. Please complete the onboarding process to receive payouts.
                <?php else: ?>
                    Thank you for submitting your details. Stripe is reviewing your account. This usually takes 1-2 business days.
                <?php endif; ?>
            </p>

            <div style="margin-bottom: 1rem;">
                <p style="margin: 0.5rem 0; color: #e65100;">
                    <?php if ($user['stripe_details_submitted']): ?>
                        <i class="fas fa-check" style="color: #ff9800;"></i>
                    <?php else: ?>
                        <i class="fas fa-times" style="color: #f44336;"></i>
                    <?php endif; ?>
                    <strong>Details Submitted:</strong> <?= $user['stripe_details_submitted'] ? 'Yes' : 'No' ?>
                </p>
                <p style="margin: 0.5rem 0; color: #e65100;">
                    <?php if ($user['stripe_charges_enabled']): ?>
                        <i class="fas fa-check" style="color: #ff9800;"></i>
                    <?php else: ?>
                        <i class="fas fa-times" style="color: #f44336;"></i>
                    <?php endif; ?>
                    <strong>Charges Enabled:</strong> <?= $user['stripe_charges_enabled'] ? 'Yes' : 'No' ?>
                </p>
                <p style="margin: 0.5rem 0; color: #e65100;">
                    <?php if ($user['stripe_payouts_enabled']): ?>
                        <i class="fas fa-check" style="color: #ff9800;"></i>
                    <?php else: ?>
                        <i class="fas fa-times" style="color: #f44336;"></i>
                    <?php endif; ?>
                    <strong>Payouts Enabled:</strong> <?= $user['stripe_payouts_enabled'] ? 'Yes' : 'No' ?>
                </p>
            </div>

            <?php if (!$user['stripe_details_submitted']): ?>
                <a href="/owner/stripe/connect" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Complete Setup
                </a>
            <?php else: ?>
                <a href="/owner/stripe/settings" class="btn" style="background: #ff9800; color: white;">
                    <i class="fas fa-info-circle"></i> Check Status
                </a>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- Unknown/Error State -->
        <div style="padding: 1.5rem; background: #ffebee; border-radius: var(--border-radius); border: 1px solid #f44336;">
            <h4 style="margin-top: 0; color: #c62828;">
                <i class="fas fa-exclamation-triangle"></i> Account Issue
            </h4>
            <p style="color: #b71c1c; margin-bottom: 1rem;">
                There's an issue with your Stripe account. Please contact support or try reconnecting.
            </p>
            <a href="/owner/stripe/connect" class="btn" style="background: #f44336; color: white;">
                <i class="fas fa-redo"></i> Reconnect Account
            </a>
        </div>
    <?php endif; ?>

    <?php if ($connectEnabled): ?>
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--medium-gray);">
            <h4 style="margin-top: 0; font-size: 1rem; color: var(--dark-gray);">
                <i class="fas fa-info-circle"></i> How It Works
            </h4>
            <ul style="margin: 0; padding-left: 1.5rem; color: var(--dark-gray); font-size: 0.9rem;">
                <li><strong>Payment:</strong> Customers pay 100% upfront through Stripe</li>
                <li><strong>Commission:</strong> You receive <strong>85%</strong> of each booking (platform keeps 15%)</li>
                <li><strong>Schedule:</strong> Payouts are processed <strong>weekly on Mondays</strong></li>
                <li><strong>Timing:</strong> Bookings must be completed for at least <strong>4 days</strong> before payout</li>
                <li><strong>Transfer:</strong> Funds arrive in your bank account within 2-7 business days after Monday processing</li>
                <li><strong>Security:</strong> All transactions are secure and PCI compliant via Stripe</li>
                <li><strong>History:</strong> View payout details in your <a href="/owner/payouts" style="color: var(--primary-gold);">Payouts</a> page</li>
            </ul>
        </div>
    <?php endif; ?>
</div>

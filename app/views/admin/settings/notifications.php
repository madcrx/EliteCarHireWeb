<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/../sidebar.php'; ?>
    <div class="main-content">
        <h1><i class="fas fa-bell"></i> Notification Settings</h1>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
                <?= e($_SESSION['flash']['message']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <form method="POST" action="/admin/settings/notifications/save">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="card">
                <h2>Global Notification Channels</h2>
                <small style="color: var(--dark-gray); display: block; margin-bottom: 1.5rem;">
                    Enable or disable notification channels platform-wide
                </small>

                <div class="form-group" style="display: flex; align-items: center; gap: 1rem;">
                    <input type="checkbox" name="email_notifications_enabled" id="email_notifications_enabled"
                           value="1" <?= ($emailNotifications ?? '1') === '1' ? 'checked' : '' ?>
                           style="width: auto; margin: 0;">
                    <label for="email_notifications_enabled" style="margin: 0; cursor: pointer;">
                        <strong>Enable Email Notifications</strong>
                    </label>
                </div>
                <small style="display: block; margin-left: 2rem; color: var(--dark-gray);">
                    Send notifications via email. Requires Email Configuration to be set up.
                </small>

                <div class="form-group" style="display: flex; align-items: center; gap: 1rem; margin-top: 1.5rem;">
                    <input type="checkbox" name="sms_notifications_enabled" id="sms_notifications_enabled"
                           value="1" <?= ($smsNotifications ?? '0') === '1' ? 'checked' : '' ?>
                           style="width: auto; margin: 0;">
                    <label for="sms_notifications_enabled" style="margin: 0; cursor: pointer;">
                        <strong>Enable SMS Notifications</strong>
                    </label>
                </div>
                <small style="display: block; margin-left: 2rem; color: var(--dark-gray);">
                    Send notifications via SMS. Requires SMS gateway integration (future feature).
                </small>

                <hr>

                <h3 style="margin-top: 1.5rem;">Booking Notifications</h3>
                <small style="color: var(--dark-gray); display: block; margin-bottom: 1rem;">
                    Configure which booking events trigger notifications to customers and owners
                </small>

                <div class="form-group" style="display: flex; align-items: center; gap: 1rem;">
                    <input type="checkbox" name="notify_new_booking" id="notify_new_booking"
                           value="1" <?= ($notifyNewBooking ?? '1') === '1' ? 'checked' : '' ?>
                           style="width: auto; margin: 0;">
                    <label for="notify_new_booking" style="margin: 0; cursor: pointer;">
                        <strong>New Booking Created</strong>
                    </label>
                </div>
                <small style="display: block; margin-left: 2rem; color: var(--dark-gray);">
                    Notify owner when a customer creates a booking request
                </small>

                <div class="form-group" style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
                    <input type="checkbox" name="notify_booking_confirm" id="notify_booking_confirm"
                           value="1" <?= ($notifyBookingConfirm ?? '1') === '1' ? 'checked' : '' ?>
                           style="width: auto; margin: 0;">
                    <label for="notify_booking_confirm" style="margin: 0; cursor: pointer;">
                        <strong>Booking Confirmed</strong>
                    </label>
                </div>
                <small style="display: block; margin-left: 2rem; color: var(--dark-gray);">
                    Notify customer when owner confirms their booking
                </small>

                <div class="form-group" style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
                    <input type="checkbox" name="notify_booking_cancel" id="notify_booking_cancel"
                           value="1" <?= ($notifyBookingCancel ?? '1') === '1' ? 'checked' : '' ?>
                           style="width: auto; margin: 0;">
                    <label for="notify_booking_cancel" style="margin: 0; cursor: pointer;">
                        <strong>Booking Cancelled</strong>
                    </label>
                </div>
                <small style="display: block; margin-left: 2rem; color: var(--dark-gray);">
                    Notify both parties when a booking is cancelled
                </small>

                <hr>

                <h3 style="margin-top: 1.5rem;">Payment Notifications</h3>

                <div class="form-group" style="display: flex; align-items: center; gap: 1rem;">
                    <input type="checkbox" name="notify_payment_received" id="notify_payment_received"
                           value="1" <?= ($notifyPaymentReceived ?? '1') === '1' ? 'checked' : '' ?>
                           style="width: auto; margin: 0;">
                    <label for="notify_payment_received" style="margin: 0; cursor: pointer;">
                        <strong>Payment Received</strong>
                    </label>
                </div>
                <small style="display: block; margin-left: 2rem; color: var(--dark-gray);">
                    Notify customer when payment is successfully processed
                </small>

                <hr>

                <h3 style="margin-top: 1.5rem;">Vehicle Listing Notifications</h3>

                <div class="form-group" style="display: flex; align-items: center; gap: 1rem;">
                    <input type="checkbox" name="notify_new_vehicle" id="notify_new_vehicle"
                           value="1" <?= ($notifyNewVehicle ?? '1') === '1' ? 'checked' : '' ?>
                           style="width: auto; margin: 0;">
                    <label for="notify_new_vehicle" style="margin: 0; cursor: pointer;">
                        <strong>New Vehicle Submitted</strong>
                    </label>
                </div>
                <small style="display: block; margin-left: 2rem; color: var(--dark-gray);">
                    Notify admin when owner submits a new vehicle for approval
                </small>

                <hr>

                <h3 style="margin-top: 1.5rem;">Admin Notification Settings</h3>

                <div class="form-group">
                    <label for="admin_notification_email">Admin Notification Email Address *</label>
                    <input type="email" name="admin_notification_email" id="admin_notification_email"
                           value="<?= e($adminNotificationEmail ?? '') ?>" class="form-control"
                           placeholder="admin@elitecarhire.com.au" required>
                    <small>Email address to receive admin notifications (new vehicles, issues, etc.)</small>
                </div>

                <hr>

                <div style="margin-top: 2rem; padding: 1rem; background: #f7fafc; border-left: 4px solid var(--primary-gold);">
                    <strong>How Notifications Work:</strong>
                    <ul style="margin: 0.5rem 0 0 1rem;">
                        <li><strong>Customer creates booking</strong> → Owner receives "New Booking" email</li>
                        <li><strong>Owner confirms booking</strong> → Customer receives "Booking Confirmed" email</li>
                        <li><strong>Payment processed</strong> → Customer receives "Payment Received" email</li>
                        <li><strong>Booking cancelled</strong> → Both parties receive "Booking Cancelled" email</li>
                        <li><strong>New vehicle submitted</strong> → Admin receives notification at admin email address</li>
                    </ul>
                </div>

                <div style="margin-top: 1.5rem; padding: 1rem; background: #fff5f5; border-left: 4px solid #c53030;">
                    <strong>Important Notes:</strong>
                    <ul style="margin: 0.5rem 0 0 1rem;">
                        <li>Email notifications require Email Configuration to be completed first</li>
                        <li>Users can individually opt-out of notifications in their account settings</li>
                        <li>Critical notifications (password resets, security alerts) cannot be disabled</li>
                        <li>Test notifications before going live to ensure proper delivery</li>
                    </ul>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Notification Settings
                    </button>
                    <a href="/admin/dashboard" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../../layout.php'; ?>

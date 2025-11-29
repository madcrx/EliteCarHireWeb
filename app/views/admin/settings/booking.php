<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/../sidebar.php'; ?>
    <div class="main-content">
        <h1><i class="fas fa-calendar-alt"></i> Booking Settings</h1>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
                <?= e($_SESSION['flash']['message']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <form method="POST" action="/admin/settings/booking/save">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="card">
                <h2>Booking Time Constraints</h2>
                <small style="color: var(--dark-gray); display: block; margin-bottom: 1.5rem;">
                    Configure minimum and maximum booking durations to control how customers can book vehicles
                </small>

                <div class="form-group">
                    <label for="min_booking_hours">Minimum Booking Duration (hours) *</label>
                    <input type="number" name="min_booking_hours" id="min_booking_hours"
                           value="<?= e($minBookingHours ?? '4') ?>" class="form-control"
                           min="1" max="24" required>
                    <small>Shortest booking allowed (e.g., 4 hours minimum)</small>
                </div>

                <div class="form-group">
                    <label for="max_booking_days">Maximum Booking Duration (days) *</label>
                    <input type="number" name="max_booking_days" id="max_booking_days"
                           value="<?= e($maxBookingDays ?? '30') ?>" class="form-control"
                           min="1" max="365" required>
                    <small>Longest booking allowed (e.g., 30 days maximum)</small>
                </div>

                <div class="form-group">
                    <label for="advance_booking_days">Advance Booking Window (days) *</label>
                    <input type="number" name="advance_booking_days" id="advance_booking_days"
                           value="<?= e($advanceBookingDays ?? '90') ?>" class="form-control"
                           min="1" max="365" required>
                    <small>How far in advance customers can book (e.g., 90 days ahead)</small>
                </div>

                <hr>

                <h3 style="margin-top: 1.5rem;">Payment & Cancellation Policy</h3>

                <div style="background: #e7f3ff; border-left: 4px solid #0066cc; padding: 1rem; margin-bottom: 1.5rem;">
                    <strong>Payment Policy:</strong>
                    <p style="margin: 0.5rem 0 0 0;">Full payment is required at the time of booking confirmation.</p>
                </div>

                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem; margin-bottom: 1.5rem;">
                    <strong>Cancellation Policy:</strong>
                    <p style="margin: 0.5rem 0 0 0;">All cancellations are subject to a 50% cancellation fee, regardless of notice period.</p>
                </div>

                <hr>

                <h3 style="margin-top: 1.5rem;">Booking Workflow</h3>

                <div class="form-group" style="display: flex; align-items: center; gap: 1rem;">
                    <input type="checkbox" name="auto_confirm_bookings" id="auto_confirm_bookings"
                           value="1" <?= ($autoConfirmBookings ?? '0') === '1' ? 'checked' : '' ?>
                           style="width: auto; margin: 0;">
                    <label for="auto_confirm_bookings" style="margin: 0; cursor: pointer;">
                        <strong>Auto-Confirm Bookings</strong>
                    </label>
                </div>
                <small style="display: block; margin-left: 2rem; color: var(--dark-gray);">
                    When enabled, bookings are automatically confirmed without owner approval. When disabled, owners must manually confirm each booking.
                </small>

                <hr>

                <div style="margin-top: 2rem; padding: 1rem; background: #f7fafc; border-left: 4px solid var(--primary-gold);">
                    <strong>Example Booking Flow:</strong>
                    <ol style="margin: 0.5rem 0 0 1.5rem;">
                        <li>Customer selects vehicle and dates (within advance booking window)</li>
                        <li>Booking duration must be between minimum and maximum hours/days</li>
                        <li>Customer pays full amount (100% payment required)</li>
                        <li>If auto-confirm is OFF: Owner reviews and confirms booking</li>
                        <li>If auto-confirm is ON: Booking is immediately confirmed</li>
                        <li>If cancelled, 50% cancellation fee applies</li>
                    </ol>
                </div>

                <div style="margin-top: 1.5rem; padding: 1rem; background: #fffbf0; border-left: 4px solid #d69e2e;">
                    <strong>Recommendations:</strong>
                    <ul style="margin: 0.5rem 0 0 1rem;">
                        <li><strong>Minimum booking:</strong> 4 hours prevents very short, unprofitable bookings</li>
                        <li><strong>Maximum booking:</strong> 30 days allows long-term rentals but prevents indefinite holds</li>
                        <li><strong>Advance window:</strong> 90 days balances planning flexibility with calendar accuracy</li>
                        <li><strong>Auto-confirm:</strong> Disable for premium vehicles that need owner approval</li>
                    </ul>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Booking Settings
                    </button>
                    <a href="/admin/dashboard" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../../layout.php'; ?>

<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1><i class="fas fa-calendar-check"></i> Bookings</h1>
        <p style="color: var(--dark-gray); margin-bottom: 2rem;">
            Manage all bookings for your vehicles.
        </p>

        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="margin-bottom: 0;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-gray);">Filter by Status:</label>
                <a href="/owner/bookings?status=all" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                <a href="/owner/bookings?status=pending" class="btn <?= $status === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
                <a href="/owner/bookings?status=confirmed" class="btn <?= $status === 'confirmed' ? 'btn-primary' : 'btn-secondary' ?>">Confirmed</a>
                <a href="/owner/bookings?status=in_progress" class="btn <?= $status === 'in_progress' ? 'btn-primary' : 'btn-secondary' ?>">In Progress</a>
                <a href="/owner/bookings?status=completed" class="btn <?= $status === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Completed</a>
                <a href="/owner/bookings?status=cancelled" class="btn <?= $status === 'cancelled' ? 'btn-primary' : 'btn-secondary' ?>">Cancelled</a>
            </div>
        </div>

        <?php if (empty($bookings)): ?>
            <div class="card" style="text-align: center; background: var(--light-gray);">
                <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 1rem;"></i>
                <h3>No Bookings Yet</h3>
                <p>You don't have any bookings at the moment. Customers will be able to book your approved vehicles.</p>
                <a href="/owner/listings" class="btn btn-primary" style="margin-top: 1rem;">View My Listings</a>
            </div>
        <?php else: ?>
            <div class="card">
                <h2><i class="fas fa-list"></i> All Bookings</h2>
                <p style="margin-bottom: 1.5rem; color: var(--dark-gray);">
                    Complete history of all bookings for your vehicles.
                </p>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Booking Ref</th>
                                <th>Vehicle</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Duration</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><strong><?= e($booking['booking_reference']) ?></strong></td>
                                    <td>
                                        <?= e($booking['make'] . ' ' . $booking['model']) ?>
                                    </td>
                                    <td><?= e($booking['first_name'] . ' ' . $booking['last_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                                    <td><?= date('g:i A', strtotime($booking['start_time'])) ?> - <?= date('g:i A', strtotime($booking['end_time'])) ?></td>
                                    <td><?= $booking['duration_hours'] ?> hrs</td>
                                    <td style="font-weight: bold; color: var(--success);">$<?= number_format($booking['total_amount'], 2) ?></td>
                                    <td>
                                        <?php
                                            $statusClass = match($booking['status']) {
                                                'confirmed' => 'success',
                                                'in_progress' => 'warning',
                                                'completed' => 'info',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>
                                        <span class="badge badge-<?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $booking['status'])) ?></span>
                                    </td>
                                    <td>
                                        <?php
                                            $paymentClass = match($booking['payment_status']) {
                                                'paid' => 'success',
                                                'pending' => 'warning',
                                                'refunded' => 'secondary',
                                                'failed' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>
                                        <span class="badge badge-<?= $paymentClass ?>"><?= ucfirst($booking['payment_status']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($booking['status'] === 'pending'): ?>
                                            <form method="POST" action="/owner/bookings/confirm" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                <button type="submit" class="btn btn-success" style="padding: 5px 15px;"
                                                        onclick="return confirm('Confirm this booking?')">
                                                    <i class="fas fa-check"></i> Confirm
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if (in_array($booking['status'], ['pending', 'confirmed', 'in_progress'])): ?>
                                            <button class="btn btn-danger" style="padding: 5px 15px;"
                                                    onclick="showCancelModal(<?= $booking['id'] ?>, '<?= e($booking['booking_reference']) ?>')">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card" style="background: var(--light-gray);">
                <h3><i class="fas fa-info-circle"></i> Booking Information</h3>
                <ul>
                    <li><strong>Confirmed:</strong> Booking is confirmed and vehicle is reserved</li>
                    <li><strong>In Progress:</strong> Booking is currently active</li>
                    <li><strong>Completed:</strong> Booking has been completed successfully</li>
                    <li><strong>Cancelled:</strong> Booking was cancelled</li>
                    <li><strong>Commission:</strong> 15% platform fee applies to all bookings</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Cancel Booking Modal -->
<div id="cancelModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: var(--border-radius); max-width: 500px; width: 90%;">
        <h2 style="margin-top: 0;">Cancel Booking</h2>
        <p style="color: var(--dark-gray); margin-bottom: 1.5rem;">
            This will request admin approval to cancel booking <strong id="cancelBookingRef"></strong>.
        </p>

        <form method="POST" action="/owner/bookings/cancel" id="cancelForm">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="booking_id" id="cancelBookingId">

            <div class="form-group">
                <label for="cancellation_reason">Cancellation Reason *</label>
                <textarea name="cancellation_reason" id="cancellation_reason" rows="4" required
                          placeholder="Please provide a reason for cancelling this booking..."></textarea>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">
                    Cancel
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times"></i> Submit Cancellation Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showCancelModal(bookingId, bookingRef) {
    document.getElementById('cancelBookingId').value = bookingId;
    document.getElementById('cancelBookingRef').textContent = bookingRef;
    document.getElementById('cancelModal').style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
    document.getElementById('cancellation_reason').value = '';
}

// Close modal on outside click
document.getElementById('cancelModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeCancelModal();
    }
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

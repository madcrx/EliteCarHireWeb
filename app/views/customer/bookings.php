<?php ob_start(); ?>
<style>
    /* Mobile responsive table */
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 0 -1rem;
        padding: 0 1rem;
    }

    @media (max-width: 768px) {
        .table-container {
            width: 100%;
            overflow-x: scroll;
        }

        .table-container table {
            min-width: 800px; /* Ensure table is wide enough to show all columns */
        }

        /* Make action column sticky on mobile for better UX */
        .table-container table th:last-child,
        .table-container table td:last-child {
            position: sticky;
            right: 0;
            background: white;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            z-index: 10;
        }

        .table-container table thead th:last-child {
            background: var(--primary-color, #333);
        }

        /* Highlight awaiting approval rows on mobile */
        .table-container table tr[style*="background-color: #fff3cd"] td:last-child {
            background-color: #fff3cd;
        }

        /* Show scroll hint */
        .table-container::after {
            content: "← Swipe to see all columns →";
            display: block;
            text-align: center;
            font-size: 0.85rem;
            color: #666;
            padding: 0.5rem;
            margin-top: 0.5rem;
            font-style: italic;
        }
    }

    @media (min-width: 769px) {
        .table-container::after {
            display: none;
        }
    }
</style>

<div class="container dashboard">
    <h1>My Bookings</h1>

    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="margin-bottom: 0;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-gray);">Filter by Status:</label>
            <a href="/customer/bookings?status=all" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
            <a href="/customer/bookings?status=pending" class="btn <?= $status === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
            <a href="/customer/bookings?status=awaiting_approval" class="btn <?= $status === 'awaiting_approval' ? 'btn-primary' : 'btn-secondary' ?>" style="<?= $status === 'awaiting_approval' ? '' : 'background: #ff9800; border-color: #ff9800; color: white;' ?>">Needs Approval</a>
            <a href="/customer/bookings?status=confirmed" class="btn <?= $status === 'confirmed' ? 'btn-primary' : 'btn-secondary' ?>">Confirmed</a>
            <a href="/customer/bookings?status=in_progress" class="btn <?= $status === 'in_progress' ? 'btn-primary' : 'btn-secondary' ?>">In Progress</a>
            <a href="/customer/bookings?status=completed" class="btn <?= $status === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Completed</a>
            <a href="/customer/bookings?status=cancelled" class="btn <?= $status === 'cancelled' ? 'btn-primary' : 'btn-secondary' ?>">Cancelled</a>
        </div>
    </div>

    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Vehicle</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking):
                        // Determine status badge color
                        $statusBadgeClass = 'info';
                        if ($booking['status'] === 'completed') $statusBadgeClass = 'success';
                        elseif ($booking['status'] === 'awaiting_approval') $statusBadgeClass = 'warning';
                        elseif ($booking['status'] === 'cancelled') $statusBadgeClass = 'danger';

                        // Format status display
                        $statusDisplay = str_replace('_', ' ', ucfirst($booking['status']));
                    ?>
                        <tr style="<?= $booking['status'] === 'awaiting_approval' ? 'background-color: #fff3cd;' : '' ?>">
                            <td><?= e($booking['booking_reference']) ?></td>
                            <td><?= e($booking['make'] . ' ' . $booking['model'] . ' (' . $booking['year'] . ')') ?></td>
                            <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                            <td><?= date('g:i A', strtotime($booking['start_time'])) ?> - <?= date('g:i A', strtotime($booking['end_time'])) ?></td>
                            <td>
                                <?= formatMoney($booking['total_amount']) ?>
                                <?php if ($booking['status'] === 'awaiting_approval' && $booking['additional_charges'] > 0): ?>
                                    <br><small style="color: #856404;">Base: <?= formatMoney($booking['base_amount']) ?><br>+Extra: <?= formatMoney($booking['additional_charges']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-<?= $statusBadgeClass ?>"><?= $statusDisplay ?></span></td>
                            <td><span class="badge badge-<?= $booking['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($booking['payment_status']) ?></span></td>
                            <td>
                                <?php if ($booking['status'] === 'awaiting_approval'): ?>
                                    <button onclick="showApprovalModal(<?= $booking['id'] ?>, '<?= e($booking['booking_reference']) ?>', '<?= e($booking['make'] . ' ' . $booking['model']) ?>', <?= $booking['base_amount'] ?>, <?= $booking['additional_charges'] ?>, <?= $booking['total_amount'] ?>, '<?= e($booking['additional_charges_reason'] ?? '') ?>')"
                                            class="btn btn-sm" style="background: #ff9800; color: white; border-color: #ff9800;">
                                        <i class="fas fa-check-circle"></i> Review & Approve
                                    </button>
                                <?php elseif ($booking['status'] === 'confirmed' && $booking['payment_status'] !== 'paid'): ?>
                                    <a href="/customer/bookings/<?= $booking['id'] ?>" class="btn btn-sm btn-primary">
                                        Pay Now
                                    </a>
                                <?php else: ?>
                                    <a href="/customer/bookings/<?= $booking['id'] ?>" class="btn btn-sm btn-secondary">
                                        View Details
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Approval Modal -->
    <div id="approvalModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
            <h2 style="margin-top: 0; color: #333;"><i class="fas fa-exclamation-circle" style="color: #ff9800;"></i> Booking Update Review</h2>

            <div style="background: #fff3cd; border-left: 4px solid #ff9800; padding: 1rem; margin-bottom: 1.5rem;">
                <strong>Action Required:</strong> The owner has updated your booking with additional charges. Please review and approve to proceed.
            </div>

            <div style="margin-bottom: 1.5rem;">
                <p style="margin-bottom: 0.5rem;"><strong>Booking Reference:</strong> <span id="approval_booking_ref"></span></p>
                <p style="margin-bottom: 0.5rem;"><strong>Vehicle:</strong> <span id="approval_vehicle_name"></span></p>
            </div>

            <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                <h3 style="margin-top: 0; font-size: 1.1rem;">Price Breakdown</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 0.5rem 0; border-bottom: 1px solid #dee2e6;">Original Booking Amount:</td>
                        <td style="padding: 0.5rem 0; border-bottom: 1px solid #dee2e6; text-align: right; font-weight: 500;">$<span id="approval_base_amount"></span></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; border-bottom: 1px solid #dee2e6; color: #ff9800; font-weight: 600;">Additional Charges:</td>
                        <td style="padding: 0.5rem 0; border-bottom: 1px solid #dee2e6; text-align: right; font-weight: 600; color: #ff9800;">+$<span id="approval_additional_charges"></span></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.75rem 0; font-size: 1.2rem; font-weight: bold;">New Total Amount:</td>
                        <td style="padding: 0.75rem 0; text-align: right; font-size: 1.2rem; font-weight: bold; color: #28a745;">$<span id="approval_total_amount"></span></td>
                    </tr>
                </table>
            </div>

            <div style="background: #e7f3ff; border-left: 4px solid #0066cc; padding: 1rem; margin-bottom: 1.5rem;">
                <h4 style="margin-top: 0; font-size: 1rem;">Reason for Additional Charges:</h4>
                <p style="margin: 0; white-space: pre-wrap;" id="approval_reason"></p>
            </div>

            <form method="POST" action="/customer/bookings/approve" id="approvalForm" style="margin-bottom: 1rem;">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="booking_id" id="approval_booking_id">

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-bottom: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeApprovalModal()">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger" onclick="rejectBooking()">
                        <i class="fas fa-times"></i> Reject Changes
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Approve & Proceed to Payment
                    </button>
                </div>
            </form>

            <div style="background: #fffbea; border: 1px solid #ffd700; padding: 1rem; border-radius: 4px; font-size: 0.9rem;">
                <strong>Note:</strong> By approving, you agree to the updated total amount and will be redirected to complete payment.
            </div>
        </div>
    </div>
</div>

<script>
function showApprovalModal(bookingId, bookingRef, vehicleName, baseAmount, additionalCharges, totalAmount, reason) {
    document.getElementById('approval_booking_id').value = bookingId;
    document.getElementById('approval_booking_ref').textContent = bookingRef;
    document.getElementById('approval_vehicle_name').textContent = vehicleName;
    document.getElementById('approval_base_amount').textContent = parseFloat(baseAmount).toFixed(2);
    document.getElementById('approval_additional_charges').textContent = parseFloat(additionalCharges).toFixed(2);
    document.getElementById('approval_total_amount').textContent = parseFloat(totalAmount).toFixed(2);
    document.getElementById('approval_reason').textContent = reason || 'No reason provided';

    document.getElementById('approvalModal').style.display = 'flex';
}

function closeApprovalModal() {
    document.getElementById('approvalModal').style.display = 'none';
    document.getElementById('approvalForm').reset();
}

function rejectBooking() {
    if (confirm('Are you sure you want to reject these changes? This will cancel the booking.')) {
        const bookingId = document.getElementById('approval_booking_id').value;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/customer/bookings/reject';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= csrfToken() ?>';

        const bookingInput = document.createElement('input');
        bookingInput.type = 'hidden';
        bookingInput.name = 'booking_id';
        bookingInput.value = bookingId;

        form.appendChild(csrfInput);
        form.appendChild(bookingInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modal on outside click
document.getElementById('approvalModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeApprovalModal();
    }
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

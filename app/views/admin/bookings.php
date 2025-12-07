<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="dashboard-header">
            <h1>All Bookings</h1>
        </div>

        <div class="card">
            <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-gray);">Booking Status:</label>
                    <a href="/admin/bookings?status=all&payment_status=<?= $paymentStatus ?>" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                    <a href="/admin/bookings?status=pending&payment_status=<?= $paymentStatus ?>" class="btn <?= $status === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
                    <a href="/admin/bookings?status=confirmed&payment_status=<?= $paymentStatus ?>" class="btn <?= $status === 'confirmed' ? 'btn-primary' : 'btn-secondary' ?>">Confirmed</a>
                    <a href="/admin/bookings?status=completed&payment_status=<?= $paymentStatus ?>" class="btn <?= $status === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Completed</a>
                    <a href="/admin/bookings?status=cancelled&payment_status=<?= $paymentStatus ?>" class="btn <?= $status === 'cancelled' ? 'btn-primary' : 'btn-secondary' ?>">Cancelled</a>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-gray);">Payment Status:</label>
                    <a href="/admin/bookings?status=<?= $status ?>&payment_status=all" class="btn <?= $paymentStatus === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                    <a href="/admin/bookings?status=<?= $status ?>&payment_status=pending" class="btn <?= $paymentStatus === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
                    <a href="/admin/bookings?status=<?= $status ?>&payment_status=paid" class="btn <?= $paymentStatus === 'paid' ? 'btn-primary' : 'btn-secondary' ?>">Paid</a>
                    <a href="/admin/bookings?status=<?= $status ?>&payment_status=refunded" class="btn <?= $paymentStatus === 'refunded' ? 'btn-primary' : 'btn-secondary' ?>">Refunded</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Owner</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Commission</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= e($booking['booking_reference']) ?></td>
                                <td><?= e($booking['customer_name'] . ' ' . $booking['customer_last']) ?></td>
                                <td><?= e($booking['make'] . ' ' . $booking['model']) ?></td>
                                <td><?= e($booking['owner_name'] . ' ' . $booking['owner_last']) ?></td>
                                <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                                <td><?= formatMoney($booking['total_amount']) ?></td>
                                <td><?= formatMoney($booking['commission_amount']) ?></td>
                                <td><span class="badge badge-<?= $booking['status'] === 'completed' ? 'success' : 'info' ?>"><?= ucfirst($booking['status']) ?></span></td>
                                <td><span class="badge badge-<?= $booking['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($booking['payment_status']) ?></span></td>
                                <td>
                                    <form method="POST" action="/admin/bookings/<?= $booking['id'] ?>/delete" style="display: inline;" onsubmit="return confirmDeleteBooking('<?= e($booking['booking_reference']) ?>', '<?= e($booking['status']) ?>', '<?= e($booking['payment_status']) ?>');">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <button type="submit" class="btn" style="padding: 5px 10px; background: var(--danger); color: white;" title="Delete Booking">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDeleteBooking(bookingRef, status, paymentStatus) {
    var isPaidOrCompleted = (paymentStatus === 'paid' || status === 'completed');
    var warningLevel = isPaidOrCompleted ? '🔴 CRITICAL WARNING' : '⚠️ WARNING';
    var extraWarning = isPaidOrCompleted ? '\n\n🔴 THIS IS A PAID/COMPLETED BOOKING!\n• Payment records will be PERMANENTLY deleted\n• Financial audit trail will be LOST\n• This may affect accounting and tax records' : '';

    // First confirmation
    if (!confirm(warningLevel + ': You are about to permanently delete booking ' + bookingRef + '.\n\n' +
                 'Status: ' + status.toUpperCase() + '\n' +
                 'Payment Status: ' + paymentStatus.toUpperCase() +
                 extraWarning + '\n\n' +
                 'This will DELETE:\n' +
                 '• The booking record\n' +
                 '• All payment records\n' +
                 '• Financial history\n\n' +
                 'This action CANNOT be undone!\n\n' +
                 'Click OK to proceed to confirmation step.')) {
        return false;
    }

    // Second confirmation - require typing DELETE
    var confirmText = isPaidOrCompleted ? 'DELETE PAID BOOKING' : 'DELETE';
    var confirmation = prompt('⚠️ FINAL CONFIRMATION REQUIRED\n\n' +
                             'To confirm deletion of booking ' + bookingRef + ', please type "' + confirmText + '" exactly:');

    if (confirmation === confirmText) {
        return true;
    } else {
        alert('Deletion cancelled. You must type "' + confirmText + '" exactly to confirm.');
        return false;
    }
}
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

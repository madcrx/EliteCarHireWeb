<?php ob_start(); ?>
<div class="container dashboard">
    <h1>My Bookings</h1>

    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="margin-bottom: 0;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-gray);">Filter by Status:</label>
            <a href="/customer/bookings?status=all" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
            <a href="/customer/bookings?status=pending" class="btn <?= $status === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= e($booking['booking_reference']) ?></td>
                            <td><?= e($booking['make'] . ' ' . $booking['model'] . ' (' . $booking['year'] . ')') ?></td>
                            <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                            <td><?= date('g:i A', strtotime($booking['start_time'])) ?> - <?= date('g:i A', strtotime($booking['end_time'])) ?></td>
                            <td><?= formatMoney($booking['total_amount']) ?></td>
                            <td><span class="badge badge-<?= $booking['status'] === 'completed' ? 'success' : 'info' ?>"><?= ucfirst($booking['status']) ?></span></td>
                            <td><span class="badge badge-<?= $booking['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($booking['payment_status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

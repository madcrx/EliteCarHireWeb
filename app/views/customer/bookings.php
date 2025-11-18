<?php ob_start(); ?>
<div class="container dashboard">
    <h1>My Bookings</h1>
    
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

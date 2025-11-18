<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="dashboard-header">
            <h1>All Bookings</h1>
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
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

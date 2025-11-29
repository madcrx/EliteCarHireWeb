<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Payment Logs</h1>

        <div class="card">
            <?php if (empty($paymentLogs)): ?>
                <p><strong>No payment logs available.</strong></p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Booking Ref</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paymentLogs as $log): ?>
                            <tr>
                                <td>#<?= $log['id'] ?></td>
                                <td><?= htmlspecialchars($log['booking_reference'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($log['customer_email'] ?? 'N/A') ?></td>
                                <td>$<?= number_format($log['amount'], 2) ?></td>
                                <td><span class="badge badge-<?= $log['status'] ?>"><?= $log['status'] ?></span></td>
                                <td><?= date('Y-m-d H:i', strtotime($log['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

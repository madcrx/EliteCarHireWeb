<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Payments</h1>

        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="margin-bottom: 0;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-gray);">Filter by Status:</label>
                <a href="/admin/payments?status=all" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                <a href="/admin/payments?status=completed" class="btn <?= $status === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Completed</a>
                <a href="/admin/payments?status=pending" class="btn <?= $status === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
                <a href="/admin/payments?status=failed" class="btn <?= $status === 'failed' ? 'btn-primary' : 'btn-secondary' ?>">Failed</a>
            </div>
        </div>

        <?php if (empty($payments)): ?>
            <div class="card">
                <p>No payment records found.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Booking Ref</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($payment['created_at'])) ?></td>
                                <td><?= e($payment['booking_reference']) ?></td>
                                <td><?= e($payment['customer_name']) ?></td>
                                <td>$<?= number_format($payment['amount'], 2) ?></td>
                                <td><?= ucfirst(e($payment['payment_method'])) ?></td>
                                <td>
                                    <?php if ($payment['status'] === 'completed'): ?>
                                        <span class="badge badge-success">Completed</span>
                                    <?php elseif ($payment['status'] === 'pending'): ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Failed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

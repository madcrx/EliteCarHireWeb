<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Payouts</h1>

        <?php if (empty($payouts)): ?>
            <div class="card">
                <p>No payout records found.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Owner</th>
                            <th>Amount</th>
                            <th>Period</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payouts as $payout): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($payout['created_at'])) ?></td>
                                <td><?= e($payout['owner_name']) ?></td>
                                <td>$<?= number_format($payout['amount'], 2) ?></td>
                                <td><?= e($payout['period_start']) ?> to <?= e($payout['period_end']) ?></td>
                                <td>
                                    <?php if ($payout['status'] === 'paid'): ?>
                                        <span class="badge badge-success">Paid</span>
                                    <?php elseif ($payout['status'] === 'pending'): ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Processing</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($payout['status'] === 'pending'): ?>
                                        <button class="btn btn-primary btn-sm">Process</button>
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

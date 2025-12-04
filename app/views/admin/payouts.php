<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Payouts</h1>

        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="margin-bottom: 0;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-gray);">Filter by Status:</label>
                <a href="/admin/payouts?status=all" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                <a href="/admin/payouts?status=pending" class="btn <?= $status === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
                <a href="/admin/payouts?status=processing" class="btn <?= $status === 'processing' ? 'btn-primary' : 'btn-secondary' ?>">Processing</a>
                <a href="/admin/payouts?status=completed" class="btn <?= $status === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Completed</a>
            </div>
        </div>

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
                                    <?php if ($payout['status'] === 'completed'): ?>
                                        <span class="badge badge-success">Completed</span>
                                    <?php elseif ($payout['status'] === 'pending'): ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php elseif ($payout['status'] === 'processing'): ?>
                                        <span class="badge badge-info">Processing</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><?= ucfirst($payout['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($payout['status'] === 'pending'): ?>
                                        <form method="POST" action="/admin/payouts/<?= $payout['id'] ?>/process" style="display: inline-block;" onsubmit="return confirm('Mark this payout as completed? This action cannot be undone.');">
                                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                            <button type="submit" class="btn btn-primary btn-sm">Process</button>
                                        </form>
                                    <?php elseif ($payout['status'] === 'completed'): ?>
                                        <span style="color: var(--success); font-weight: 600;">
                                            <i class="fas fa-check-circle"></i> Paid
                                        </span>
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

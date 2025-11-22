<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1><i class="fas fa-money-check-alt"></i> Payouts</h1>
        <p style="color: var(--dark-gray); margin-bottom: 2rem;">
            Track your earnings and payment history.
        </p>

        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="margin-bottom: 0;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-gray);">Filter by Status:</label>
                <a href="/owner/payouts?status=all" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                <a href="/owner/payouts?status=pending" class="btn <?= $status === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
                <a href="/owner/payouts?status=scheduled" class="btn <?= $status === 'scheduled' ? 'btn-primary' : 'btn-secondary' ?>">Scheduled</a>
                <a href="/owner/payouts?status=processing" class="btn <?= $status === 'processing' ? 'btn-primary' : 'btn-secondary' ?>">Processing</a>
                <a href="/owner/payouts?status=completed" class="btn <?= $status === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Completed</a>
            </div>
        </div>

        <?php if (empty($payouts)): ?>
            <div class="card" style="text-align: center; background: var(--light-gray);">
                <i class="fas fa-wallet" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 1rem;"></i>
                <h3>No Payouts Yet</h3>
                <p>Complete bookings to start receiving payouts.</p>
                <a href="/owner/dashboard" class="btn btn-primary" style="margin-top: 1rem;">Return to Dashboard</a>
            </div>
        <?php else: ?>
            <div class="card">
                <h2><i class="fas fa-list"></i> Payout History</h2>
                <p style="margin-bottom: 1.5rem; color: var(--dark-gray);">
                    All your payouts are listed below. Payouts are typically processed within 5-7 business days.
                </p>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Scheduled Date</th>
                                <th>Payout Date</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payouts as $payout): ?>
                                <tr>
                                    <td><strong><?= e($payout['reference']) ?></strong></td>
                                    <td style="color: var(--success); font-weight: bold;">$<?= number_format($payout['amount'], 2) ?></td>
                                    <td>
                                        <?php
                                            $statusClass = match($payout['status']) {
                                                'completed' => 'success',
                                                'processing' => 'warning',
                                                'scheduled' => 'info',
                                                'pending' => 'secondary',
                                                default => 'secondary'
                                            };
                                        ?>
                                        <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($payout['status']) ?></span>
                                    </td>
                                    <td><?= $payout['scheduled_date'] ? date('M d, Y', strtotime($payout['scheduled_date'])) : '-' ?></td>
                                    <td><?= $payout['payout_date'] ? date('M d, Y', strtotime($payout['payout_date'])) : '-' ?></td>
                                    <td><?= date('M d, Y', strtotime($payout['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; background: var(--light-gray);">
                                <td>Total Paid</td>
                                <td style="color: var(--success);" colspan="5">
                                    $<?= number_format(array_sum(array_map(fn($p) => $p['status'] === 'completed' ? $p['amount'] : 0, $payouts)), 2) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card" style="background: var(--light-gray);">
                <h3><i class="fas fa-info-circle"></i> Payout Information</h3>
                <ul>
                    <li><strong>Processing Time:</strong> Payouts are typically processed within 5-7 business days</li>
                    <li><strong>Commission:</strong> A 15% platform commission is deducted from each booking</li>
                    <li><strong>Payment Method:</strong> Payouts are made via bank transfer to your registered account</li>
                    <li><strong>Tax Reporting:</strong> Keep records of all payouts for tax purposes</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

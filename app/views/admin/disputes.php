<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Disputes</h1>

        <?php if (empty($disputes)): ?>
            <div class="card">
                <p>No disputes to review.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Booking Ref</th>
                            <th>Raised By</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($disputes as $dispute): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($dispute['created_at'])) ?></td>
                                <td><?= e($dispute['booking_reference']) ?></td>
                                <td><?= e($dispute['raised_by']) ?></td>
                                <td><?= e($dispute['reason']) ?></td>
                                <td>
                                    <?php if ($dispute['status'] === 'resolved'): ?>
                                        <span class="badge badge-success">Resolved</span>
                                    <?php elseif ($dispute['status'] === 'pending'): ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">In Review</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($dispute['status'] !== 'resolved'): ?>
                                        <button class="btn btn-primary btn-sm">Review</button>
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

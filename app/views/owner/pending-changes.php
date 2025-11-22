<?php ob_start(); ?>
<div class="container" style="padding: 2rem 0;">
    <h1>My Pending Changes</h1>

    <?php if (empty($changes)): ?>
        <div class="card">
            <p>You have no pending changes awaiting approval.</p>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date Submitted</th>
                        <th>Entity</th>
                        <th>Change Type</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($changes as $change): ?>
                        <tr>
                            <td><?= date('M d, Y H:i', strtotime($change['created_at'])) ?></td>
                            <td><?= ucfirst(e($change['entity_type'])) ?></td>
                            <td><?= ucfirst(e($change['change_type'])) ?></td>
                            <td>
                                <?php if ($change['status'] === 'pending'): ?>
                                    <span class="badge badge-warning">Pending Review</span>
                                <?php elseif ($change['status'] === 'approved'): ?>
                                    <span class="badge badge-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($change['admin_notes'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

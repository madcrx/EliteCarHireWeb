<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Pending Changes</h1>

        <?php if (empty($changes)): ?>
            <div class="card">
                <p>No pending changes to review.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Owner</th>
                            <th>Entity Type</th>
                            <th>Change Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($changes as $change): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($change['created_at'])) ?></td>
                                <td><?= e($change['first_name'] . ' ' . $change['last_name']) ?></td>
                                <td><?= ucfirst(e($change['entity_type'])) ?></td>
                                <td><?= ucfirst(e($change['change_type'])) ?></td>
                                <td><span class="badge badge-warning"><?= ucfirst(e($change['status'])) ?></span></td>
                                <td>
                                    <form method="POST" action="/admin/pending-changes/<?= $change['id'] ?>/approve" style="display: inline;">
                                        <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                                    </form>
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

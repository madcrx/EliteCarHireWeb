<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Audit Logs</h1>

        <?php if (empty($logs)): ?>
            <div class="card">
                <p>No audit logs found.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></td>
                                <td><?= e($log['user_name'] ?? 'System') ?></td>
                                <td><?= e($log['action']) ?></td>
                                <td><?= e($log['entity_type']) ?></td>
                                <td><?= e($log['ip_address']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

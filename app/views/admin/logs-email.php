<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Email Logs</h1>

        <div class="card">
            <?php if (empty($emailLogs)): ?>
                <p><strong>No email logs available.</strong></p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>To</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Queued</th>
                            <th>Sent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emailLogs as $log): ?>
                            <tr>
                                <td>#<?= $log['id'] ?></td>
                                <td><?= htmlspecialchars($log['to_email']) ?></td>
                                <td><?= htmlspecialchars($log['subject']) ?></td>
                                <td><span class="badge badge-<?= $log['status'] ?>"><?= $log['status'] ?></span></td>
                                <td><?= date('Y-m-d H:i', strtotime($log['created_at'])) ?></td>
                                <td><?= $log['sent_at'] ? date('Y-m-d H:i', strtotime($log['sent_at'])) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

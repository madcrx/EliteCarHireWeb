<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Email Queue</h1>

        <div class="card">
            <?php if (empty($emails)): ?>
                <p><strong>No emails in queue.</strong></p>
                <p>Emails will appear here when queued for sending.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>To</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Sent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emails as $email): ?>
                            <tr>
                                <td><?= $email['id'] ?></td>
                                <td><?= htmlspecialchars($email['to_email']) ?></td>
                                <td><?= htmlspecialchars($email['subject']) ?></td>
                                <td><span class="badge badge-<?= $email['status'] ?>"><?= $email['status'] ?></span></td>
                                <td><?= date('Y-m-d H:i', strtotime($email['created_at'])) ?></td>
                                <td><?= $email['sent_at'] ? date('Y-m-d H:i', strtotime($email['sent_at'])) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

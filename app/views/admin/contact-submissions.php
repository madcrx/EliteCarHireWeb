<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Contact Submissions</h1>

        <?php if (empty($submissions)): ?>
            <div class="card">
                <p>No contact form submissions found.</p>
            </div>
        <?php else: ?>
            <?php foreach ($submissions as $submission): ?>
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <h3><?= e($submission['subject'] ?? 'No Subject') ?></h3>
                            <p><strong>From:</strong> <?= e($submission['name']) ?> (<?= e($submission['email']) ?>)</p>
                            <?php if (!empty($submission['phone'])): ?>
                                <p><strong>Phone:</strong> <?= e($submission['phone']) ?></p>
                            <?php endif; ?>
                            <p><strong>Date:</strong> <?= date('M d, Y H:i', strtotime($submission['created_at'])) ?></p>
                        </div>
                        <span class="badge badge-info">New</span>
                    </div>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--medium-gray);">
                        <p><?= nl2br(e($submission['message'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

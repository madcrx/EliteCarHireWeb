<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Content Management</h1>

        <?php if (empty($pages)): ?>
            <div class="card">
                <p>No CMS pages found.</p>
            </div>
        <?php else: ?>
            <?php foreach ($pages as $page): ?>
                <div class="card">
                    <h3><?= e($page['title']) ?></h3>
                    <p><strong>Page Key:</strong> <?= e($page['page_key']) ?></p>
                    <form method="POST" action="/admin/cms/save">
                        <input type="hidden" name="page_id" value="<?= $page['id'] ?>">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" value="<?= e($page['title']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Content</label>
                            <textarea name="content" rows="10" required><?= e($page['content']) ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

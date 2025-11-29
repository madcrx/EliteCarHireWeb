<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Image Management</h1>
        <p style="color: var(--dark-gray); margin-bottom: 2rem;">
            Upload and manage website images including logos, banners, and hero images.
            You can revert to default images at any time.
        </p>

        <?php if (empty($images)): ?>
            <div class="card">
                <p>No images configured yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($images as $image): ?>
                <div class="card">
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 2rem;">
                        <div>
                            <img src="/<?= e($image['image_path']) ?>"
                                 alt="<?= e($image['title']) ?>"
                                 style="width: 100%; height: auto; border-radius: var(--border-radius); border: 2px solid var(--medium-gray);"
                                 onerror="this.src='/assets/images/placeholder.png'">
                        </div>

                        <div>
                            <h3 style="color: var(--primary-gold); margin-bottom: 0.5rem;">
                                <?= e($image['title']) ?>
                                <span class="badge badge-info" style="margin-left: 1rem;">
                                    <?= ucfirst(e($image['image_type'])) ?>
                                </span>
                            </h3>
                            <p style="margin-bottom: 1rem; color: var(--dark-gray);">
                                <?= e($image['description']) ?>
                            </p>

                            <div style="margin-bottom: 1rem;">
                                <strong>Image Key:</strong> <code style="background: var(--light-gray); padding: 2px 8px; border-radius: 4px;"><?= e($image['image_key']) ?></code><br>
                                <strong>Current Path:</strong> <code style="background: var(--light-gray); padding: 2px 8px; border-radius: 4px;"><?= e($image['image_path']) ?></code><br>
                                <strong>Last Updated:</strong> <?= date('M d, Y H:i', strtotime($image['updated_at'])) ?>
                            </div>

                            <form method="POST" action="/admin/images/upload" enctype="multipart/form-data" style="margin-bottom: 1rem;">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="image_key" value="<?= e($image['image_key']) ?>">

                                <div class="form-group" style="margin-bottom: 1rem;">
                                    <input type="file" name="image_file" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" required>
                                </div>

                                <button type="submit" class="btn btn-primary">Upload New Image</button>
                            </form>

                            <?php if ($image['image_path'] !== $image['default_image_path']): ?>
                                <form method="POST" action="/admin/images/revert" style="display: inline-block;">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="image_key" value="<?= e($image['image_key']) ?>">
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Revert to default image?')">
                                        Revert to Default
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="card" style="background: var(--light-gray);">
            <h3><i class="fas fa-info-circle"></i> Image Guidelines</h3>
            <ul>
                <li><strong>Supported Formats:</strong> JPG, PNG, GIF, WebP</li>
                <li><strong>Maximum Size:</strong> 5MB per image</li>
                <li><strong>Logo Dimensions:</strong> 250x100px recommended (transparent PNG preferred)</li>
                <li><strong>Hero Images:</strong> 1920x800px recommended</li>
                <li><strong>Banners:</strong> 1200x400px recommended</li>
                <li><strong>Tip:</strong> Optimize images before uploading for faster page loading</li>
            </ul>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

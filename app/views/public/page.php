<?php ob_start(); ?>
<div class="container" style="padding: 4rem 0;">
    <div class="card">
        <?= $page['content'] ?? '<p>Page content not found.</p>' ?>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

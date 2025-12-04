<?php
ob_start();

// Output FAQ Schema for SEO
// This will help Google show rich results with FAQ snippets
echo seoFAQSchema();
?>

<div class="container" style="padding: 4rem 0;">
    <div class="card">
        <?= $page['content'] ?? '<p>Page content not found.</p>' ?>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

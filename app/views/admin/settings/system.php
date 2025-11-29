<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/../sidebar.php'; ?>
    <div class="main-content">
        <h1>System Configuration</h1>
        <div class="card">
            <p>This feature is under development and will be available soon.</p>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../../layout.php'; ?>

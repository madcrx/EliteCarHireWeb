<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Email Configuration - SMTP & Templates</h1>

        <div class="card">
            <h3>Email Configuration</h3>
            <p>Email configuration and template management will be available here soon.</p>

            <div class="alert alert-info" style="margin-top: 20px;">
                <strong>Note:</strong> Email settings are configured via environment variables.
                Please refer to <code>EMAIL_SETUP_GUIDE.md</code> for detailed configuration instructions.
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Security Alerts</h1>

        <div class="card">
            <p><strong>No security alerts at this time.</strong></p>
            <p>This page will display:</p>
            <ul>
                <li>Failed login attempts</li>
                <li>Suspicious activity</li>
                <li>Security warnings</li>
                <li>System alerts</li>
            </ul>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

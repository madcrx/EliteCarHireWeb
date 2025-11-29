<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Commission Rates - Platform Fees</h1>

        <div class="card">
            <h3>Current Commission Rate</h3>
            <p class="stat-value" style="font-size: 48px; color: #C5A253; margin: 20px 0;"><?= number_format($currentRate, 2) ?>%</p>

            <p>This is the platform commission rate charged on all completed bookings.</p>

            <div class="alert alert-info" style="margin-top: 20px;">
                <strong>Note:</strong> Commission rates are configured in <code>config/app.php</code>.
                Advanced commission management features will be available here soon.
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

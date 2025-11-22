<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Settings</h1>

        <div class="card">
            <h3>Application Settings</h3>
            <?php if (empty($settings)): ?>
                <p>No settings configured.</p>
            <?php else: ?>
                <?php foreach ($settings as $setting): ?>
                    <div class="form-group">
                        <label><?= e(ucwords(str_replace('_', ' ', $setting['setting_key']))) ?></label>
                        <input type="text" value="<?= e($setting['setting_value']) ?>">
                    </div>
                <?php endforeach; ?>
                <button class="btn btn-primary">Save Settings</button>
            <?php endif; ?>
        </div>

        <div class="card" style="margin-top: 2rem;">
            <h3>System Information</h3>
            <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
            <p><strong>Application:</strong> Elite Car Hire v1.0</p>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

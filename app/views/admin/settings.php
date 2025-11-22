<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Settings</h1>

        <form method="POST" action="/admin/settings/save">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="card">
                <h3><i class="fas fa-cog"></i> Application Settings</h3>
                <p style="color: var(--dark-gray); margin-bottom: 1.5rem;">
                    Configure application-wide settings. Changes take effect immediately after saving.
                </p>

                <?php if (empty($settings)): ?>
                    <div style="background: var(--light-gray); padding: 2rem; border-radius: var(--border-radius); text-align: center;">
                        <i class="fas fa-info-circle" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 1rem;"></i>
                        <p>No settings configured yet. Settings will appear here once created in the database.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($settings as $index => $setting): ?>
                        <div class="form-group">
                            <label for="setting_<?= $index ?>">
                                <strong><?= e(ucwords(str_replace('_', ' ', $setting['setting_key']))) ?></strong>
                            </label>
                            <input type="hidden" name="setting_keys[]" value="<?= e($setting['setting_key']) ?>">

                            <?php if (strlen($setting['setting_value']) > 100): ?>
                                <textarea name="setting_values[]" id="setting_<?= $index ?>" rows="4"><?= e($setting['setting_value']) ?></textarea>
                            <?php else: ?>
                                <input type="text" name="setting_values[]" id="setting_<?= $index ?>" value="<?= e($setting['setting_value']) ?>">
                            <?php endif; ?>

                            <?php if (!empty($setting['description'])): ?>
                                <small style="color: var(--dark-gray); display: block; margin-top: 0.5rem;">
                                    <?= e($setting['description']) ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </form>

        <div class="card" style="margin-top: 2rem;">
            <h3><i class="fas fa-info-circle"></i> System Information</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <div>
                    <p><strong>PHP Version:</strong></p>
                    <p style="color: var(--dark-gray);"><?= phpversion() ?></p>
                </div>
                <div>
                    <p><strong>Application:</strong></p>
                    <p style="color: var(--dark-gray);">Elite Car Hire v1.0</p>
                </div>
                <div>
                    <p><strong>Database:</strong></p>
                    <p style="color: var(--dark-gray);">MySQL <?= db()->fetch("SELECT VERSION() as version")['version'] ?></p>
                </div>
                <div>
                    <p><strong>Server Software:</strong></p>
                    <p style="color: var(--dark-gray);"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></p>
                </div>
            </div>
        </div>

        <div class="card" style="background: #fff8dc; border-left: 4px solid var(--primary-gold); margin-top: 2rem;">
            <h3><i class="fas fa-lightbulb"></i> Tips for Managing Settings</h3>
            <ul>
                <li><strong>Site Name:</strong> Update company name, email, and contact information</li>
                <li><strong>Booking Rules:</strong> Configure minimum booking hours, cancellation policies</li>
                <li><strong>Payment:</strong> Set commission rates, payment gateway settings</li>
                <li><strong>Email:</strong> SMTP settings for sending emails</li>
                <li><strong>Changes:</strong> All settings are logged in the audit trail</li>
            </ul>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

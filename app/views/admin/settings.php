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
            <h3><i class="fas fa-image"></i> Company Logo</h3>
            <p style="color: var(--dark-gray); margin-bottom: 1.5rem;">
                Upload a company logo to replace the "Elite Car Hire" text in the header. Recommended size: 200x60px, PNG with transparent background.
            </p>

            <?php
            $currentLogo = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'company_logo'");
            $logoPath = $currentLogo['setting_value'] ?? null;
            ?>

            <?php if ($logoPath && file_exists(__DIR__ . '/../../..' . $logoPath)): ?>
                <div style="margin-bottom: 1.5rem; padding: 1rem; background: var(--light-gray); border-radius: var(--border-radius);">
                    <p style="margin-bottom: 0.5rem;"><strong>Current Logo:</strong></p>
                    <img src="<?= e($logoPath) ?>" alt="Company Logo" style="max-height: 60px; background: white; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            <?php else: ?>
                <div style="margin-bottom: 1.5rem; padding: 1rem; background: var(--light-gray); border-radius: var(--border-radius);">
                    <p style="color: var(--dark-gray);"><i class="fas fa-info-circle"></i> No logo uploaded. Using default "Elite Car Hire" text in header.</p>
                </div>
            <?php endif; ?>

            <form method="POST" action="/admin/settings/upload-logo" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <div class="form-group">
                    <label for="logo_file">Select Logo Image</label>
                    <input type="file" name="logo_file" id="logo_file" accept="image/png,image/jpeg,image/jpg,image/svg+xml" required>
                    <small style="color: var(--dark-gray); display: block; margin-top: 0.5rem;">
                        Supported formats: PNG, JPG, SVG. Maximum size: 2MB.
                    </small>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload Logo
                    </button>

                    <?php if ($logoPath): ?>
                        <button type="button" onclick="if(confirm('Remove logo and return to text header?')) { document.getElementById('removeLogo').submit(); }" class="btn" style="background: var(--dark-gray); color: white;">
                            <i class="fas fa-trash"></i> Remove Logo
                        </button>
                    <?php endif; ?>
                </div>
            </form>

            <?php if ($logoPath): ?>
                <form id="removeLogo" method="POST" action="/admin/settings/remove-logo" style="display: none;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                </form>
            <?php endif; ?>
        </div>

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

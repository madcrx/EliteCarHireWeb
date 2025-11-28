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
            <h3><i class="fas fa-image"></i> Company Logos</h3>
            <p style="color: var(--dark-gray); margin-bottom: 1.5rem;">
                Upload multiple company logos and select which one to display. Recommended size: 200x60px, PNG with transparent background.
            </p>

            <?php
            // Get all uploaded logos from site_images table
            $allLogos = db()->fetchAll("SELECT * FROM site_images WHERE image_type = 'logo' ORDER BY created_at DESC");
            // Get active logo ID from settings
            $activeLogo = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'active_logo_id'");
            $activeLogoId = $activeLogo['setting_value'] ?? null;
            ?>

            <?php if (!empty($allLogos)): ?>
                <div style="margin-bottom: 1.5rem; padding: 1rem; background: var(--light-gray); border-radius: var(--border-radius);">
                    <p style="margin-bottom: 1rem;"><strong>Uploaded Logos:</strong></p>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
                        <?php foreach ($allLogos as $logo): ?>
                            <div style="padding: 1rem; background: white; border: <?= $logo['id'] == $activeLogoId ? '3px solid var(--primary-gold)' : '1px solid #ddd' ?>; border-radius: var(--border-radius); text-align: center;">
                                <img src="<?= e($logo['image_path']) ?>" alt="<?= e($logo['title']) ?>"
                                     style="max-height: 60px; max-width: 100%; margin-bottom: 0.5rem;">
                                <p style="font-size: 0.85rem; margin: 0.5rem 0; color: var(--dark-gray);">
                                    <?= e($logo['title']) ?>
                                </p>
                                <?php if ($logo['id'] == $activeLogoId): ?>
                                    <span class="badge badge-success" style="display: block; margin-bottom: 0.5rem;">Active</span>
                                <?php else: ?>
                                    <form method="POST" action="/admin/settings/set-active-logo" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="logo_id" value="<?= $logo['id'] ?>">
                                        <button type="submit" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem; width: 100%;">
                                            Set as Active
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" action="/admin/settings/delete-logo" style="display: inline; margin-top: 0.5rem;">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="logo_id" value="<?= $logo['id'] ?>">
                                    <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: var(--danger); color: white; width: 100%; margin-top: 0.5rem;"
                                            onclick="return confirm('Delete this logo?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div style="margin-bottom: 1.5rem; padding: 1rem; background: var(--light-gray); border-radius: var(--border-radius);">
                    <p style="color: var(--dark-gray);"><i class="fas fa-info-circle"></i> No logos uploaded yet. Using default "Elite Car Hire" text in header.</p>
                </div>
            <?php endif; ?>

            <form method="POST" action="/admin/settings/upload-logo" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <div class="form-group">
                    <label for="logo_title">Logo Name/Title</label>
                    <input type="text" name="logo_title" id="logo_title" required placeholder="e.g., Main Logo, Dark Version, Light Version">
                </div>

                <div class="form-group">
                    <label for="logo_file">Select Logo Image</label>
                    <input type="file" name="logo_file" id="logo_file" accept="image/png,image/jpeg,image/jpg,image/svg+xml" required>
                    <small style="color: var(--dark-gray); display: block; margin-top: 0.5rem;">
                        Supported formats: PNG, JPG, SVG. Maximum size: 2MB.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload New Logo
                </button>
            </form>
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

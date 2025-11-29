<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/../sidebar.php'; ?>
    <div class="main-content">
        <h1><i class="fas fa-server"></i> System Configuration</h1>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
                <?= e($_SESSION['flash']['message']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <form method="POST" action="/admin/settings/system/save">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="card">
                <h2>General System Settings</h2>

                <div class="form-group">
                    <label for="site_name">Site Name *</label>
                    <input type="text" name="site_name" id="site_name"
                           value="<?= e($siteName ?? 'Elite Car Hire') ?>" class="form-control"
                           placeholder="Elite Car Hire" required>
                    <small>The name of your website (used in titles, emails, etc.)</small>
                </div>

                <div class="form-group">
                    <label for="site_url">Site URL *</label>
                    <input type="url" name="site_url" id="site_url"
                           value="<?= e($siteUrl ?? '') ?>" class="form-control"
                           placeholder="https://elitecarhire.com.au" required>
                    <small>Full URL to your website (include https://)</small>
                </div>

                <div class="form-group">
                    <label for="timezone">Timezone *</label>
                    <select name="timezone" id="timezone" class="form-control" required>
                        <option value="Australia/Sydney" <?= ($timezone ?? 'Australia/Sydney') === 'Australia/Sydney' ? 'selected' : '' ?>>Australia/Sydney (AEST/AEDT)</option>
                        <option value="Australia/Melbourne" <?= ($timezone ?? '') === 'Australia/Melbourne' ? 'selected' : '' ?>>Australia/Melbourne (AEST/AEDT)</option>
                        <option value="Australia/Brisbane" <?= ($timezone ?? '') === 'Australia/Brisbane' ? 'selected' : '' ?>>Australia/Brisbane (AEST)</option>
                        <option value="Australia/Adelaide" <?= ($timezone ?? '') === 'Australia/Adelaide' ? 'selected' : '' ?>>Australia/Adelaide (ACST/ACDT)</option>
                        <option value="Australia/Perth" <?= ($timezone ?? '') === 'Australia/Perth' ? 'selected' : '' ?>>Australia/Perth (AWST)</option>
                        <option value="Australia/Darwin" <?= ($timezone ?? '') === 'Australia/Darwin' ? 'selected' : '' ?>>Australia/Darwin (ACST)</option>
                        <option value="Australia/Hobart" <?= ($timezone ?? '') === 'Australia/Hobart' ? 'selected' : '' ?>>Australia/Hobart (AEST/AEDT)</option>
                    </select>
                    <small>Default timezone for the application</small>
                </div>

                <div class="form-group">
                    <label for="session_timeout">Session Timeout (seconds) *</label>
                    <input type="number" name="session_timeout" id="session_timeout"
                           value="<?= e($sessionTimeout ?? '3600') ?>" class="form-control"
                           placeholder="3600" min="300" max="86400" required>
                    <small>How long users stay logged in (3600 = 1 hour, 86400 = 24 hours)</small>
                </div>

                <hr>

                <h3 style="margin-top: 1.5rem;">System Modes</h3>

                <div class="form-group" style="display: flex; align-items: center; gap: 1rem;">
                    <input type="checkbox" name="maintenance_mode" id="maintenance_mode"
                           value="1" <?= ($maintenanceMode ?? '0') === '1' ? 'checked' : '' ?>
                           style="width: auto; margin: 0;">
                    <label for="maintenance_mode" style="margin: 0; cursor: pointer;">
                        <strong>Maintenance Mode</strong> - Disable public access to the site
                    </label>
                </div>
                <small style="display: block; margin-left: 2rem; color: #c53030;">
                    ⚠️ When enabled, only admins can access the site. Customers will see a maintenance message.
                </small>

                <div class="form-group" style="display: flex; align-items: center; gap: 1rem; margin-top: 1.5rem;">
                    <input type="checkbox" name="debug_mode" id="debug_mode"
                           value="1" <?= ($debugMode ?? '0') === '1' ? 'checked' : '' ?>
                           style="width: auto; margin: 0;">
                    <label for="debug_mode" style="margin: 0; cursor: pointer;">
                        <strong>Debug Mode</strong> - Show detailed error messages
                    </label>
                </div>
                <small style="display: block; margin-left: 2rem; color: #c53030;">
                    ⚠️ Only enable for development. Never use in production as it exposes sensitive information.
                </small>

                <hr>

                <h3 style="margin-top: 1.5rem;">Database Information</h3>
                <small style="color: var(--dark-gray); display: block; margin-bottom: 1rem;">
                    Database settings are read from your <code>.env</code> file and cannot be changed here for security reasons.
                </small>

                <div class="form-group">
                    <label>Database Host</label>
                    <input type="text" value="<?= e($dbHost ?? 'localhost') ?>" class="form-control" disabled readonly>
                </div>

                <div class="form-group">
                    <label>Database Name</label>
                    <input type="text" value="<?= e($dbName ?? '') ?>" class="form-control" disabled readonly>
                </div>

                <div class="form-group">
                    <label>Database Username</label>
                    <input type="text" value="<?= e($dbUser ?? '') ?>" class="form-control" disabled readonly>
                </div>

                <div style="margin-top: 1.5rem; padding: 1rem; background: #f7fafc; border-left: 4px solid var(--primary-gold);">
                    <strong>To change database settings:</strong>
                    <ol style="margin: 0.5rem 0 0 1.5rem;">
                        <li>Connect to your server via FTP or SSH</li>
                        <li>Edit the <code>.env</code> file in the root directory</li>
                        <li>Update DB_HOST, DB_NAME, DB_USER, and DB_PASSWORD</li>
                        <li>Save and refresh your website</li>
                    </ol>
                </div>

                <hr>

                <div style="margin-top: 2rem; padding: 1rem; background: #fff5f5; border-left: 4px solid #c53030;">
                    <strong>Important Notes:</strong>
                    <ul style="margin: 0.5rem 0 0 1rem;">
                        <li>Changes take effect immediately - test in a staging environment first</li>
                        <li>Maintenance mode will lock out all non-admin users</li>
                        <li>Never enable debug mode on a live production site</li>
                        <li>Keep session timeout reasonable for security (1-24 hours)</li>
                    </ul>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save System Configuration
                    </button>
                    <a href="/admin/dashboard" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../../layout.php'; ?>

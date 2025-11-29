<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/../sidebar.php'; ?>
    <div class="main-content">
        <h1><i class="fas fa-envelope-open-text"></i> Email Configuration</h1>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
                <?= e($_SESSION['flash']['message']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <form method="POST" action="/admin/settings/email/save">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="card">
                <h2>SMTP Server Configuration</h2>
                <small style="color: var(--dark-gray); display: block; margin-bottom: 1.5rem;">
                    Configure your email server settings to send transactional emails (booking confirmations, notifications, etc.)
                </small>

                <div class="form-group">
                    <label for="smtp_host">SMTP Host *</label>
                    <input type="text" name="smtp_host" id="smtp_host"
                           value="<?= e($smtpHost ?? '') ?>" class="form-control"
                           placeholder="smtp.gmail.com or mail.yourdomain.com" required>
                    <small>Your email provider's SMTP server address</small>
                </div>

                <div class="form-group">
                    <label for="smtp_port">SMTP Port *</label>
                    <input type="number" name="smtp_port" id="smtp_port"
                           value="<?= e($smtpPort ?? '587') ?>" class="form-control"
                           placeholder="587" required>
                    <small>Common ports: 587 (TLS), 465 (SSL), 25 (unsecured)</small>
                </div>

                <div class="form-group">
                    <label for="smtp_encryption">Encryption Method *</label>
                    <select name="smtp_encryption" id="smtp_encryption" class="form-control" required>
                        <option value="tls" <?= ($smtpEncryption ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Recommended)</option>
                        <option value="ssl" <?= ($smtpEncryption ?? 'tls') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                        <option value="none" <?= ($smtpEncryption ?? 'tls') === 'none' ? 'selected' : '' ?>>None (Not recommended)</option>
                    </select>
                </div>

                <hr>

                <h3 style="margin-top: 1.5rem;">SMTP Authentication</h3>

                <div class="form-group">
                    <label for="smtp_username">SMTP Username *</label>
                    <input type="text" name="smtp_username" id="smtp_username"
                           value="<?= e($smtpUsername ?? '') ?>" class="form-control"
                           placeholder="your-email@example.com" required autocomplete="off">
                    <small>Your email address or SMTP username</small>
                </div>

                <div class="form-group">
                    <label for="smtp_password">SMTP Password *</label>
                    <input type="password" name="smtp_password" id="smtp_password"
                           value="<?= e($smtpPassword ?? '') ?>" class="form-control"
                           placeholder="••••••••" required autocomplete="new-password">
                    <small style="color: #c53030;">⚠️ Keep this secure! For Gmail, use an App Password</small>
                </div>

                <hr>

                <h3 style="margin-top: 1.5rem;">Email Sender Information</h3>
                <small style="color: var(--dark-gray); display: block; margin-bottom: 1rem;">
                    These details will appear in the "From" field of all outgoing emails
                </small>

                <div class="form-group">
                    <label for="email_from_address">From Email Address *</label>
                    <input type="email" name="email_from_address" id="email_from_address"
                           value="<?= e($emailFrom ?? '') ?>" class="form-control"
                           placeholder="noreply@elitecarhire.com.au" required>
                    <small>Email address customers will see emails from</small>
                </div>

                <div class="form-group">
                    <label for="email_from_name">From Name *</label>
                    <input type="text" name="email_from_name" id="email_from_name"
                           value="<?= e($emailFromName ?? '') ?>" class="form-control"
                           placeholder="Elite Car Hire" required>
                    <small>Name that will appear in email clients</small>
                </div>

                <hr>

                <div style="margin-top: 2rem; padding: 1rem; background: #f7fafc; border-left: 4px solid var(--primary-gold);">
                    <strong>Popular Email Provider Settings:</strong>
                    <ul style="margin: 0.5rem 0 0 1rem;">
                        <li><strong>Gmail:</strong> smtp.gmail.com | Port: 587 | TLS | Use App Password (not regular password)</li>
                        <li><strong>Outlook/Office365:</strong> smtp.office365.com | Port: 587 | TLS</li>
                        <li><strong>SendGrid:</strong> smtp.sendgrid.net | Port: 587 | TLS</li>
                        <li><strong>Mailgun:</strong> smtp.mailgun.org | Port: 587 | TLS</li>
                        <li><strong>cPanel/WHM:</strong> mail.yourdomain.com | Port: 587 | TLS</li>
                    </ul>
                </div>

                <div style="margin-top: 1.5rem; padding: 1rem; background: #fff5f5; border-left: 4px solid #c53030;">
                    <strong>Security Notes:</strong>
                    <ul style="margin: 0.5rem 0 0 1rem;">
                        <li>Never share SMTP credentials</li>
                        <li>Use App Passwords for Gmail (not your main password)</li>
                        <li>Enable 2FA on your email account for extra security</li>
                        <li>Test settings before going live</li>
                    </ul>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Email Configuration
                    </button>
                    <a href="/admin/dashboard" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../../layout.php'; ?>

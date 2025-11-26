<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Configuration - Admin - Elite Car Hire</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <style>
        .config-container {
            padding: 30px;
        }
        .config-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .config-section {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .config-section h3 {
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #C5A253;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .config-section h3::before {
            content: '⚙️';
            font-size: 24px;
        }
        .config-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }
        .config-item {
            margin-bottom: 20px;
        }
        .config-item label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #555;
        }
        .config-item input,
        .config-item select,
        .config-item textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .config-item input:focus,
        .config-item select:focus,
        .config-item textarea:focus {
            outline: none;
            border-color: #C5A253;
        }
        .config-item .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .config-item .current-value {
            background: #f5f5f5;
            padding: 8px 12px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 13px;
            color: #333;
            border-left: 3px solid #C5A253;
        }
        .password-field {
            position: relative;
        }
        .password-field input {
            padding-right: 40px;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            background: none;
            border: none;
            font-size: 18px;
        }
        .btn-save {
            background: #4caf50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        .btn-save:hover {
            background: #45a049;
        }
        .btn-test {
            background: #2196f3;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
        }
        .btn-test:hover {
            background: #0b7dda;
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #f39c12;
            padding: 15px;
            margin-bottom: 20px;
        }
        .warning-box p {
            margin: 0;
            color: #856404;
        }
        .success-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin-bottom: 20px;
        }
        .success-box p {
            margin: 0;
            color: #155724;
        }
        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }
        .tab-button {
            padding: 12px 24px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            color: #666;
            transition: all 0.3s;
        }
        .tab-button.active {
            color: #C5A253;
            border-bottom-color: #C5A253;
        }
        .tab-button:hover {
            color: #C5A253;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <div class="config-container">
            <div class="config-header">
                <h1>⚙️ System Configuration</h1>
            </div>

            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="success-box">
                    <p>✓ <?= e($_SESSION['flash_success']) ?></p>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <div class="warning-box">
                <p><strong>⚠️ Security Warning:</strong> This page contains sensitive configuration data. Only make changes if you know what you're doing. Incorrect settings can break the application.</p>
            </div>

            <div class="tab-buttons">
                <button class="tab-button active" onclick="switchTab('database')">Database</button>
                <button class="tab-button" onclick="switchTab('email')">Email / SMTP</button>
                <button class="tab-button" onclick="switchTab('payment')">Payment Gateway</button>
                <button class="tab-button" onclick="switchTab('app')">Application</button>
                <button class="tab-button" onclick="switchTab('security')">Security</button>
            </div>

            <form method="POST" action="/admin/system-config/save">
                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

                <!-- Database Configuration -->
                <div id="tab-database" class="tab-content active">
                    <div class="config-section">
                        <h3>Database Configuration</h3>
                        <div class="config-grid">
                            <div class="config-item">
                                <label>Database Host</label>
                                <div class="current-value"><?= e(config('database.host', 'localhost')) ?></div>
                                <input type="text" name="db_host" value="<?= e(config('database.host', 'localhost')) ?>" placeholder="localhost">
                                <div class="help-text">Usually 'localhost' or an IP address</div>
                            </div>

                            <div class="config-item">
                                <label>Database Port</label>
                                <div class="current-value"><?= e(config('database.port', '3306')) ?></div>
                                <input type="text" name="db_port" value="<?= e(config('database.port', '3306')) ?>" placeholder="3306">
                                <div class="help-text">Default MySQL port is 3306</div>
                            </div>

                            <div class="config-item">
                                <label>Database Name</label>
                                <div class="current-value"><?= e(config('database.database', '')) ?></div>
                                <input type="text" name="db_name" value="<?= e(config('database.database', '')) ?>" placeholder="elitecarhire_db">
                                <div class="help-text">Name of your MySQL database</div>
                            </div>

                            <div class="config-item">
                                <label>Database Username</label>
                                <div class="current-value"><?= e(config('database.username', '')) ?></div>
                                <input type="text" name="db_username" value="<?= e(config('database.username', '')) ?>" placeholder="root">
                                <div class="help-text">MySQL user with access to the database</div>
                            </div>

                            <div class="config-item password-field">
                                <label>Database Password</label>
                                <div class="current-value">••••••••</div>
                                <input type="password" name="db_password" id="db_password" value="<?= e(config('database.password', '')) ?>" placeholder="••••••••">
                                <button type="button" class="toggle-password" onclick="togglePassword('db_password')">👁️</button>
                                <div class="help-text">Leave blank to keep current password</div>
                            </div>

                            <div class="config-item">
                                <label>Database Charset</label>
                                <div class="current-value"><?= e(config('database.charset', 'utf8mb4')) ?></div>
                                <select name="db_charset">
                                    <option value="utf8mb4" <?= config('database.charset') === 'utf8mb4' ? 'selected' : '' ?>>UTF-8 (utf8mb4)</option>
                                    <option value="utf8" <?= config('database.charset') === 'utf8' ? 'selected' : '' ?>>UTF-8 (utf8)</option>
                                </select>
                                <div class="help-text">Character set for database connection</div>
                            </div>
                        </div>
                        <button type="button" class="btn-test" onclick="testDatabaseConnection()">Test Database Connection</button>
                    </div>
                </div>

                <!-- Email Configuration -->
                <div id="tab-email" class="tab-content">
                    <div class="config-section">
                        <h3>SMTP Configuration</h3>
                        <div class="config-grid">
                            <div class="config-item">
                                <label>SMTP Host</label>
                                <div class="current-value"><?= e(config('email.smtp_host', '')) ?></div>
                                <input type="text" name="smtp_host" value="<?= e(config('email.smtp_host', '')) ?>" placeholder="smtp.gmail.com">
                                <div class="help-text">SMTP server hostname</div>
                            </div>

                            <div class="config-item">
                                <label>SMTP Port</label>
                                <div class="current-value"><?= e(config('email.smtp_port', '587')) ?></div>
                                <input type="number" name="smtp_port" value="<?= e(config('email.smtp_port', '587')) ?>" placeholder="587">
                                <div class="help-text">Usually 587 (TLS) or 465 (SSL)</div>
                            </div>

                            <div class="config-item">
                                <label>SMTP Encryption</label>
                                <div class="current-value"><?= e(config('email.smtp_encryption', 'tls')) ?></div>
                                <select name="smtp_encryption">
                                    <option value="tls" <?= config('email.smtp_encryption') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                    <option value="ssl" <?= config('email.smtp_encryption') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                    <option value="none" <?= config('email.smtp_encryption') === 'none' ? 'selected' : '' ?>>None</option>
                                </select>
                                <div class="help-text">Encryption method for SMTP</div>
                            </div>

                            <div class="config-item">
                                <label>SMTP Username</label>
                                <div class="current-value"><?= e(config('email.smtp_username', '')) ?></div>
                                <input type="text" name="smtp_username" value="<?= e(config('email.smtp_username', '')) ?>" placeholder="your-email@example.com">
                                <div class="help-text">SMTP authentication username</div>
                            </div>

                            <div class="config-item password-field">
                                <label>SMTP Password</label>
                                <div class="current-value">••••••••</div>
                                <input type="password" name="smtp_password" id="smtp_password" value="<?= e(config('email.smtp_password', '')) ?>" placeholder="••••••••">
                                <button type="button" class="toggle-password" onclick="togglePassword('smtp_password')">👁️</button>
                                <div class="help-text">SMTP authentication password or app password</div>
                            </div>

                            <div class="config-item">
                                <label>From Email Address</label>
                                <div class="current-value"><?= e(config('email.from_address', '')) ?></div>
                                <input type="email" name="from_email" value="<?= e(config('email.from_address', '')) ?>" placeholder="noreply@elitecarhire.au">
                                <div class="help-text">Email address that emails are sent from</div>
                            </div>

                            <div class="config-item">
                                <label>From Name</label>
                                <div class="current-value"><?= e(config('email.from_name', 'Elite Car Hire')) ?></div>
                                <input type="text" name="from_name" value="<?= e(config('email.from_name', 'Elite Car Hire')) ?>" placeholder="Elite Car Hire">
                                <div class="help-text">Name that appears in sent emails</div>
                            </div>
                        </div>
                        <button type="button" class="btn-test" onclick="testEmailConnection()">Send Test Email</button>
                    </div>
                </div>

                <!-- Payment Gateway Configuration -->
                <div id="tab-payment" class="tab-content">
                    <div class="config-section">
                        <h3>Stripe Configuration</h3>
                        <div class="config-grid">
                            <div class="config-item">
                                <label>Stripe Mode</label>
                                <div class="current-value">Using environment keys</div>
                                <select name="stripe_mode">
                                    <option value="test" selected>Test Mode</option>
                                    <option value="live">Live Mode</option>
                                </select>
                                <div class="help-text">Switch between test and live Stripe keys (configured via environment variables)</div>
                            </div>

                            <div class="config-item password-field">
                                <label>Stripe Publishable Key</label>
                                <div class="current-value"><?= e(substr(config('payment.stripe.publishable_key', ''), 0, 20)) ?><?= strlen(config('payment.stripe.publishable_key', '')) > 0 ? '...' : 'Not set' ?></div>
                                <input type="password" name="stripe_test_pub_key" id="stripe_test_pub_key" value="<?= e(config('payment.stripe.publishable_key', '')) ?>" placeholder="pk_test_... or pk_live_...">
                                <button type="button" class="toggle-password" onclick="togglePassword('stripe_test_pub_key')">👁️</button>
                                <div class="help-text">Stripe publishable key (starts with pk_test_ or pk_live_)</div>
                            </div>

                            <div class="config-item password-field">
                                <label>Stripe Secret Key</label>
                                <div class="current-value"><?= strlen(config('payment.stripe.secret_key', '')) > 0 ? 'sk_••••••••' : 'Not set' ?></div>
                                <input type="password" name="stripe_test_secret_key" id="stripe_test_secret_key" value="<?= e(config('payment.stripe.secret_key', '')) ?>" placeholder="sk_test_... or sk_live_...">
                                <button type="button" class="toggle-password" onclick="togglePassword('stripe_test_secret_key')">👁️</button>
                                <div class="help-text">Stripe secret key (starts with sk_test_ or sk_live_)</div>
                            </div>

                            <div class="config-item password-field">
                                <label>Stripe Webhook Secret</label>
                                <div class="current-value"><?= strlen(config('payment.stripe.webhook_secret', '')) > 0 ? 'whsec_••••••••' : 'Not set' ?></div>
                                <input type="password" name="stripe_webhook_secret" id="stripe_webhook_secret" value="<?= e(config('payment.stripe.webhook_secret', '')) ?>" placeholder="whsec_...">
                                <button type="button" class="toggle-password" onclick="togglePassword('stripe_webhook_secret')">👁️</button>
                                <div class="help-text">Stripe webhook secret (starts with whsec_)</div>
                            </div>

                            <div class="config-item password-field">
                                <label>Stripe Connect Client ID</label>
                                <div class="current-value"><?= strlen(config('payment.stripe.connect_client_id', '')) > 0 ? 'ca_••••••••' : 'Not set' ?></div>
                                <input type="password" name="stripe_connect_client_id" id="stripe_connect_client_id" value="<?= e(config('payment.stripe.connect_client_id', '')) ?>" placeholder="ca_...">
                                <button type="button" class="toggle-password" onclick="togglePassword('stripe_connect_client_id')">👁️</button>
                                <div class="help-text">Stripe Connect client ID for vehicle owner payments</div>
                            </div>

                            <div class="config-item">
                                <label>Currency</label>
                                <div class="current-value"><?= e(config('payment.currency', 'AUD')) ?></div>
                                <select name="stripe_currency">
                                    <option value="AUD" <?= config('payment.currency') === 'AUD' ? 'selected' : '' ?>>AUD - Australian Dollar</option>
                                    <option value="USD" <?= config('payment.currency') === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                                    <option value="EUR" <?= config('payment.currency') === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                                    <option value="GBP" <?= config('payment.currency') === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                                </select>
                                <div class="help-text">Payment currency</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Application Configuration -->
                <div id="tab-app" class="tab-content">
                    <div class="config-section">
                        <h3>Application Settings</h3>
                        <div class="config-grid">
                            <div class="config-item">
                                <label>Application Name</label>
                                <div class="current-value"><?= e(config('app.name', 'Elite Car Hire')) ?></div>
                                <input type="text" name="app_name" value="<?= e(config('app.name', 'Elite Car Hire')) ?>" placeholder="Elite Car Hire">
                                <div class="help-text">Name of your application</div>
                            </div>

                            <div class="config-item">
                                <label>Application URL</label>
                                <div class="current-value"><?= e(config('app.url', '')) ?></div>
                                <input type="url" name="app_url" value="<?= e(config('app.url', '')) ?>" placeholder="https://elitecarhire.au">
                                <div class="help-text">Full URL of your application (no trailing slash)</div>
                            </div>

                            <div class="config-item">
                                <label>Environment</label>
                                <div class="current-value"><?= e(config('app.env', 'production')) ?></div>
                                <select name="app_env">
                                    <option value="development" <?= config('app.env') === 'development' ? 'selected' : '' ?>>Development</option>
                                    <option value="staging" <?= config('app.env') === 'staging' ? 'selected' : '' ?>>Staging</option>
                                    <option value="production" <?= config('app.env') === 'production' ? 'selected' : '' ?>>Production</option>
                                </select>
                                <div class="help-text">Current environment (affects error reporting)</div>
                            </div>

                            <div class="config-item">
                                <label>Debug Mode</label>
                                <div class="current-value"><?= config('app.debug', false) ? 'Enabled' : 'Disabled' ?></div>
                                <select name="app_debug">
                                    <option value="0" <?= !config('app.debug', false) ? 'selected' : '' ?>>Disabled (Recommended for Production)</option>
                                    <option value="1" <?= config('app.debug', false) ? 'selected' : '' ?>>Enabled (Development Only)</option>
                                </select>
                                <div class="help-text">⚠️ NEVER enable debug mode in production!</div>
                            </div>

                            <div class="config-item">
                                <label>Timezone</label>
                                <div class="current-value"><?= e(config('app.timezone', 'Australia/Melbourne')) ?></div>
                                <select name="app_timezone">
                                    <option value="Australia/Melbourne" <?= config('app.timezone') === 'Australia/Melbourne' ? 'selected' : '' ?>>Australia/Melbourne</option>
                                    <option value="Australia/Sydney" <?= config('app.timezone') === 'Australia/Sydney' ? 'selected' : '' ?>>Australia/Sydney</option>
                                    <option value="Australia/Brisbane" <?= config('app.timezone') === 'Australia/Brisbane' ? 'selected' : '' ?>>Australia/Brisbane</option>
                                    <option value="Australia/Perth" <?= config('app.timezone') === 'Australia/Perth' ? 'selected' : '' ?>>Australia/Perth</option>
                                    <option value="UTC" <?= config('app.timezone') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                                </select>
                                <div class="help-text">Default timezone for the application</div>
                            </div>

                            <div class="config-item">
                                <label>Commission Rate (%)</label>
                                <div class="current-value"><?= e(config('payment.commission_rate', '15')) ?>%</div>
                                <input type="number" name="commission_rate" value="<?= e(config('payment.commission_rate', '15')) ?>" placeholder="15" min="0" max="100" step="0.1">
                                <div class="help-text">Platform commission percentage on bookings</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Configuration -->
                <div id="tab-security" class="tab-content">
                    <div class="config-section">
                        <h3>Security Settings</h3>
                        <div class="config-grid">
                            <div class="config-item">
                                <label>Session Name</label>
                                <div class="current-value"><?= e(config('security.session_name', 'elite_car_hire_session')) ?></div>
                                <input type="text" name="session_name" value="<?= e(config('security.session_name', 'elite_car_hire_session')) ?>" placeholder="elite_car_hire_session">
                                <div class="help-text">Name of the session cookie</div>
                            </div>

                            <div class="config-item">
                                <label>Session Lifetime (seconds)</label>
                                <div class="current-value"><?= e(config('security.session_lifetime', '7200')) ?> seconds (<?= round(config('security.session_lifetime', 7200) / 60) ?> minutes)</div>
                                <input type="number" name="session_lifetime" value="<?= e(config('security.session_lifetime', '7200')) ?>" placeholder="7200" min="900" max="86400">
                                <div class="help-text">How long users stay logged in (900-86400 seconds)</div>
                            </div>

                            <div class="config-item">
                                <label>CSRF Token Name</label>
                                <div class="current-value"><?= e(config('security.csrf_token_name', '_csrf_token')) ?></div>
                                <input type="text" name="csrf_token_name" value="<?= e(config('security.csrf_token_name', '_csrf_token')) ?>" placeholder="_csrf_token">
                                <div class="help-text">Name of the CSRF token field</div>
                            </div>

                            <div class="config-item">
                                <label>Minimum Password Length</label>
                                <div class="current-value"><?= e(config('security.password_min_length', '8')) ?> characters</div>
                                <input type="number" name="password_min_length" value="<?= e(config('security.password_min_length', '8')) ?>" placeholder="8" min="6" max="20">
                                <div class="help-text">Minimum required password length (6-20 characters)</div>
                            </div>

                            <div class="config-item">
                                <label>Maximum Login Attempts</label>
                                <div class="current-value"><?= e(config('security.max_login_attempts', '5')) ?> attempts</div>
                                <input type="number" name="max_login_attempts" value="<?= e(config('security.max_login_attempts', '5')) ?>" placeholder="5" min="3" max="10">
                                <div class="help-text">Failed login attempts before lockout</div>
                            </div>

                            <div class="config-item">
                                <label>Account Lockout Time (seconds)</label>
                                <div class="current-value"><?= e(config('security.lockout_time', '900')) ?> seconds (<?= round(config('security.lockout_time', 900) / 60) ?> minutes)</div>
                                <input type="number" name="lockout_duration" value="<?= e(config('security.lockout_time', '900')) ?>" placeholder="900" min="300" max="7200">
                                <div class="help-text">How long accounts are locked after max failed attempts (300-7200 seconds)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px; padding-bottom: 30px;">
                    <button type="submit" class="btn-save">💾 Save All Configuration</button>
                    <p style="color: #666; font-size: 13px; margin-top: 10px;">⚠️ Changes will take effect immediately. Make sure all settings are correct before saving.</p>
                </div>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });

            // Show selected tab
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }

        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            field.type = field.type === 'password' ? 'text' : 'password';
        }

        function testDatabaseConnection() {
            alert('Testing database connection...');
            fetch('/admin/system-config/test-database', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    host: document.querySelector('[name="db_host"]').value,
                    port: document.querySelector('[name="db_port"]').value,
                    database: document.querySelector('[name="db_name"]').value,
                    username: document.querySelector('[name="db_username"]').value,
                    password: document.querySelector('[name="db_password"]').value,
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✓ Database connection successful!');
                } else {
                    alert('✗ Database connection failed: ' + data.message);
                }
            })
            .catch(error => {
                alert('✗ Test failed: ' + error);
            });
        }

        function testEmailConnection() {
            const email = prompt('Enter email address to send test email to:');
            if (!email) return;

            alert('Sending test email...');
            fetch('/admin/system-config/test-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    to: email,
                    smtp_host: document.querySelector('[name="smtp_host"]').value,
                    smtp_port: document.querySelector('[name="smtp_port"]').value,
                    smtp_username: document.querySelector('[name="smtp_username"]').value,
                    smtp_password: document.querySelector('[name="smtp_password"]').value,
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✓ Test email sent successfully! Check ' + email);
                } else {
                    alert('✗ Failed to send test email: ' + data.message);
                }
            })
            .catch(error => {
                alert('✗ Test failed: ' + error);
            });
        }
    </script>
</body>
</html>

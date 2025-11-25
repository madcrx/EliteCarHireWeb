<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Email Settings - SMTP Configuration</h1>

        <div class="card">
            <h3>Current SMTP Configuration</h3>
            <table class="table">
                <tr>
                    <th>SMTP Host:</th>
                    <td><?= htmlspecialchars($emailConfig['smtp_host'] ?: 'Not configured') ?></td>
                </tr>
                <tr>
                    <th>SMTP Port:</th>
                    <td><?= htmlspecialchars($emailConfig['smtp_port']) ?></td>
                </tr>
                <tr>
                    <th>SMTP User:</th>
                    <td><?= htmlspecialchars($emailConfig['smtp_user'] ?: 'Not configured') ?></td>
                </tr>
                <tr>
                    <th>Encryption:</th>
                    <td><?= htmlspecialchars($emailConfig['smtp_encryption']) ?></td>
                </tr>
            </table>

            <div class="alert alert-info" style="margin-top: 20px;">
                <strong>Note:</strong> SMTP settings are configured via environment variables.
                Please refer to <code>EMAIL_SETUP_GUIDE.md</code> for detailed configuration instructions.
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

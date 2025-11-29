<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Payment Settings - Stripe Configuration</h1>

        <div class="card">
            <h3>Stripe API Configuration</h3>
            <table class="table">
                <tr>
                    <th>Publishable Key:</th>
                    <td><?= $stripeConfig['publishable_key'] ? '<span class="badge badge-success">Configured</span>' : '<span class="badge badge-warning">Not configured</span>' ?></td>
                </tr>
                <tr>
                    <th>Secret Key:</th>
                    <td><?= $stripeConfig['has_secret_key'] ? '<span class="badge badge-success">Configured</span>' : '<span class="badge badge-warning">Not configured</span>' ?></td>
                </tr>
                <tr>
                    <th>Webhook:</th>
                    <td><?= $stripeConfig['webhook_configured'] ? '<span class="badge badge-success">Configured</span>' : '<span class="badge badge-warning">Not configured</span>' ?></td>
                </tr>
            </table>

            <div class="alert alert-info" style="margin-top: 20px;">
                <strong>Note:</strong> Stripe settings are configured via environment variables.
                Please refer to <code>STRIPE_SETUP_GUIDE.md</code> for detailed configuration instructions.
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

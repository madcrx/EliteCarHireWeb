<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Notification Settings - Email Notifications</h1>

        <div class="card">
            <h3>Email Notification Configuration</h3>
            <p>Email notification preferences and template management will be available here soon.</p>

            <p>Future features will include:</p>
            <ul>
                <li>Booking confirmation emails</li>
                <li>Payment receipt emails</li>
                <li>Booking reminder emails</li>
                <li>Cancellation notification emails</li>
                <li>Owner payout notifications</li>
                <li>Admin alert emails</li>
                <li>Custom email templates</li>
            </ul>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Booking Settings - Rules & Policies</h1>

        <div class="card">
            <h3>Booking Configuration</h3>
            <p>Booking rules, policies, and settings management will be available here soon.</p>

            <p>Future features will include:</p>
            <ul>
                <li>Minimum/maximum booking duration</li>
                <li>Advance booking requirements</li>
                <li>Cancellation policies</li>
                <li>Security deposit rules</li>
                <li>Age restrictions</li>
                <li>Insurance requirements</li>
            </ul>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

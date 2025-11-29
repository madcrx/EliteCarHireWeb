<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Booking Analytics</h1>

        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="stat-card">
                <h3>Total Bookings</h3>
                <div class="stat-value"><?= number_format($totalBookings) ?></div>
            </div>
            <div class="stat-card">
                <h3>Completed</h3>
                <div class="stat-value"><?= number_format($completedBookings) ?></div>
            </div>
        </div>

        <div class="card">
            <h3>Booking Trends</h3>
            <p>Detailed booking analytics and charts will be available here soon.</p>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

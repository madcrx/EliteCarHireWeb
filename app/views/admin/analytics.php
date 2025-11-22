<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Analytics</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <h2><?= $stats['total_bookings'] ?? 0 ?></h2>
                <p>Total Bookings</p>
            </div>
            <div class="stat-card">
                <h2>$<?= number_format($stats['total_revenue'] ?? 0, 2) ?></h2>
                <p>Total Revenue</p>
            </div>
            <div class="stat-card">
                <h2><?= $stats['active_users'] ?? 0 ?></h2>
                <p>Active Users</p>
            </div>
            <div class="stat-card">
                <h2><?= $stats['total_vehicles'] ?? 0 ?></h2>
                <p>Total Vehicles</p>
            </div>
        </div>

        <div class="card" style="margin-top: 2rem;">
            <h2>Recent Activity</h2>
            <p>Analytics dashboard with charts and graphs would be displayed here.</p>
            <p style="color: var(--medium-gray);">Integration with Chart.js or similar library required for visual analytics.</p>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

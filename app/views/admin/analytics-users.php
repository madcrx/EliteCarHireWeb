<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>User Statistics</h1>

        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="stat-value"><?= number_format($totalUsers) ?></div>
            </div>
            <div class="stat-card">
                <h3>Customers</h3>
                <div class="stat-value"><?= number_format($customerCount) ?></div>
            </div>
            <div class="stat-card">
                <h3>Vehicle Owners</h3>
                <div class="stat-value"><?= number_format($ownerCount) ?></div>
            </div>
        </div>

        <div class="card">
            <h3>User Growth</h3>
            <p>Detailed user statistics and growth charts will be available here soon.</p>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

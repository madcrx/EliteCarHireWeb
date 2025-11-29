<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Vehicle Performance</h1>

        <div class="card">
            <h3>Top Performing Vehicles</h3>
            <?php if (empty($topVehicles)): ?>
                <p>No vehicle data available.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Make & Model</th>
                            <th>Total Bookings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topVehicles as $vehicle): ?>
                            <tr>
                                <td>#<?= $vehicle['id'] ?></td>
                                <td><?= htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) ?></td>
                                <td><?= $vehicle['booking_count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <a href="/admin/clear-cache" class="btn btn-secondary" onclick="return confirm('Clear system cache? This will reset OPcache and file caches.')">
                    <i class="fas fa-sync-alt"></i> Clear Cache
                </a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= $stats['total_users'] ?></h3>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['pending_users'] ?></h3>
                <p>Pending Approvals</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['total_vehicles'] ?></h3>
                <p>Total Vehicles</p>
            </div>
            <div class="stat-card">
                <h3><?= formatMoney($stats['total_revenue']) ?></h3>
                <p>Monthly Revenue</p>
            </div>
        </div>

        <div class="card">
            <h2>Recent Bookings</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBookings as $booking): ?>
                            <tr>
                                <td><?= e($booking['booking_reference']) ?></td>
                                <td><?= e($booking['first_name'] . ' ' . $booking['last_name']) ?></td>
                                <td><?= e($booking['make'] . ' ' . $booking['model']) ?></td>
                                <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                                <td><?= formatMoney($booking['total_amount']) ?></td>
                                <td><span class="badge badge-<?= $booking['status'] === 'completed' ? 'success' : 'warning' ?>"><?= ucfirst($booking['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php if (!empty($recentUsers)): ?>
        <div class="card">
            <h2>Pending User Approvals</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td><?= e($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                <td><?= e($user['email']) ?></td>
                                <td><?= ucfirst($user['role']) ?></td>
                                <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <form method="POST" action="/admin/users/<?= $user['id'] ?>/approve" style="display: inline;">
                                        <button class="btn btn-primary" style="padding: 5px 15px;">Approve</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

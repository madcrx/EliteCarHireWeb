<?php ob_start(); ?>
<div class="container dashboard">
    <h1>Welcome, <?= e($_SESSION['name']) ?></h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= $stats['total_bookings'] ?></h3>
            <p>Total Bookings</p>
        </div>
        <div class="stat-card">
            <h3><?= $stats['active_bookings'] ?></h3>
            <p>Active Bookings</p>
        </div>
        <div class="stat-card">
            <h3><?= $stats['completed_bookings'] ?></h3>
            <p>Completed</p>
        </div>
    </div>
    
    <?php if (!empty($upcomingBookings)): ?>
    <div class="card">
        <h2>Upcoming Bookings</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Vehicle</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcomingBookings as $booking): ?>
                        <tr>
                            <td><?= e($booking['booking_reference']) ?></td>
                            <td><?= e($booking['make'] . ' ' . $booking['model']) ?></td>
                            <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                            <td><?= date('g:i A', strtotime($booking['start_time'])) ?></td>
                            <td><span class="badge badge-<?= $booking['status'] === 'confirmed' ? 'success' : 'info' ?>"><?= ucfirst($booking['status']) ?></span></td>
                            <td><span class="badge badge-<?= $booking['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($booking['payment_status']) ?></span></td>
                            <td>
                                <a href="/customer/bookings/<?= $booking['id'] ?>" class="btn btn-sm btn-<?= ($booking['status'] === 'confirmed' && $booking['payment_status'] !== 'paid') ? 'primary' : 'secondary' ?>">
                                    <?= ($booking['status'] === 'confirmed' && $booking['payment_status'] !== 'paid') ? 'Pay Now' : 'View Details' ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 2rem;">
        <a href="/vehicles" class="btn btn-primary">Browse Vehicles</a>
        <a href="/customer/bookings" class="btn btn-secondary">View All Bookings</a>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

<?php ob_start(); ?>
<div class="sidebar-layout">
    <div class="sidebar">
        <ul>
            <li><a href="/owner/dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="/owner/listings"><i class="fas fa-car"></i> My Listings</a></li>
            <li><a href="/owner/bookings"><i class="fas fa-calendar"></i> Bookings</a></li>
            <li><a href="/owner/calendar"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
            <li><a href="/owner/analytics"><i class="fas fa-chart-line"></i> Analytics</a></li>
            <li><a href="/owner/payouts"><i class="fas fa-money-bill"></i> Payouts</a></li>
            <li><a href="/owner/reviews"><i class="fas fa-star"></i> Reviews</a></li>
            <li><a href="/owner/messages"><i class="fas fa-envelope"></i> Messages</a></li>
            <li><a href="/owner/pending-changes"><i class="fas fa-clock"></i> Pending Changes</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h1>Owner Dashboard</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= $stats['total_vehicles'] ?></h3>
                <p>Total Vehicles</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['active_bookings'] ?></h3>
                <p>Active Bookings</p>
            </div>
            <div class="stat-card">
                <h3><?= formatMoney($stats['monthly_earnings']) ?></h3>
                <p>Monthly Earnings</p>
            </div>
            <div class="stat-card">
                <h3><?= formatMoney($stats['pending_payouts']) ?></h3>
                <p>Pending Payouts</p>
            </div>
        </div>
        
        <div class="card">
            <h2>Recent Bookings</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Vehicle</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBookings as $booking): ?>
                            <tr>
                                <td><?= e($booking['booking_reference']) ?></td>
                                <td><?= e($booking['make'] . ' ' . $booking['model']) ?></td>
                                <td><?= e($booking['first_name'] . ' ' . $booking['last_name']) ?></td>
                                <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                                <td><?= formatMoney($booking['total_amount'] - $booking['commission_amount']) ?></td>
                                <td><span class="badge badge-<?= $booking['status'] === 'completed' ? 'success' : 'warning' ?>"><?= ucfirst($booking['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

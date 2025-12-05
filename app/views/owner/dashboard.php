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

        <!-- Stripe Connect Warning -->
        <?php if (!$hasStripeConnected): ?>
            <div class="card" style="background: #fff3cd; border-left: 4px solid #f39c12; margin-bottom: 1.5rem;">
                <h3 style="margin-top: 0; color: #d68910;">
                    <i class="fas fa-exclamation-triangle"></i> ACTION REQUIRED: Connect Your Bank Account
                </h3>
                <p style="margin-bottom: 1rem; color: #856404;">
                    <strong>Hi <?= e($ownerName) ?>!</strong> To receive payouts and confirm customer bookings, you must connect your bank account through Stripe Connect.
                </p>
                <div style="background: #ffffff; padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1rem;">
                    <h4 style="margin-top: 0; color: #d68910;">What you'll need:</h4>
                    <ul style="margin: 0.5rem 0; padding-left: 1.5rem; color: #856404;">
                        <li>Australian Business Number (ABN) or Personal details</li>
                        <li>Bank account details (BSB and Account number)</li>
                        <li>Government-issued ID (Driver's license or passport)</li>
                        <li>5-10 minutes to complete the process</li>
                    </ul>
                </div>
                <p style="margin-bottom: 1rem; font-size: 0.9rem; color: #856404;">
                    <i class="fas fa-info-circle"></i> <strong>Note:</strong> You won't be able to confirm bookings or receive payments until your Stripe account is connected and verified.
                </p>
                <a href="/owner/stripe/connect" class="btn btn-warning" style="background: #f39c12; border-color: #f39c12;">
                    <i class="fas fa-link"></i> Connect Stripe Account Now
                </a>
            </div>
        <?php else: ?>
            <div class="card" style="background: #d4edda; border-left: 4px solid #28a745; margin-bottom: 1.5rem;">
                <p style="margin: 0; color: #155724;">
                    <i class="fas fa-check-circle"></i> <strong>Account Connected & Verified</strong> - You can receive payouts and confirm bookings.
                </p>
            </div>
        <?php endif; ?>

        <!-- Notifications -->
        <?php if (!empty($notifications)): ?>
            <div class="card" style="background: #e3f2fd; border-left: 4px solid #2196f3; margin-bottom: 1.5rem;">
                <h3 style="margin-top: 0; color: #1976d2;">
                    <i class="fas fa-bell"></i> Notifications
                    <?php if ($notificationCount > 0): ?>
                        <span class="badge badge-primary" style="font-size: 0.8rem;"><?= $notificationCount ?></span>
                    <?php endif; ?>
                </h3>
                <div style="max-height: 300px; overflow-y: auto;">
                    <?php foreach ($notifications as $notification): ?>
                        <div style="padding: 0.75rem; margin-bottom: 0.5rem; background: white; border-radius: var(--border-radius); border-left: 3px solid #2196f3;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <strong style="color: #1976d2;"><?= e($notification['title']) ?></strong>
                                <span style="font-size: 0.8rem; color: var(--dark-gray);">
                                    <?= timeAgo($notification['created_at']) ?>
                                </span>
                            </div>
                            <p style="margin: 0; color: var(--dark-gray); font-size: 0.9rem;">
                                <?= e($notification['message']) ?>
                            </p>
                            <?php if (!empty($notification['link'])): ?>
                                <a href="<?= e($notification['link']) ?>" style="font-size: 0.85rem; color: #2196f3; text-decoration: none;">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($notificationCount > 5): ?>
                    <p style="margin: 1rem 0 0 0; text-align: center;">
                        <a href="/owner/notifications" class="btn btn-primary btn-sm">
                            View All Notifications (<?= $notificationCount ?>)
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

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
            <?php if (empty($recentBookings)): ?>
                <p style="text-align: center; padding: 2rem; color: var(--dark-gray);">
                    <i class="fas fa-calendar" style="font-size: 3rem; color: var(--light-gray); margin-bottom: 1rem;"></i><br>
                    No bookings yet. When customers book your vehicles, they'll appear here.
                </p>
            <?php else: ?>
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
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

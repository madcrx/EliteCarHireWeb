<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1><i class="fas fa-chart-line"></i> Analytics</h1>
        <p style="color: var(--dark-gray); margin-bottom: 2rem;">
            Track your performance, earnings, and booking trends over time.
        </p>

        <?php if (empty($monthlyData)): ?>
            <div class="card" style="text-align: center; background: var(--light-gray);">
                <i class="fas fa-chart-bar" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 1rem;"></i>
                <h3>No Data Available Yet</h3>
                <p>Complete some bookings to see your analytics data here.</p>
                <a href="/owner/dashboard" class="btn btn-primary" style="margin-top: 1rem;">Return to Dashboard</a>
            </div>
        <?php else: ?>
            <div class="card">
                <h2><i class="fas fa-calendar-alt"></i> Monthly Performance</h2>
                <p style="margin-bottom: 1.5rem; color: var(--dark-gray);">
                    Your earnings and booking history over the past 12 months.
                </p>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Bookings</th>
                                <th>Earnings</th>
                                <th>Avg per Booking</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthlyData as $data): ?>
                                <?php
                                    $avgPerBooking = $data['bookings'] > 0 ? $data['earnings'] / $data['bookings'] : 0;
                                ?>
                                <tr>
                                    <td><?= date('F Y', strtotime($data['month'] . '-01')) ?></td>
                                    <td><span class="badge badge-info"><?= $data['bookings'] ?></span></td>
                                    <td style="color: var(--success); font-weight: bold;">$<?= number_format($data['earnings'], 2) ?></td>
                                    <td>$<?= number_format($avgPerBooking, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; background: var(--light-gray);">
                                <td>Total</td>
                                <td><?= array_sum(array_column($monthlyData, 'bookings')) ?> bookings</td>
                                <td style="color: var(--success);">$<?= number_format(array_sum(array_column($monthlyData, 'earnings')), 2) ?></td>
                                <td>-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card" style="background: var(--light-gray);">
                <h3><i class="fas fa-info-circle"></i> Insights & Tips</h3>
                <ul>
                    <li><strong>Peak Months:</strong> Identify your busiest months and prepare inventory accordingly</li>
                    <li><strong>Earnings Trends:</strong> Monitor month-over-month growth to track business health</li>
                    <li><strong>Booking Patterns:</strong> Use historical data to predict future demand</li>
                    <li><strong>Optimize Pricing:</strong> Adjust hourly rates based on seasonal demand</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

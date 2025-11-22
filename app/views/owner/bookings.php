<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1><i class="fas fa-calendar-check"></i> Bookings</h1>
        <p style="color: var(--dark-gray); margin-bottom: 2rem;">
            Manage all bookings for your vehicles.
        </p>

        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="margin-bottom: 0;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-gray);">Filter by Status:</label>
                <a href="/owner/bookings?status=all" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                <a href="/owner/bookings?status=pending" class="btn <?= $status === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
                <a href="/owner/bookings?status=confirmed" class="btn <?= $status === 'confirmed' ? 'btn-primary' : 'btn-secondary' ?>">Confirmed</a>
                <a href="/owner/bookings?status=in_progress" class="btn <?= $status === 'in_progress' ? 'btn-primary' : 'btn-secondary' ?>">In Progress</a>
                <a href="/owner/bookings?status=completed" class="btn <?= $status === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Completed</a>
                <a href="/owner/bookings?status=cancelled" class="btn <?= $status === 'cancelled' ? 'btn-primary' : 'btn-secondary' ?>">Cancelled</a>
            </div>
        </div>

        <?php if (empty($bookings)): ?>
            <div class="card" style="text-align: center; background: var(--light-gray);">
                <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 1rem;"></i>
                <h3>No Bookings Yet</h3>
                <p>You don't have any bookings at the moment. Customers will be able to book your approved vehicles.</p>
                <a href="/owner/listings" class="btn btn-primary" style="margin-top: 1rem;">View My Listings</a>
            </div>
        <?php else: ?>
            <div class="card">
                <h2><i class="fas fa-list"></i> All Bookings</h2>
                <p style="margin-bottom: 1.5rem; color: var(--dark-gray);">
                    Complete history of all bookings for your vehicles.
                </p>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Booking Ref</th>
                                <th>Vehicle</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Duration</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><strong><?= e($booking['booking_reference']) ?></strong></td>
                                    <td>
                                        <?= e($booking['make'] . ' ' . $booking['model']) ?>
                                    </td>
                                    <td><?= e($booking['first_name'] . ' ' . $booking['last_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                                    <td><?= date('g:i A', strtotime($booking['start_time'])) ?> - <?= date('g:i A', strtotime($booking['end_time'])) ?></td>
                                    <td><?= $booking['duration_hours'] ?> hrs</td>
                                    <td style="font-weight: bold; color: var(--success);">$<?= number_format($booking['total_amount'], 2) ?></td>
                                    <td>
                                        <?php
                                            $statusClass = match($booking['status']) {
                                                'confirmed' => 'success',
                                                'in_progress' => 'warning',
                                                'completed' => 'info',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>
                                        <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($booking['status']) ?></span>
                                    </td>
                                    <td>
                                        <?php
                                            $paymentClass = match($booking['payment_status']) {
                                                'paid' => 'success',
                                                'pending' => 'warning',
                                                'refunded' => 'secondary',
                                                'failed' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>
                                        <span class="badge badge-<?= $paymentClass ?>"><?= ucfirst($booking['payment_status']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card" style="background: var(--light-gray);">
                <h3><i class="fas fa-info-circle"></i> Booking Information</h3>
                <ul>
                    <li><strong>Confirmed:</strong> Booking is confirmed and vehicle is reserved</li>
                    <li><strong>In Progress:</strong> Booking is currently active</li>
                    <li><strong>Completed:</strong> Booking has been completed successfully</li>
                    <li><strong>Cancelled:</strong> Booking was cancelled</li>
                    <li><strong>Commission:</strong> 15% platform fee applies to all bookings</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

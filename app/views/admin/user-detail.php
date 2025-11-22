<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>User Details</h1>
            <div style="display: flex; gap: 1rem;">
                <a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit User
                </a>
                <a href="/admin/users" class="btn" style="background: var(--medium-gray);">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>

        <div class="card">
            <h2><?= e($user['first_name'] . ' ' . $user['last_name']) ?></h2>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1.5rem;">
                <div>
                    <p><strong>Email:</strong> <?= e($user['email']) ?></p>
                    <p><strong>Phone:</strong> <?= e($user['phone'] ?? 'Not provided') ?></p>
                    <p><strong>Role:</strong> <?= ucfirst(e($user['role'])) ?></p>
                </div>
                <div>
                    <p><strong>Status:</strong>
                        <?php if ($user['status'] === 'active'): ?>
                            <span class="badge badge-success">Active</span>
                        <?php elseif ($user['status'] === 'pending'): ?>
                            <span class="badge badge-warning">Pending</span>
                        <?php elseif ($user['status'] === 'suspended'): ?>
                            <span class="badge badge-danger">Suspended</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Rejected</span>
                        <?php endif; ?>
                    </p>
                    <p><strong>Joined:</strong> <?= date('M d, Y', strtotime($user['created_at'])) ?></p>
                    <p><strong>Last Login:</strong> <?= $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never' ?></p>
                </div>
            </div>

            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--medium-gray);">
                <?php if ($user['status'] === 'pending'): ?>
                    <form method="POST" action="/admin/users/<?= $user['id'] ?>/approve" style="display: inline-block; margin-right: 1rem;">
                        <button type="submit" class="btn btn-primary">Approve User</button>
                    </form>
                    <form method="POST" action="/admin/users/<?= $user['id'] ?>/reject" style="display: inline-block;">
                        <button type="submit" class="btn btn-danger">Reject User</button>
                    </form>
                <?php elseif ($user['status'] === 'active'): ?>
                    <button class="btn btn-warning">Suspend User</button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($user['role'] === 'owner' && !empty($vehicles)): ?>
            <div class="card" style="margin-top: 2rem;">
                <h3>Owned Vehicles (<?= count($vehicles) ?>)</h3>
                <div class="table-container" style="margin-top: 1rem;">
                    <table>
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Year</th>
                                <th>Category</th>
                                <th>Rate</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <tr>
                                    <td><?= e($vehicle['make'] . ' ' . $vehicle['model']) ?></td>
                                    <td><?= e($vehicle['year']) ?></td>
                                    <td><?= ucwords(str_replace('_', ' ', e($vehicle['category']))) ?></td>
                                    <td>$<?= number_format($vehicle['hourly_rate'], 2) ?>/hr</td>
                                    <td>
                                        <?php if ($vehicle['status'] === 'approved'): ?>
                                            <span class="badge badge-success">Approved</span>
                                        <?php elseif ($vehicle['status'] === 'pending'): ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php else: ?>
                                            <span class="badge badge-info"><?= ucfirst(e($vehicle['status'])) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($bookings)): ?>
            <div class="card" style="margin-top: 2rem;">
                <h3>Recent Bookings (<?= count($bookings) ?>)</h3>
                <div class="table-container" style="margin-top: 1rem;">
                    <table>
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Date</th>
                                <th>Duration</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?= e($booking['booking_reference']) ?></td>
                                    <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                                    <td><?= e($booking['duration_hours']) ?> hours</td>
                                    <td>$<?= number_format($booking['total_amount'], 2) ?></td>
                                    <td>
                                        <?php if ($booking['status'] === 'confirmed'): ?>
                                            <span class="badge badge-success">Confirmed</span>
                                        <?php elseif ($booking['status'] === 'pending'): ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php elseif ($booking['status'] === 'completed'): ?>
                                            <span class="badge badge-info">Completed</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger"><?= ucfirst(e($booking['status'])) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($vehicles) && empty($bookings)): ?>
            <div class="card" style="margin-top: 2rem;">
                <p>No activity recorded for this user yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

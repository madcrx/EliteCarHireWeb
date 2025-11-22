<?php ob_start(); ?>
<div class="sidebar-layout">
    <div class="sidebar">
        <ul>
            <li><a href="/owner/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="/owner/listings" class="active"><i class="fas fa-car"></i> My Listings</a></li>
            <li><a href="/owner/bookings"><i class="fas fa-calendar"></i> Bookings</a></li>
            <li><a href="/owner/calendar"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
            <li><a href="/owner/analytics"><i class="fas fa-chart-line"></i> Analytics</a></li>
            <li><a href="/owner/payouts"><i class="fas fa-money-bill"></i> Payouts</a></li>
            <li><a href="/owner/reviews"><i class="fas fa-star"></i> Reviews</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="dashboard-header">
            <h1>My Vehicle Listings</h1>
            <a href="/owner/listings/add" class="btn btn-primary">Add New Vehicle</a>
        </div>

        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="margin-bottom: 0;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-gray);">Filter by Status:</label>
                <a href="/owner/listings?status=all" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                <a href="/owner/listings?status=pending" class="btn <?= $status === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
                <a href="/owner/listings?status=approved" class="btn <?= $status === 'approved' ? 'btn-primary' : 'btn-secondary' ?>">Approved</a>
                <a href="/owner/listings?status=active" class="btn <?= $status === 'active' ? 'btn-primary' : 'btn-secondary' ?>">Active</a>
                <a href="/owner/listings?status=inactive" class="btn <?= $status === 'inactive' ? 'btn-primary' : 'btn-secondary' ?>">Inactive</a>
            </div>
        </div>

        <div class="vehicle-grid">
            <?php foreach ($vehicles as $vehicle): ?>
                <div class="vehicle-card">
                    <div class="vehicle-card-body">
                        <h3><?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?></h3>
                        <p><?= e($vehicle['year']) ?> • <?= ucwords(str_replace('_', ' ', $vehicle['category'])) ?></p>
                        <p style="color: var(--primary-gold); font-weight: bold;"><?= formatMoney($vehicle['hourly_rate']) ?>/hour</p>
                        <span class="badge badge-<?= $vehicle['status'] === 'approved' ? 'success' : 'warning' ?>">
                            <?= ucfirst($vehicle['status']) ?>
                        </span>
                        <div style="margin-top: 1rem;">
                            <a href="/owner/listings/<?= $vehicle['id'] ?>/edit" class="btn btn-secondary">Edit</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

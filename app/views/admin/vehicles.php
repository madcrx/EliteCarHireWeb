<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="dashboard-header">
            <h1>Vehicle Listings</h1>
        </div>
        
        <div class="card">
            <div style="margin-bottom: 1rem;">
                <a href="/admin/vehicles?status=all" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                <a href="/admin/vehicles?status=pending" class="btn <?= $status === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
                <a href="/admin/vehicles?status=approved" class="btn <?= $status === 'approved' ? 'btn-primary' : 'btn-secondary' ?>">Approved</a>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vehicle</th>
                            <th>Owner</th>
                            <th>Category</th>
                            <th>Hourly Rate</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <tr>
                                <td><?= $vehicle['id'] ?></td>
                                <td><?= e($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']) ?></td>
                                <td><?= e($vehicle['first_name'] . ' ' . $vehicle['last_name']) ?></td>
                                <td><?= ucwords(str_replace('_', ' ', $vehicle['category'])) ?></td>
                                <td><?= formatMoney($vehicle['hourly_rate']) ?></td>
                                <td><span class="badge badge-<?= $vehicle['status'] === 'approved' ? 'success' : 'warning' ?>"><?= ucfirst($vehicle['status']) ?></span></td>
                                <td>
                                    <a href="/admin/vehicles/<?= $vehicle['id'] ?>/edit" class="btn btn-secondary" style="padding: 5px 10px;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php if ($vehicle['status'] === 'pending'): ?>
                                        <a href="/admin/vehicles/<?= $vehicle['id'] ?>/approve" class="btn btn-primary" style="padding: 5px 10px;">
                                            <i class="fas fa-check"></i> Approve
                                        </a>
                                    <?php endif; ?>
                                    <form method="POST" action="/admin/vehicles/<?= $vehicle['id'] ?>/delete" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this vehicle? This will also delete all related bookings and data. This action cannot be undone.');">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <button type="submit" class="btn" style="padding: 5px 10px; background: var(--danger); color: white;">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

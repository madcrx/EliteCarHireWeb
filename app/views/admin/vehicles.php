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
                                    <form method="POST" action="/admin/vehicles/<?= $vehicle['id'] ?>/delete" style="display: inline;" onsubmit="return confirmDeleteVehicle('<?= e($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']) ?>');">
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

<script>
function confirmDeleteVehicle(vehicleName) {
    // First confirmation
    if (!confirm('⚠️ WARNING: You are about to permanently delete ' + vehicleName + '.\n\n' +
                 'This will DELETE ALL related data including:\n' +
                 '• All bookings (even paid/completed ones)\n' +
                 '• All payment records\n' +
                 '• All payout records\n' +
                 '• All vehicle images\n\n' +
                 'This action CANNOT be undone!\n\n' +
                 'Click OK to proceed to confirmation step.')) {
        return false;
    }

    // Second confirmation - require typing DELETE
    var confirmation = prompt('⚠️ FINAL CONFIRMATION REQUIRED\n\n' +
                             'To confirm deletion of ' + vehicleName + ', please type DELETE in capital letters:');

    if (confirmation === 'DELETE') {
        return true;
    } else {
        alert('Deletion cancelled. You must type DELETE exactly to confirm.');
        return false;
    }
}
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

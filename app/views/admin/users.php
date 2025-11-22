<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="dashboard-header">
            <h1>User Management</h1>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 1rem;"><i class="fas fa-filter"></i> Filters</h3>
            <form method="GET" action="/admin/users" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--medium-gray);">
                <div class="form-group" style="margin: 0;">
                    <label for="role">Role</label>
                    <select name="role" id="role" onchange="this.form.submit()">
                        <option value="all" <?= $role === 'all' ? 'selected' : '' ?>>All Roles</option>
                        <option value="customer" <?= $role === 'customer' ? 'selected' : '' ?>>Customer</option>
                        <option value="owner" <?= $role === 'owner' ? 'selected' : '' ?>>Owner</option>
                        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label for="status">Status</label>
                    <select name="status" id="status" onchange="this.form.submit()">
                        <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="suspended" <?= $status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <a href="/admin/users" class="btn" style="background: var(--medium-gray);">Clear Filters</a>
                </div>
            </form>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= e($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                <td><?= e($user['email']) ?></td>
                                <td><?= ucfirst($user['role']) ?></td>
                                <td><span class="badge badge-<?= $user['status'] === 'active' ? 'success' : 'warning' ?>"><?= ucfirst($user['status']) ?></span></td>
                                <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-secondary" style="padding: 5px 10px;"><i class="fas fa-edit"></i></a>

                                    <?php if ($user['status'] === 'pending'): ?>
                                        <form method="POST" action="/admin/users/<?= $user['id'] ?>/approve" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                            <button class="btn btn-primary" style="padding: 5px 10px;"><i class="fas fa-check"></i></button>
                                        </form>
                                    <?php endif; ?>

                                    <!-- Status Change Dropdown -->
                                    <select onchange="if(this.value && confirm('Change user status to ' + this.value + '?')) { document.getElementById('status-form-<?= $user['id'] ?>-' + this.value).submit(); } else { this.value = ''; }" class="btn" style="padding: 5px 10px; cursor: pointer;">
                                        <option value="">Status...</option>
                                        <?php if ($user['status'] !== 'active'): ?><option value="active">Activate</option><?php endif; ?>
                                        <?php if ($user['status'] !== 'suspended'): ?><option value="suspended">Suspend</option><?php endif; ?>
                                        <?php if ($user['status'] !== 'rejected'): ?><option value="rejected">Reject</option><?php endif; ?>
                                    </select>

                                    <!-- Hidden forms for status changes -->
                                    <?php foreach (['active', 'suspended', 'rejected'] as $newStatus): ?>
                                        <form id="status-form-<?= $user['id'] ?>-<?= $newStatus ?>" method="POST" action="/admin/users/<?= $user['id'] ?>/change-status" style="display: none;">
                                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                            <input type="hidden" name="status" value="<?= $newStatus ?>">
                                        </form>
                                    <?php endforeach; ?>

                                    <!-- Delete Button -->
                                    <form method="POST" action="/admin/users/<?= $user['id'] ?>/delete" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user? This will also delete all their related data. This action cannot be undone.');">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <button type="submit" class="btn" style="padding: 5px 10px; background: var(--danger); color: white;"><i class="fas fa-trash"></i></button>
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

<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="dashboard-header">
            <h1>User Management</h1>
        </div>
        
        <div class="card">
            <div style="margin-bottom: 1rem;">
                <a href="/admin/users?status=all" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                <a href="/admin/users?status=pending" class="btn <?= $status === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
                <a href="/admin/users?status=active" class="btn <?= $status === 'active' ? 'btn-primary' : 'btn-secondary' ?>">Active</a>
            </div>
            
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
                                    <a href="/admin/users/<?= $user['id'] ?>" class="btn btn-secondary" style="padding: 5px 10px;">View</a>
                                    <?php if ($user['status'] === 'pending'): ?>
                                        <form method="POST" action="/admin/users/<?= $user['id'] ?>/approve" style="display: inline;">
                                            <button class="btn btn-primary" style="padding: 5px 10px;">Approve</button>
                                        </form>
                                    <?php endif; ?>
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

<?php
// Fetch notification counts for badges
$pendingUsers = db()->fetch("SELECT COUNT(*) as count FROM users WHERE status='pending'")['count'] ?? 0;
$pendingVehicles = db()->fetch("SELECT COUNT(*) as count FROM vehicles WHERE status='pending'")['count'] ?? 0;
$pendingBookings = db()->fetch("SELECT COUNT(*) as count FROM bookings WHERE status='pending'")['count'] ?? 0;
$pendingChanges = db()->fetch("SELECT COUNT(*) as count FROM pending_changes WHERE status='pending'")['count'] ?? 0;
$newContacts = db()->fetch("SELECT COUNT(*) as count FROM contact_submissions WHERE status='new'")['count'] ?? 0;
$activeDisputes = db()->fetch("SELECT COUNT(*) as count FROM disputes WHERE status IN ('open', 'investigating')")['count'] ?? 0;
$failedPayments = db()->fetch("SELECT COUNT(*) as count FROM payments WHERE status='failed'")['count'] ?? 0;

// Get current path for active state
$currentPath = $_SERVER['REQUEST_URI'];
$isActive = function($path) use ($currentPath) {
    $currentPathBase = parse_url($currentPath, PHP_URL_PATH);
    $pathBase = parse_url($path, PHP_URL_PATH);

    if ($currentPathBase === $pathBase || strpos($currentPathBase, $pathBase) === 0) {
        return 'active';
    }
    return '';
};
?>

<div class="admin-sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-crown"></i> Admin Panel</h2>
        <span class="admin-badge">Administrator</span>
    </div>

    <div class="sidebar-nav">
        <!-- Dashboard -->
        <a href="/admin/dashboard" class="nav-item <?= $isActive('/admin/dashboard') ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>

        <!-- Main Management Sections -->
        <div class="nav-divider">Management</div>

        <a href="/admin/users" class="nav-item <?= $isActive('/admin/users') ?>">
            <i class="fas fa-users"></i>
            <span>Users</span>
            <?php if ($pendingUsers > 0): ?>
                <span class="badge-small"><?= $pendingUsers ?></span>
            <?php endif; ?>
        </a>

        <a href="/admin/vehicles" class="nav-item <?= $isActive('/admin/vehicles') ?>">
            <i class="fas fa-car"></i>
            <span>Vehicles</span>
            <?php if ($pendingVehicles > 0): ?>
                <span class="badge-small"><?= $pendingVehicles ?></span>
            <?php endif; ?>
        </a>

        <a href="/admin/bookings" class="nav-item <?= $isActive('/admin/bookings') ?>">
            <i class="fas fa-calendar-check"></i>
            <span>Bookings</span>
            <?php if ($pendingBookings > 0): ?>
                <span class="badge-small"><?= $pendingBookings ?></span>
            <?php endif; ?>
        </a>

        <a href="/admin/pending-changes" class="nav-item <?= $isActive('/admin/pending-changes') ?>">
            <i class="fas fa-edit"></i>
            <span>Vehicle Updates</span>
            <?php if ($pendingChanges > 0): ?>
                <span class="badge-small"><?= $pendingChanges ?></span>
            <?php endif; ?>
        </a>

        <!-- Financial -->
        <div class="nav-divider">Financial</div>

        <a href="/admin/payments" class="nav-item <?= $isActive('/admin/payments') ?>">
            <i class="fas fa-credit-card"></i>
            <span>Payments</span>
            <?php if ($failedPayments > 0): ?>
                <span class="badge-small warning"><?= $failedPayments ?></span>
            <?php endif; ?>
        </a>

        <a href="/admin/payouts" class="nav-item <?= $isActive('/admin/payouts') ?>">
            <i class="fas fa-money-bill-wave"></i>
            <span>Payouts</span>
        </a>

        <a href="/admin/disputes" class="nav-item <?= $isActive('/admin/disputes') ?>">
            <i class="fas fa-gavel"></i>
            <span>Disputes</span>
            <?php if ($activeDisputes > 0): ?>
                <span class="badge-small danger"><?= $activeDisputes ?></span>
            <?php endif; ?>
        </a>

        <!-- Communication & Content -->
        <div class="nav-divider">Communication</div>

        <a href="/admin/contact-submissions" class="nav-item <?= $isActive('/admin/contact-submissions') ?>">
            <i class="fas fa-envelope"></i>
            <span>Contact Messages</span>
            <?php if ($newContacts > 0): ?>
                <span class="badge-small"><?= $newContacts ?></span>
            <?php endif; ?>
        </a>

        <a href="/admin/email-settings" class="nav-item <?= $isActive('/admin/email-settings') ?>">
            <i class="fas fa-mail-bulk"></i>
            <span>Email Settings</span>
        </a>

        <a href="/admin/cms" class="nav-item <?= $isActive('/admin/cms') ?>">
            <i class="fas fa-file-alt"></i>
            <span>CMS Pages</span>
        </a>

        <a href="/admin/images" class="nav-item <?= $isActive('/admin/images') ?>">
            <i class="fas fa-images"></i>
            <span>Website Images</span>
        </a>

        <!-- Analytics & Reports -->
        <div class="nav-divider">Analytics</div>

        <a href="/admin/analytics" class="nav-item <?= $isActive('/admin/analytics') ?>">
            <i class="fas fa-chart-line"></i>
            <span>Analytics Dashboard</span>
        </a>

        <a href="/admin/analytics/revenue" class="nav-item <?= $isActive('/admin/analytics/revenue') ?>">
            <i class="fas fa-dollar-sign"></i>
            <span>Revenue Reports</span>
        </a>

        <!-- Settings & System -->
        <div class="nav-divider">System</div>

        <a href="/admin/settings" class="nav-item <?= $isActive('/admin/settings') ?>">
            <i class="fas fa-cog"></i>
            <span>General Settings</span>
        </a>

        <a href="/admin/settings/payment" class="nav-item <?= $isActive('/admin/settings/payment') ?>">
            <i class="fas fa-credit-card"></i>
            <span>Payment Settings</span>
        </a>

        <a href="/admin/system-config" class="nav-item <?= $isActive('/admin/system-config') ?>">
            <i class="fas fa-server"></i>
            <span>System Configuration</span>
        </a>

        <a href="/admin/security" class="nav-item <?= $isActive('/admin/security') ?>">
            <i class="fas fa-shield-alt"></i>
            <span>Security & Logs</span>
        </a>
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="system-status">
            <span class="status-indicator online"></span>
            <span class="status-text">System Online</span>
        </div>
        <a href="/" target="_blank" class="view-site">
            <i class="fas fa-external-link-alt"></i>
            View Website
        </a>
    </div>
</div>

<style>
.admin-sidebar {
    width: 250px;
    height: 100vh;
    background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
    color: #fff;
    overflow-y: auto;
    position: fixed;
    left: 0;
    top: 0;
    box-shadow: 2px 0 10px rgba(0,0,0,0.3);
    z-index: 1000;
}

.sidebar-header {
    padding: 20px;
    background: rgba(0,0,0,0.2);
    border-bottom: 1px solid rgba(197,162,83,0.3);
}

.sidebar-header h2 {
    margin: 0 0 8px 0;
    font-size: 20px;
    color: #C5A253;
    display: flex;
    align-items: center;
    gap: 10px;
}

.admin-badge {
    display: inline-block;
    background: #C5A253;
    color: #1a1a2e;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.sidebar-nav {
    padding: 10px 0;
}

.nav-divider {
    padding: 15px 20px 8px 20px;
    font-size: 11px;
    font-weight: 700;
    color: rgba(197,162,83,0.6);
    text-transform: uppercase;
    letter-spacing: 1px;
    border-top: 1px solid rgba(255,255,255,0.05);
    margin-top: 10px;
}

.nav-divider:first-child {
    border-top: none;
    margin-top: 0;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    color: rgba(255,255,255,0.85);
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 14px;
    position: relative;
    border-left: 3px solid transparent;
}

.nav-item:hover {
    background: rgba(197,162,83,0.15);
    color: #fff;
    border-left-color: #C5A253;
}

.nav-item.active {
    background: rgba(197,162,83,0.2);
    color: #fff;
    border-left-color: #C5A253;
    font-weight: 600;
}

.nav-item i {
    width: 18px;
    text-align: center;
    opacity: 0.8;
}

.badge-small {
    background: #e74c3c;
    color: white;
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 10px;
    font-weight: 600;
    margin-left: auto;
}

.badge-small.warning {
    background: #f39c12;
}

.badge-small.danger {
    background: #c0392b;
}

.sidebar-footer {
    position: sticky;
    bottom: 0;
    background: rgba(0,0,0,0.3);
    padding: 15px 20px;
    border-top: 1px solid rgba(197,162,83,0.3);
}

.system-status {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    font-size: 12px;
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #2ecc71;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.view-site {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #C5A253;
    text-decoration: none;
    font-size: 13px;
    padding: 8px 12px;
    background: rgba(197,162,83,0.1);
    border-radius: 6px;
    transition: all 0.2s ease;
}

.view-site:hover {
    background: rgba(197,162,83,0.2);
}

/* Scrollbar Styling */
.admin-sidebar::-webkit-scrollbar {
    width: 6px;
}

.admin-sidebar::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.2);
}

.admin-sidebar::-webkit-scrollbar-thumb {
    background: rgba(197,162,83,0.5);
    border-radius: 3px;
}

.admin-sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(197,162,83,0.7);
}

/* Main content adjustment */
.sidebar-layout {
    display: flex;
}

.main-content {
    margin-left: 250px;
    flex: 1;
    min-height: 100vh;
    background: #f5f5f5;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .admin-sidebar.mobile-open {
        transform: translateX(0);
    }

    .main-content {
        margin-left: 0;
    }
}
</style>

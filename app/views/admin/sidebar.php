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
    return strpos($currentPath, $path) === 0 ? 'active' : '';
};
?>

<div class="admin-sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-crown"></i> Admin Panel</h2>
        <span class="admin-badge">Administrator</span>
    </div>

    <div class="sidebar-nav">
        <!-- Dashboard -->
        <div class="nav-section">
            <a href="/admin/dashboard" class="nav-item <?= $isActive('/admin/dashboard') ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Content Management -->
        <div class="nav-section">
            <div class="section-title" onclick="toggleSection(this)">
                <i class="fas fa-edit"></i>
                <span>Content Management</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </div>
            <div class="section-items">
                <a href="/admin/cms" class="nav-item <?= $isActive('/admin/cms') ?>">
                    <i class="fas fa-file-alt"></i>
                    <span>CMS Pages</span>
                    <small>Terms, Privacy, FAQ</small>
                </a>
                <a href="/admin/images" class="nav-item <?= $isActive('/admin/images') ?>">
                    <i class="fas fa-images"></i>
                    <span>Website Images</span>
                    <small>Placeholders & Graphics</small>
                </a>
                <a href="/admin/settings" class="nav-item <?= $isActive('/admin/settings') ?>">
                    <i class="fas fa-image"></i>
                    <span>Company Logo</span>
                    <small>Upload & Manage Logo</small>
                </a>
            </div>
        </div>

        <!-- User Management -->
        <div class="nav-section">
            <div class="section-title" onclick="toggleSection(this)">
                <i class="fas fa-users"></i>
                <span>User Management</span>
                <?php if ($pendingUsers > 0): ?>
                    <span class="notification-badge"><?= $pendingUsers ?></span>
                <?php endif; ?>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </div>
            <div class="section-items">
                <a href="/admin/users?role=all" class="nav-item <?= $isActive('/admin/users') ?>">
                    <i class="fas fa-user"></i>
                    <span>All Users</span>
                </a>
                <a href="/admin/users?role=customer" class="nav-item">
                    <i class="fas fa-user-circle"></i>
                    <span>Customers</span>
                </a>
                <a href="/admin/users?role=owner" class="nav-item">
                    <i class="fas fa-user-tie"></i>
                    <span>Vehicle Owners</span>
                </a>
                <a href="/admin/users?status=pending" class="nav-item">
                    <i class="fas fa-user-clock"></i>
                    <span>Pending Approvals</span>
                    <?php if ($pendingUsers > 0): ?>
                        <span class="badge-small"><?= $pendingUsers ?></span>
                    <?php endif; ?>
                </a>
                <a href="/admin/users?status=suspended" class="nav-item">
                    <i class="fas fa-user-slash"></i>
                    <span>Suspended Users</span>
                </a>
            </div>
        </div>

        <!-- Vehicle Management -->
        <div class="nav-section">
            <div class="section-title" onclick="toggleSection(this)">
                <i class="fas fa-car"></i>
                <span>Vehicle Management</span>
                <?php if ($pendingVehicles > 0): ?>
                    <span class="notification-badge"><?= $pendingVehicles ?></span>
                <?php endif; ?>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </div>
            <div class="section-items">
                <a href="/admin/vehicles?status=all" class="nav-item <?= $isActive('/admin/vehicles') ?>">
                    <i class="fas fa-list"></i>
                    <span>All Vehicles</span>
                </a>
                <a href="/admin/vehicles?status=approved" class="nav-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Active Listings</span>
                </a>
                <a href="/admin/vehicles?status=pending" class="nav-item">
                    <i class="fas fa-clock"></i>
                    <span>Pending Approval</span>
                    <?php if ($pendingVehicles > 0): ?>
                        <span class="badge-small"><?= $pendingVehicles ?></span>
                    <?php endif; ?>
                </a>
                <a href="/admin/vehicles?status=rejected" class="nav-item">
                    <i class="fas fa-times-circle"></i>
                    <span>Rejected Listings</span>
                </a>
                <a href="/admin/pending-changes" class="nav-item">
                    <i class="fas fa-edit"></i>
                    <span>Vehicle Updates</span>
                    <?php if ($pendingChanges > 0): ?>
                        <span class="badge-small"><?= $pendingChanges ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Booking Management -->
        <div class="nav-section">
            <div class="section-title" onclick="toggleSection(this)">
                <i class="fas fa-calendar-check"></i>
                <span>Booking Management</span>
                <?php if ($pendingBookings > 0): ?>
                    <span class="notification-badge"><?= $pendingBookings ?></span>
                <?php endif; ?>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </div>
            <div class="section-items">
                <a href="/admin/bookings?status=all" class="nav-item <?= $isActive('/admin/bookings') ?>">
                    <i class="fas fa-list"></i>
                    <span>All Bookings</span>
                </a>
                <a href="/admin/bookings?status=pending" class="nav-item">
                    <i class="fas fa-hourglass-half"></i>
                    <span>Pending</span>
                    <?php if ($pendingBookings > 0): ?>
                        <span class="badge-small"><?= $pendingBookings ?></span>
                    <?php endif; ?>
                </a>
                <a href="/admin/bookings?status=confirmed" class="nav-item">
                    <i class="fas fa-check"></i>
                    <span>Confirmed</span>
                </a>
                <a href="/admin/bookings?status=in_progress" class="nav-item">
                    <i class="fas fa-clock"></i>
                    <span>In Progress</span>
                </a>
                <a href="/admin/bookings?status=completed" class="nav-item">
                    <i class="fas fa-check-double"></i>
                    <span>Completed</span>
                </a>
                <a href="/admin/bookings?status=cancelled" class="nav-item">
                    <i class="fas fa-ban"></i>
                    <span>Cancelled</span>
                </a>
            </div>
        </div>

        <!-- Financial Management -->
        <div class="nav-section">
            <div class="section-title" onclick="toggleSection(this)">
                <i class="fas fa-dollar-sign"></i>
                <span>Financial Management</span>
                <?php if ($failedPayments > 0): ?>
                    <span class="notification-badge warning"><?= $failedPayments ?></span>
                <?php endif; ?>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </div>
            <div class="section-items">
                <a href="/admin/payments?status=all" class="nav-item <?= $isActive('/admin/payments') ?>">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </a>
                <a href="/admin/payments?status=completed" class="nav-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Successful Payments</span>
                </a>
                <a href="/admin/payments?status=failed" class="nav-item">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Failed Payments</span>
                    <?php if ($failedPayments > 0): ?>
                        <span class="badge-small warning"><?= $failedPayments ?></span>
                    <?php endif; ?>
                </a>
                <a href="/admin/payments?status=refunded" class="nav-item">
                    <i class="fas fa-undo"></i>
                    <span>Refunds</span>
                </a>
                <a href="/admin/payouts?status=all" class="nav-item <?= $isActive('/admin/payouts') ?>">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Owner Payouts</span>
                </a>
                <a href="/admin/payouts?status=pending" class="nav-item">
                    <i class="fas fa-clock"></i>
                    <span>Pending Payouts</span>
                </a>
                <a href="/admin/payouts?status=completed" class="nav-item">
                    <i class="fas fa-check"></i>
                    <span>Completed Payouts</span>
                </a>
                <a href="/admin/disputes" class="nav-item <?= $isActive('/admin/disputes') ?>">
                    <i class="fas fa-gavel"></i>
                    <span>Disputes</span>
                    <?php if ($activeDisputes > 0): ?>
                        <span class="badge-small danger"><?= $activeDisputes ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Communication & Support -->
        <div class="nav-section">
            <div class="section-title" onclick="toggleSection(this)">
                <i class="fas fa-envelope"></i>
                <span>Communication</span>
                <?php if ($newContacts > 0): ?>
                    <span class="notification-badge"><?= $newContacts ?></span>
                <?php endif; ?>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </div>
            <div class="section-items">
                <a href="/admin/contact-submissions?status=all" class="nav-item <?= $isActive('/admin/contact-submissions') ?>">
                    <i class="fas fa-inbox"></i>
                    <span>Contact Submissions</span>
                    <?php if ($newContacts > 0): ?>
                        <span class="badge-small"><?= $newContacts ?></span>
                    <?php endif; ?>
                </a>
                <a href="/admin/contact-submissions?status=new" class="nav-item">
                    <i class="fas fa-envelope"></i>
                    <span>New Messages</span>
                </a>
                <a href="/admin/contact-submissions?status=responded" class="nav-item">
                    <i class="fas fa-reply"></i>
                    <span>Responded</span>
                </a>
                <a href="/admin/email-settings" class="nav-item">
                    <i class="fas fa-mail-bulk"></i>
                    <span>Email Settings</span>
                    <small>SMTP Configuration</small>
                </a>
                <a href="/admin/email-queue" class="nav-item">
                    <i class="fas fa-list"></i>
                    <span>Email Queue</span>
                    <small>View Pending Emails</small>
                </a>
            </div>
        </div>

        <!-- Reports & Analytics -->
        <div class="nav-section">
            <div class="section-title" onclick="toggleSection(this)">
                <i class="fas fa-chart-line"></i>
                <span>Reports & Analytics</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </div>
            <div class="section-items">
                <a href="/admin/analytics" class="nav-item <?= $isActive('/admin/analytics') ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Dashboard Analytics</span>
                </a>
                <a href="/admin/analytics/revenue" class="nav-item">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Revenue Reports</span>
                </a>
                <a href="/admin/analytics/bookings" class="nav-item">
                    <i class="fas fa-calendar"></i>
                    <span>Booking Analytics</span>
                </a>
                <a href="/admin/analytics/vehicles" class="nav-item">
                    <i class="fas fa-car"></i>
                    <span>Vehicle Performance</span>
                </a>
                <a href="/admin/analytics/users" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>User Statistics</span>
                </a>
            </div>
        </div>

        <!-- System Settings -->
        <div class="nav-section">
            <div class="section-title" onclick="toggleSection(this)">
                <i class="fas fa-cog"></i>
                <span>System Settings</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </div>
            <div class="section-items">
                <a href="/admin/settings" class="nav-item <?= $isActive('/admin/settings') ?>">
                    <i class="fas fa-sliders-h"></i>
                    <span>General Settings</span>
                    <small>Site Name, Logo, etc.</small>
                </a>
                <a href="/admin/settings/payment" class="nav-item">
                    <i class="fas fa-credit-card"></i>
                    <span>Payment Settings</span>
                    <small>Stripe Configuration</small>
                </a>
                <a href="/admin/settings/email" class="nav-item">
                    <i class="fas fa-envelope-open-text"></i>
                    <span>Email Configuration</span>
                    <small>SMTP & Templates</small>
                </a>
                <a href="/admin/settings/commission" class="nav-item">
                    <i class="fas fa-percent"></i>
                    <span>Commission Rates</span>
                    <small>Platform Fees</small>
                </a>
                <a href="/admin/settings/booking" class="nav-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Booking Settings</span>
                    <small>Rules & Policies</small>
                </a>
                <a href="/admin/settings/notifications" class="nav-item">
                    <i class="fas fa-bell"></i>
                    <span>Notification Settings</span>
                    <small>Email Notifications</small>
                </a>
            </div>
        </div>

        <!-- Security & Logs -->
        <div class="nav-section">
            <div class="section-title" onclick="toggleSection(this)">
                <i class="fas fa-shield-alt"></i>
                <span>Security & Logs</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </div>
            <div class="section-items">
                <a href="/admin/security" class="nav-item <?= $isActive('/admin/security') ?>">
                    <i class="fas fa-lock"></i>
                    <span>Security Alerts</span>
                </a>
                <a href="/admin/audit-logs" class="nav-item <?= $isActive('/admin/audit-logs') ?>">
                    <i class="fas fa-file-alt"></i>
                    <span>Audit Logs</span>
                    <small>User Actions</small>
                </a>
                <a href="/admin/logs/payment" class="nav-item">
                    <i class="fas fa-receipt"></i>
                    <span>Payment Logs</span>
                </a>
                <a href="/admin/logs/email" class="nav-item">
                    <i class="fas fa-mail-bulk"></i>
                    <span>Email Logs</span>
                </a>
                <a href="/admin/logs/login" class="nav-item">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login History</span>
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="nav-section quick-actions">
            <div class="section-title">
                <i class="fas fa-bolt"></i>
                <span>Quick Actions</span>
            </div>
            <div class="section-items">
                <button class="nav-item action-btn" onclick="window.location='/admin/users?status=pending'">
                    <i class="fas fa-user-check"></i>
                    <span>Approve Users</span>
                </button>
                <button class="nav-item action-btn" onclick="window.location='/admin/vehicles?status=pending'">
                    <i class="fas fa-car-side"></i>
                    <span>Approve Vehicles</span>
                </button>
                <button class="nav-item action-btn" onclick="window.location='/admin/contact-submissions?status=new'">
                    <i class="fas fa-reply"></i>
                    <span>Reply to Messages</span>
                </button>
                <button class="nav-item action-btn" onclick="clearCache()">
                    <i class="fas fa-sync"></i>
                    <span>Clear Cache</span>
                </button>
            </div>
        </div>
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
    width: 280px;
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

.nav-section {
    margin-bottom: 5px;
}

.section-title {
    padding: 12px 20px;
    font-weight: 600;
    font-size: 13px;
    color: #C5A253;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    position: relative;
}

.section-title:hover {
    background: rgba(197,162,83,0.1);
}

.section-title .toggle-icon {
    margin-left: auto;
    font-size: 10px;
    transition: transform 0.3s ease;
}

.section-title.collapsed .toggle-icon {
    transform: rotate(-90deg);
}

.section-items {
    max-height: 1000px;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.section-items.collapsed {
    max-height: 0;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px 12px 45px;
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

.nav-item small {
    display: block;
    font-size: 11px;
    opacity: 0.6;
    margin-top: 2px;
}

.notification-badge {
    background: #e74c3c;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
    margin-left: auto;
}

.notification-badge.warning {
    background: #f39c12;
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

.action-btn {
    background: none;
    border: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    color: rgba(255,255,255,0.85);
}

.action-btn:hover {
    background: rgba(197,162,83,0.15);
}

.quick-actions .nav-item {
    padding-left: 40px;
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

.status-indicator.online {
    background: #2ecc71;
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
    margin-left: 280px;
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

<script>
function toggleSection(element) {
    const sectionItems = element.nextElementSibling;
    const toggleIcon = element.querySelector('.toggle-icon');

    element.classList.toggle('collapsed');
    sectionItems.classList.toggle('collapsed');
}

function clearCache() {
    if (confirm('Are you sure you want to clear the system cache?')) {
        fetch('/admin/api/clear-cache', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cache cleared successfully!');
            } else {
                alert('Failed to clear cache');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

// Keep sections open/closed state in localStorage
document.addEventListener('DOMContentLoaded', function() {
    const sectionTitles = document.querySelectorAll('.section-title');

    sectionTitles.forEach((title, index) => {
        const storageKey = `sidebar-section-${index}`;
        const isCollapsed = localStorage.getItem(storageKey) === 'true';

        if (isCollapsed) {
            title.classList.add('collapsed');
            title.nextElementSibling.classList.add('collapsed');
        }

        title.addEventListener('click', function() {
            const collapsed = this.classList.contains('collapsed');
            localStorage.setItem(storageKey, !collapsed);
        });
    });
});
</script>

<?php
// Fetch notification counts for badges
$pendingUsers = db()->fetch("SELECT COUNT(*) as count FROM users WHERE status='pending'")['count'] ?? 0;
$pendingVehicles = db()->fetch("SELECT COUNT(*) as count FROM vehicles WHERE status='pending'")['count'] ?? 0;
$pendingBookings = db()->fetch("SELECT COUNT(*) as count FROM bookings WHERE status='pending'")['count'] ?? 0;
$pendingChanges = db()->fetch("SELECT COUNT(*) as count FROM pending_changes WHERE status='pending'")['count'] ?? 0;
$newContacts = db()->fetch("SELECT COUNT(*) as count FROM contact_submissions WHERE status='new'")['count'] ?? 0;
$activeDisputes = db()->fetch("SELECT COUNT(*) as count FROM disputes WHERE status IN ('open', 'investigating')")['count'] ?? 0;
?>
<div class="sidebar">
    <ul>
        <li><a href="/admin/dashboard" class="<?= $_SERVER['REQUEST_URI'] === '/admin/dashboard' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="/admin/analytics"><i class="fas fa-chart-line"></i> Analytics</a></li>
        <li>
            <a href="/admin/users"><i class="fas fa-users"></i> All Users
                <?php if ($pendingUsers > 0): ?>
                    <span class="notification-badge"><?= $pendingUsers ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="/admin/vehicles"><i class="fas fa-car"></i> All Vehicles
                <?php if ($pendingVehicles > 0): ?>
                    <span class="notification-badge"><?= $pendingVehicles ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="/admin/bookings"><i class="fas fa-calendar-check"></i> All Bookings
                <?php if ($pendingBookings > 0): ?>
                    <span class="notification-badge"><?= $pendingBookings ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li><a href="/admin/payments"><i class="fas fa-credit-card"></i> Payments</a></li>
        <li><a href="/admin/payouts"><i class="fas fa-money-bill-wave"></i> Payouts</a></li>
        <li>
            <a href="/admin/disputes"><i class="fas fa-exclamation-triangle"></i> Disputes
                <?php if ($activeDisputes > 0): ?>
                    <span class="notification-badge"><?= $activeDisputes ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="/admin/pending-changes"><i class="fas fa-clock"></i> Pending Changes
                <?php if ($pendingChanges > 0): ?>
                    <span class="notification-badge"><?= $pendingChanges ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="/admin/contact-submissions"><i class="fas fa-envelope"></i> Contact Submissions
                <?php if ($newContacts > 0): ?>
                    <span class="notification-badge"><?= $newContacts ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li><a href="/admin/security"><i class="fas fa-shield-alt"></i> Security</a></li>
        <li><a href="/admin/audit-logs"><i class="fas fa-file-alt"></i> Audit Logs</a></li>

        <!-- System Settings Section -->
        <li style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--medium-gray);">
            <a href="#" onclick="toggleSettingsMenu(event)" style="font-weight: 600;">
                <i class="fas fa-cog"></i> System Settings
                <i class="fas fa-chevron-down" id="settings-chevron" style="float: right; font-size: 0.8rem;"></i>
            </a>
        </li>
        <ul id="settings-submenu" style="display: none; padding-left: 1.5rem; margin: 0;">
            <li><a href="/admin/settings"><i class="fas fa-sliders-h"></i> General Settings</a></li>
            <li><a href="/admin/settings/payment"><i class="fas fa-credit-card"></i> Payment Settings (Stripe)</a></li>
            <li><a href="/admin/settings/email"><i class="fas fa-envelope-open-text"></i> Email Configuration</a></li>
            <li><a href="/admin/settings/commission"><i class="fas fa-percentage"></i> Commission Rates</a></li>
            <li><a href="/admin/settings/booking"><i class="fas fa-calendar-alt"></i> Booking Settings</a></li>
            <li><a href="/admin/settings/notifications"><i class="fas fa-bell"></i> Notification Settings</a></li>
            <li><a href="/admin/settings/system"><i class="fas fa-server"></i> System Configuration</a></li>
        </ul>

<script>
function toggleSettingsMenu(e) {
    e.preventDefault();
    const submenu = document.getElementById('settings-submenu');
    const chevron = document.getElementById('settings-chevron');

    if (submenu.style.display === 'none') {
        submenu.style.display = 'block';
        chevron.className = 'fas fa-chevron-up';
        localStorage.setItem('settingsMenuOpen', 'true');
    } else {
        submenu.style.display = 'none';
        chevron.className = 'fas fa-chevron-down';
        localStorage.setItem('settingsMenuOpen', 'false');
    }
}

// Restore settings menu state on page load
document.addEventListener('DOMContentLoaded', function() {
    const isOpen = localStorage.getItem('settingsMenuOpen') === 'true';
    const currentPath = window.location.pathname;

    // Auto-expand if on a settings page
    if (currentPath.startsWith('/admin/settings')) {
        document.getElementById('settings-submenu').style.display = 'block';
        document.getElementById('settings-chevron').className = 'fas fa-chevron-up';
    } else if (isOpen) {
        document.getElementById('settings-submenu').style.display = 'block';
        document.getElementById('settings-chevron').className = 'fas fa-chevron-up';
    }
});
</script>
    </ul>
</div>

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
        <li><a href="/admin/settings"><i class="fas fa-cog"></i> Settings</a></li>
    </ul>
</div>

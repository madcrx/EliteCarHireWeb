<?php ob_start(); ?>
<div class="sidebar-layout">
    <div class="sidebar">
        <ul>
            <li><a href="/owner/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="/owner/listings"><i class="fas fa-car"></i> My Listings</a></li>
            <li><a href="/owner/bookings"><i class="fas fa-calendar"></i> Bookings</a></li>
            <li><a href="/owner/calendar"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
            <li><a href="/owner/analytics"><i class="fas fa-chart-line"></i> Analytics</a></li>
            <li><a href="/owner/payouts"><i class="fas fa-money-bill"></i> Payouts</a></li>
            <li><a href="/owner/reviews"><i class="fas fa-star"></i> Reviews</a></li>
            <li><a href="/owner/messages"><i class="fas fa-envelope"></i> Messages</a></li>
            <li><a href="/owner/pending-changes"><i class="fas fa-clock"></i> Pending Changes</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h1><i class="fas fa-bell"></i> Notifications</h1>

        <?php if (empty($allNotifications)): ?>
            <div class="card" style="text-align: center; padding: 3rem;">
                <i class="fas fa-bell-slash" style="font-size: 4rem; color: var(--medium-gray); margin-bottom: 1rem;"></i>
                <h3 style="color: var(--dark-gray);">No Notifications</h3>
                <p style="color: var(--medium-gray);">You don't have any notifications yet. Check back later!</p>
                <a href="/owner/dashboard" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        <?php else: ?>
            <div class="card">
                <p style="color: var(--medium-gray); margin-bottom: 1.5rem;">
                    Showing <?= count($allNotifications) ?> notification<?= count($allNotifications) !== 1 ? 's' : '' ?>
                </p>

                <div class="notifications-list">
                    <?php foreach ($allNotifications as $notification): ?>
                        <div class="notification-item <?= $notification['is_read'] ? 'read' : 'unread' ?>" style="padding: 1rem; margin-bottom: 0.75rem; background: <?= $notification['is_read'] ? '#f8f9fa' : '#e3f2fd' ?>; border-radius: var(--border-radius); border-left: 4px solid <?= $notification['is_read'] ? 'var(--medium-gray)' : '#2196f3' ?>;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <div style="flex: 1;">
                                    <strong style="color: <?= $notification['is_read'] ? 'var(--dark-gray)' : '#1976d2' ?>; display: block; margin-bottom: 0.25rem;">
                                        <?php if (!$notification['is_read']): ?>
                                            <span style="display: inline-block; width: 8px; height: 8px; background: #2196f3; border-radius: 50%; margin-right: 0.5rem;"></span>
                                        <?php endif; ?>
                                        <?= e($notification['title']) ?>
                                    </strong>
                                    <span style="font-size: 0.85rem; color: var(--medium-gray);">
                                        <?php
                                            if (function_exists('timeAgo')) {
                                                echo timeAgo($notification['created_at']);
                                            } else {
                                                echo date('M j, Y g:i A', strtotime($notification['created_at']));
                                            }
                                        ?>
                                    </span>
                                </div>
                                <?php if (!empty($notification['type'])): ?>
                                    <span class="badge" style="background: var(--medium-gray); color: white; padding: 0.25rem 0.5rem; border-radius: var(--border-radius); font-size: 0.75rem;">
                                        <?= e(ucfirst($notification['type'])) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <p style="margin: 0.5rem 0; color: var(--dark-gray); line-height: 1.5;">
                                <?= e($notification['message']) ?>
                            </p>

                            <?php if (!empty($notification['link'])): ?>
                                <a href="<?= e($notification['link']) ?>" class="btn btn-primary btn-sm" style="margin-top: 0.5rem; display: inline-block;">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 2rem; text-align: center;">
                    <a href="/owner/dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.notifications-list .notification-item {
    transition: all 0.2s ease;
}

.notifications-list .notification-item:hover {
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.notifications-list .notification-item.unread {
    font-weight: 500;
}

.badge {
    display: inline-block;
    text-align: center;
    white-space: nowrap;
}
</style>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

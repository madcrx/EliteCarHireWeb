<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1><i class="fas fa-bell"></i> All Notifications</h1>
        <p style="color: var(--dark-gray); margin-bottom: 2rem;">
            View all your notifications and updates.
        </p>

        <?php if (empty($notifications)): ?>
            <div class="card" style="text-align: center; background: var(--light-gray);">
                <i class="fas fa-bell-slash" style="font-size: 3rem; color: var(--medium-gray); margin-bottom: 1rem;"></i>
                <h3>No Notifications</h3>
                <p>You don't have any notifications at the moment.</p>
            </div>
        <?php else: ?>
            <div class="card">
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($notifications as $notification): ?>
                        <div style="padding: 1rem; border-left: 4px solid <?= $notification['is_read'] ? 'var(--medium-gray)' : 'var(--primary-gold)' ?>; background: <?= $notification['is_read'] ? 'white' : 'var(--light-gray)' ?>; border-radius: var(--border-radius);">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <h4 style="margin: 0; color: var(--text-color);"><?= e($notification['title']) ?></h4>
                                <small style="color: var(--dark-gray); white-space: nowrap; margin-left: 1rem;">
                                    <?= timeAgo($notification['created_at']) ?>
                                </small>
                            </div>
                            <p style="margin: 0; color: var(--dark-gray);"><?= e($notification['message']) ?></p>
                            <?php if (!$notification['is_read']): ?>
                                <form method="POST" action="/owner/notifications/<?= $notification['id'] ?>/mark-read" style="margin-top: 0.5rem;">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <button type="submit" class="btn btn-sm" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">
                                        Mark as Read
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

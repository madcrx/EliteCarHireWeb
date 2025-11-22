<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1><i class="fas fa-envelope"></i> Messages</h1>
        <p style="color: var(--dark-gray); margin-bottom: 2rem;">
            View and manage messages from customers and administrators.
        </p>

        <?php if (empty($messages)): ?>
            <div class="card" style="text-align: center; background: var(--light-gray);">
                <i class="fas fa-inbox" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 1rem;"></i>
                <h3>No Messages</h3>
                <p>You don't have any messages at the moment.</p>
                <a href="/owner/dashboard" class="btn btn-primary" style="margin-top: 1rem;">Return to Dashboard</a>
            </div>
        <?php else: ?>
            <div class="card">
                <h2><i class="fas fa-list"></i> Your Messages</h2>
                <p style="margin-bottom: 1.5rem; color: var(--dark-gray);">
                    All messages from customers, administrators, and system notifications.
                </p>

                <div style="display: grid; gap: 1.5rem;">
                    <?php foreach ($messages as $message): ?>
                        <div style="border: 1px solid var(--medium-gray); border-radius: var(--border-radius); padding: 1.5rem; <?= empty($message['read_at']) ? 'background: #fffbf0; border-left: 4px solid var(--primary-gold);' : '' ?>">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 style="margin: 0 0 0.5rem 0;">
                                        <?= e($message['subject'] ?? 'No Subject') ?>
                                    </h3>
                                    <p style="color: var(--dark-gray); margin: 0;">
                                        <i class="fas fa-user"></i>
                                        From: <strong><?= e($message['first_name'] . ' ' . $message['last_name']) ?></strong>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <?php if (empty($message['read_at'])): ?>
                                        <span class="badge badge-warning">Unread</span>
                                    <?php endif; ?>
                                    <div style="color: var(--dark-gray); font-size: 0.9rem; margin-top: 0.5rem;">
                                        <i class="fas fa-clock"></i>
                                        <?= date('M d, Y g:i A', strtotime($message['created_at'])) ?>
                                    </div>
                                </div>
                            </div>

                            <div style="padding: 1rem; background: white; border-radius: var(--border-radius); margin-top: 1rem;">
                                <p style="margin: 0; line-height: 1.6; white-space: pre-wrap;">
                                    <?= nl2br(e($message['message'])) ?>
                                </p>
                            </div>

                            <?php if (!empty($message['booking_reference'])): ?>
                                <div style="margin-top: 1rem; padding: 0.75rem; background: var(--light-gray); border-radius: var(--border-radius);">
                                    <small style="color: var(--dark-gray);">
                                        <i class="fas fa-link"></i>
                                        Related to Booking: <strong><?= e($message['booking_reference']) ?></strong>
                                    </small>
                                </div>
                            <?php endif; ?>

                            <div style="margin-top: 1rem; display: flex; gap: 1rem;">
                                <button class="btn btn-primary" style="padding: 0.5rem 1rem;" onclick="alert('Reply functionality coming soon')">
                                    <i class="fas fa-reply"></i> Reply
                                </button>
                                <?php if (empty($message['read_at'])): ?>
                                    <button class="btn" style="padding: 0.5rem 1rem; background: var(--success); color: white;" onclick="alert('Mark as read functionality coming soon')">
                                        <i class="fas fa-check"></i> Mark as Read
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card" style="background: var(--light-gray);">
                <h3><i class="fas fa-info-circle"></i> Message Information</h3>
                <ul>
                    <li><strong>Unread Messages:</strong> Highlighted with gold border for easy identification</li>
                    <li><strong>Booking Related:</strong> Messages linked to bookings show the booking reference</li>
                    <li><strong>Response Time:</strong> We recommend responding to customer inquiries within 24 hours</li>
                    <li><strong>Professional Communication:</strong> Maintain professional and courteous communication</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

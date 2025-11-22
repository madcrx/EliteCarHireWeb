<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1>Contact Submissions</h1>

        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="margin-bottom: 0;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-gray);">Filter by Status:</label>
                <a href="/admin/contact-submissions?status=all" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                <a href="/admin/contact-submissions?status=new" class="btn <?= $status === 'new' ? 'btn-primary' : 'btn-secondary' ?>">New</a>
                <a href="/admin/contact-submissions?status=read" class="btn <?= $status === 'read' ? 'btn-primary' : 'btn-secondary' ?>">Read</a>
                <a href="/admin/contact-submissions?status=responded" class="btn <?= $status === 'responded' ? 'btn-primary' : 'btn-secondary' ?>">Responded</a>
                <a href="/admin/contact-submissions?status=archived" class="btn <?= $status === 'archived' ? 'btn-primary' : 'btn-secondary' ?>">Archived</a>
            </div>
        </div>

        <?php if (empty($submissions)): ?>
            <div class="card">
                <p style="text-align: center; color: var(--dark-gray); padding: 2rem;">
                    <i class="fas fa-inbox" style="font-size: 3rem; color: var(--primary-gold); display: block; margin-bottom: 1rem;"></i>
                    No contact form submissions found.
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($submissions as $submission): ?>
                <div class="card" style="<?= $submission['status'] === 'new' ? 'border-left: 4px solid var(--primary-gold);' : '' ?>">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div style="flex: 1;">
                            <h3 style="margin-bottom: 0.5rem;">
                                <i class="fas fa-envelope"></i> <?= e($submission['subject'] ?? 'No Subject') ?>
                            </h3>
                            <p style="margin-bottom: 0.25rem;"><strong>From:</strong> <?= e($submission['name']) ?>
                                <a href="mailto:<?= e($submission['email']) ?>" style="color: var(--primary-gold);"><?= e($submission['email']) ?></a>
                            </p>
                            <?php if (!empty($submission['phone'])): ?>
                                <p style="margin-bottom: 0.25rem;"><strong>Phone:</strong> <a href="tel:<?= e($submission['phone']) ?>" style="color: var(--primary-gold);"><?= e($submission['phone']) ?></a></p>
                            <?php endif; ?>
                            <p style="margin-bottom: 0;"><strong>Date:</strong> <?= date('M d, Y H:i', strtotime($submission['created_at'])) ?></p>
                        </div>
                        <div style="text-align: right;">
                            <?php
                            $statusBadge = [
                                'new' => 'info',
                                'read' => 'warning',
                                'responded' => 'success',
                                'archived' => 'secondary'
                            ];
                            $badgeClass = $statusBadge[$submission['status']] ?? 'info';
                            ?>
                            <span class="badge badge-<?= $badgeClass ?>"><?= ucfirst($submission['status']) ?></span>
                        </div>
                    </div>

                    <div style="margin: 1rem 0; padding: 1rem; background: var(--light-gray); border-radius: var(--border-radius); border-left: 3px solid var(--medium-gray);">
                        <p style="margin: 0; white-space: pre-wrap;"><?= e($submission['message']) ?></p>
                    </div>

                    <?php if (!empty($submission['response_text'])): ?>
                        <div style="margin: 1rem 0; padding: 1rem; background: #e8f5e9; border-radius: var(--border-radius); border-left: 3px solid #4caf50;">
                            <p style="margin-bottom: 0.5rem;"><strong><i class="fas fa-reply"></i> Admin Reply:</strong></p>
                            <p style="margin: 0; white-space: pre-wrap;"><?= e($submission['response_text']) ?></p>
                            <?php if (!empty($submission['responded_at'])): ?>
                                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--dark-gray);">Replied: <?= date('M d, Y H:i', strtotime($submission['responded_at'])) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; gap: 1rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--medium-gray); flex-wrap: wrap;">
                        <!-- Reply Button -->
                        <button onclick="toggleReply(<?= $submission['id'] ?>)" class="btn btn-primary">
                            <i class="fas fa-reply"></i> Reply
                        </button>

                        <!-- Change Status Form -->
                        <form method="POST" action="/admin/contact-submissions/<?= $submission['id'] ?>/update-status" style="display: inline-block;">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <select name="status" onchange="this.form.submit()" class="btn" style="padding: 8px 15px; cursor: pointer;">
                                <option value="">Change Status...</option>
                                <option value="new" <?= $submission['status'] === 'new' ? 'selected' : '' ?>>New</option>
                                <option value="read" <?= $submission['status'] === 'read' ? 'selected' : '' ?>>Read</option>
                                <option value="responded" <?= $submission['status'] === 'responded' ? 'selected' : '' ?>>Responded</option>
                                <option value="archived" <?= $submission['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </form>

                        <!-- Delete Button -->
                        <form method="POST" action="/admin/contact-submissions/<?= $submission['id'] ?>/delete" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this contact submission? This action cannot be undone.');">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <button type="submit" class="btn" style="background: var(--danger); color: white;">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>

                    <!-- Reply Form (Hidden by default) -->
                    <div id="reply-form-<?= $submission['id'] ?>" style="display: none; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--medium-gray);">
                        <form method="POST" action="/admin/contact-submissions/<?= $submission['id'] ?>/reply">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                            <div class="form-group">
                                <label for="reply-<?= $submission['id'] ?>">Your Reply:</label>
                                <textarea name="reply" id="reply-<?= $submission['id'] ?>" rows="5" required placeholder="Type your reply here..."><?= !empty($submission['response_text']) ? e($submission['response_text']) : '' ?></textarea>
                                <small style="color: var(--dark-gray);">This will be sent to <?= e($submission['email']) ?></small>
                            </div>

                            <div style="display: flex; gap: 1rem;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send Reply
                                </button>
                                <button type="button" onclick="toggleReply(<?= $submission['id'] ?>)" class="btn" style="background: var(--medium-gray);">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleReply(id) {
    const replyForm = document.getElementById('reply-form-' + id);
    if (replyForm.style.display === 'none') {
        replyForm.style.display = 'block';
    } else {
        replyForm.style.display = 'none';
    }
}
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

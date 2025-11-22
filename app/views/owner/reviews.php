<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1><i class="fas fa-star"></i> Reviews</h1>
        <p style="color: var(--dark-gray); margin-bottom: 2rem;">
            Customer reviews and ratings for your vehicles.
        </p>

        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="margin-bottom: 0;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-gray);">Filter by Rating:</label>
                <a href="/owner/reviews?rating=all" class="btn <?= $rating === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                <a href="/owner/reviews?rating=5" class="btn <?= $rating === '5' ? 'btn-primary' : 'btn-secondary' ?>">⭐ 5 Stars</a>
                <a href="/owner/reviews?rating=4" class="btn <?= $rating === '4' ? 'btn-primary' : 'btn-secondary' ?>">⭐ 4 Stars</a>
                <a href="/owner/reviews?rating=3" class="btn <?= $rating === '3' ? 'btn-primary' : 'btn-secondary' ?>">⭐ 3 Stars</a>
                <a href="/owner/reviews?rating=2" class="btn <?= $rating === '2' ? 'btn-primary' : 'btn-secondary' ?>">⭐ 2 Stars</a>
                <a href="/owner/reviews?rating=1" class="btn <?= $rating === '1' ? 'btn-primary' : 'btn-secondary' ?>">⭐ 1 Star</a>
            </div>
        </div>

        <?php if (empty($reviews)): ?>
            <div class="card" style="text-align: center; background: var(--light-gray);">
                <i class="fas fa-comments" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 1rem;"></i>
                <h3>No Reviews Yet</h3>
                <p>Complete bookings to start receiving customer reviews.</p>
                <a href="/owner/dashboard" class="btn btn-primary" style="margin-top: 1rem;">Return to Dashboard</a>
            </div>
        <?php else: ?>
            <div class="card">
                <h2><i class="fas fa-list"></i> Customer Reviews</h2>
                <p style="margin-bottom: 1.5rem; color: var(--dark-gray);">
                    See what customers are saying about your vehicles and service.
                </p>

                <div style="display: grid; gap: 1.5rem;">
                    <?php foreach ($reviews as $review): ?>
                        <div style="border: 1px solid var(--medium-gray); border-radius: var(--border-radius); padding: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 style="margin: 0 0 0.5rem 0;">
                                        <?= e($review['make'] . ' ' . $review['model']) ?>
                                    </h3>
                                    <p style="color: var(--dark-gray); margin: 0;">
                                        by <?= e($review['first_name'] . ' ' . $review['last_name']) ?>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="color: var(--primary-gold); font-size: 1.2rem; margin-bottom: 0.25rem;">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <?php if ($i < $review['rating']): ?>
                                                <i class="fas fa-star"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <small style="color: var(--dark-gray);">
                                        <?= date('M d, Y', strtotime($review['created_at'])) ?>
                                    </small>
                                </div>
                            </div>

                            <p style="margin: 0; line-height: 1.6;">
                                <?= nl2br(e($review['comment'])) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card" style="background: var(--light-gray);">
                <h3><i class="fas fa-info-circle"></i> Review Tips</h3>
                <ul>
                    <li><strong>Respond Promptly:</strong> Engage with reviewers to show you value their feedback</li>
                    <li><strong>Learn from Feedback:</strong> Use reviews to improve your service quality</li>
                    <li><strong>Highlight Positives:</strong> Great reviews help attract more bookings</li>
                    <li><strong>Address Concerns:</strong> Resolve issues mentioned in negative reviews professionally</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

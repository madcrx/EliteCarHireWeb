<?php ob_start(); ?>
<div class="container" style="padding: 4rem 0;">
    <h1 style="text-align: center; color: var(--primary-gold); margin-bottom: 3rem;">Our Premium Fleet</h1>
    
    <div class="vehicle-grid">
        <?php foreach ($vehicles as $vehicle): ?>
            <div class="vehicle-card">
                <?php if ($vehicle['primary_image']): ?>
                    <img src="/<?= e($vehicle['primary_image']) ?>" alt="<?= e($vehicle['make'] . ' ' . $vehicle['model']) ?>">
                <?php else: ?>
                    <div style="height: 200px; background: var(--light-gray); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-car" style="font-size: 4rem; color: var(--medium-gray);"></i>
                    </div>
                <?php endif; ?>
                <div class="vehicle-card-body">
                    <h3><?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?></h3>
                    <p><?= e($vehicle['year']) ?> • <?= ucwords(str_replace('_', ' ', $vehicle['category'])) ?></p>
                    <p style="color: var(--primary-gold); font-size: 1.3rem; font-weight: bold; margin: 1rem 0;">
                        <?= formatMoney($vehicle['hourly_rate']) ?>/hour
                    </p>
                    <p style="color: var(--dark-gray); font-size: 0.9rem;">
                        <i class="fas fa-users"></i> Up to <?= $vehicle['max_passengers'] ?> passengers<br>
                        <i class="fas fa-clock"></i> <?= $vehicle['minimum_hours'] ?> hour minimum
                    </p>
                    <a href="/vehicles/<?= $vehicle['id'] ?>" class="btn btn-primary" style="width: 100%; text-align: center; margin-top: 1rem;">
                        View Details & Book
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

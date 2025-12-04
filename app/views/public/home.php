<?php ob_start(); ?>
<div class="hero">
    <div class="container">
        <h1>Luxury Vehicle Hire in Melbourne</h1>
        <p>Experience elegance and sophistication with our premium fleet</p>
        <a href="/vehicles" class="btn btn-primary">View Our Fleet</a>
    </div>
</div>

<div class="container" style="padding: 4rem 0;">
    <h2 style="text-align: center; color: var(--primary-gold); margin-bottom: 3rem;">Featured Vehicles</h2>
    
    <div class="vehicle-grid">
        <?php foreach ($featuredVehicles as $vehicle): ?>
            <div class="vehicle-card">
                <?php if ($vehicle['primary_image']): ?>
                    <img src="/<?= e($vehicle['primary_image']) ?>" alt="<?= e($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'] . ' - Luxury chauffeur service Melbourne - Premium vehicle hire') ?>">
                <?php else: ?>
                    <img src="/assets/images/placeholder.jpg" alt="Luxury vehicle for hire - Elite Car Hire Melbourne chauffeur service">
                <?php endif; ?>
                <div class="vehicle-card-body">
                    <h3><?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?></h3>
                    <p><?= e($vehicle['year']) ?> • <?= ucwords(str_replace('_', ' ', $vehicle['category'])) ?></p>
                    <p style="color: var(--primary-gold); font-size: 1.2rem; font-weight: bold;">
                        <?= formatMoney($vehicle['hourly_rate']) ?>/hour
                    </p>
                    <a href="/vehicles/<?= $vehicle['id'] ?>" class="btn btn-primary" style="width: 100%; text-align: center;">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div style="background: var(--light-gray); padding: 4rem 0;">
    <div class="container">
        <h2 style="text-align: center; color: var(--primary-gold); margin-bottom: 3rem;">Why Choose Elite Car Hire?</h2>
        <div class="stats-grid">
            <div class="card" style="text-align: center;">
                <i class="fas fa-car" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 1rem;"></i>
                <h3>Premium Fleet</h3>
                <p>Luxury and classic muscle cars for every occasion</p>
            </div>
            <div class="card" style="text-align: center;">
                <i class="fas fa-user-tie" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 1rem;"></i>
                <h3>Professional Chauffeurs</h3>
                <p>Experienced, licensed, and fully insured drivers</p>
            </div>
            <div class="card" style="text-align: center;">
                <i class="fas fa-shield-alt" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 1rem;"></i>
                <h3>Fully Insured</h3>
                <p>Comprehensive coverage for your peace of mind</p>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

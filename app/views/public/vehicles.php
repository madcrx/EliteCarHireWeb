<?php ob_start(); ?>
<div class="container" style="padding: 4rem 0;">
    <h1 style="text-align: center; color: var(--primary-gold); margin-bottom: 2rem;">Our Premium Fleet</h1>

    <!-- Search & Filter Form -->
    <div class="card" style="margin-bottom: 3rem; background: linear-gradient(135deg, var(--light-gray) 0%, #fff 100%);">
        <h3 style="color: var(--primary-gold); margin-bottom: 1.5rem;">
            <i class="fas fa-search"></i> Find Your Perfect Vehicle
        </h3>

        <form method="GET" action="/vehicles" id="vehicleFilterForm">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1rem;">
                <!-- State Filter -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="state" style="font-weight: bold; margin-bottom: 0.5rem; display: block;">
                        <i class="fas fa-map-marker-alt"></i> Location
                    </label>
                    <select name="state" id="state" style="width: 100%;">
                        <option value="">All Locations</option>
                        <?php foreach ($states as $stateOption): ?>
                            <?php if (!empty($stateOption['state'])): ?>
                                <option value="<?= e($stateOption['state']) ?>" <?= $filters['state'] === $stateOption['state'] ? 'selected' : '' ?>>
                                    <?= e($stateOption['state']) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="category" style="font-weight: bold; margin-bottom: 0.5rem; display: block;">
                        <i class="fas fa-car"></i> Vehicle Type
                    </label>
                    <select name="category" id="category" style="width: 100%;">
                        <option value="">All Types</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat['category']) ?>" <?= $filters['category'] === $cat['category'] ? 'selected' : '' ?>>
                                <?= ucwords(str_replace('_', ' ', e($cat['category']))) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Start Date -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="start_date" style="font-weight: bold; margin-bottom: 0.5rem; display: block;">
                        <i class="fas fa-calendar-alt"></i> Start Date
                    </label>
                    <input type="date" name="start_date" id="start_date" value="<?= e($filters['start_date']) ?>" min="<?= date('Y-m-d') ?>" style="width: 100%;">
                </div>

                <!-- End Date -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="end_date" style="font-weight: bold; margin-bottom: 0.5rem; display: block;">
                        <i class="fas fa-calendar-check"></i> End Date
                    </label>
                    <input type="date" name="end_date" id="end_date" value="<?= e($filters['end_date']) ?>" min="<?= date('Y-m-d') ?>" style="width: 100%;">
                </div>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: center;">
                <button type="submit" class="btn btn-primary" style="min-width: 150px;">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="/vehicles" class="btn" style="min-width: 150px; background: var(--medium-gray); color: white; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="fas fa-redo"></i> Clear Filters
                </a>
            </div>
        </form>

        <?php
        // Show active filters
        $activeFilters = [];
        if (!empty($filters['state'])) $activeFilters[] = 'Location: ' . $filters['state'];
        if (!empty($filters['category'])) $activeFilters[] = 'Type: ' . ucwords(str_replace('_', ' ', $filters['category']));
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $activeFilters[] = 'Dates: ' . date('M d, Y', strtotime($filters['start_date'])) . ' - ' . date('M d, Y', strtotime($filters['end_date']));
        }
        ?>
        <?php if (!empty($activeFilters)): ?>
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--medium-gray);">
                <strong style="color: var(--primary-gold);">Active Filters:</strong>
                <?php foreach ($activeFilters as $filter): ?>
                    <span class="badge badge-info" style="margin-left: 0.5rem;"><?= e($filter) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Results Count -->
    <div style="margin-bottom: 1.5rem; text-align: center; color: var(--dark-gray);">
        <strong><?= count($vehicles) ?></strong> vehicle<?= count($vehicles) !== 1 ? 's' : '' ?> found
    </div>

    <?php if (empty($vehicles)): ?>
        <div class="card" style="text-align: center; padding: 3rem;">
            <i class="fas fa-car" style="font-size: 4rem; color: var(--medium-gray); margin-bottom: 1rem;"></i>
            <h3>No Vehicles Found</h3>
            <p style="color: var(--dark-gray);">Try adjusting your search criteria or <a href="/vehicles">clear all filters</a> to see all available vehicles.</p>
        </div>
    <?php else: ?>
        <div class="vehicle-grid">
        <?php foreach ($vehicles as $vehicle): ?>
            <div class="vehicle-card">
                <?php if ($vehicle['primary_image']): ?>
                    <img src="/<?= e($vehicle['primary_image']) ?>" alt="<?= e($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'] . ' - Luxury chauffeur hire Melbourne - ' . ucwords(str_replace('_', ' ', $vehicle['category'])) . ' rental') ?>">
                <?php else: ?>
                    <div style="height: 200px; background: var(--light-gray); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-car" style="font-size: 4rem; color: var(--medium-gray);"></i>
                    </div>
                <?php endif; ?>
                <div class="vehicle-card-body">
                    <h3><?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?></h3>
                    <p><?= e($vehicle['year']) ?> • <?= ucwords(str_replace('_', ' ', $vehicle['category'])) ?></p>
                    <p style="color: var(--dark-gray); font-size: 0.9rem; margin: 0.5rem 0;">
                        <i class="fas fa-map-marker-alt"></i> <?= e($vehicle['state'] ?? 'VIC') ?>
                    </p>
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
    <?php endif; ?>
</div>

<script>
// Auto-update end date minimum when start date changes
document.getElementById('start_date')?.addEventListener('change', function() {
    const endDateInput = document.getElementById('end_date');
    if (endDateInput) {
        endDateInput.min = this.value;
        // If end date is before start date, update it
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
    }
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

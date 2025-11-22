<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1><i class="fas fa-edit"></i> Edit Listing</h1>
        <p style="color: var(--dark-gray); margin-bottom: 2rem;">
            Update your vehicle information. Changes may require admin approval.
        </p>

        <div class="card">
            <form method="POST" action="/owner/listings/<?= $vehicle['id'] ?>/edit">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-car"></i> Vehicle Details</h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="make">Make *</label>
                        <input type="text" name="make" id="make" value="<?= e($vehicle['make']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="model">Model *</label>
                        <input type="text" name="model" id="model" value="<?= e($vehicle['model']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="year">Year *</label>
                        <input type="number" name="year" id="year" value="<?= e($vehicle['year']) ?>" required min="1990" max="<?= date('Y') + 1 ?>">
                    </div>

                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" name="color" id="color" value="<?= e($vehicle['color'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select name="category" id="category" required>
                            <option value="">-- Select Category --</option>
                            <option value="classic_muscle" <?= $vehicle['category'] === 'classic_muscle' ? 'selected' : '' ?>>Classic/Muscle Car</option>
                            <option value="luxury_exotic" <?= $vehicle['category'] === 'luxury_exotic' ? 'selected' : '' ?>>Luxury/Exotic</option>
                            <option value="premium" <?= $vehicle['category'] === 'premium' ? 'selected' : '' ?>>Premium</option>
                            <option value="other" <?= $vehicle['category'] === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="max_passengers">Max Passengers</label>
                        <input type="number" name="max_passengers" id="max_passengers" value="<?= e($vehicle['max_passengers'] ?? 4) ?>" min="1" max="12">
                    </div>

                    <div class="form-group">
                        <label for="hourly_rate">Hourly Rate ($) *</label>
                        <input type="number" name="hourly_rate" id="hourly_rate" value="<?= e($vehicle['hourly_rate']) ?>" required min="0" step="0.01">
                    </div>

                    <div class="form-group">
                        <label for="registration_number">Registration Number</label>
                        <input type="text" name="registration_number" id="registration_number" value="<?= e($vehicle['registration_number'] ?? '') ?>" placeholder="e.g., ABC123-VIC">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="4"><?= e($vehicle['description'] ?? '') ?></textarea>
                </div>

                <div style="background: var(--light-gray); padding: 1rem; border-radius: var(--border-radius); margin: 1.5rem 0;">
                    <p style="margin: 0;"><strong>Current Status:</strong>
                        <?php
                            $statusClass = match($vehicle['status']) {
                                'approved' => 'success',
                                'active' => 'success',
                                'pending' => 'warning',
                                'rejected' => 'danger',
                                default => 'secondary'
                            };
                        ?>
                        <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($vehicle['status']) ?></span>
                    </p>
                </div>

                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--medium-gray); display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="/owner/listings" class="btn" style="background: var(--medium-gray);">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>

        <div class="card" style="background: var(--light-gray);">
            <h3><i class="fas fa-info-circle"></i> Update Guidelines</h3>
            <ul>
                <li><strong>Review Process:</strong> Significant changes may require admin re-approval</li>
                <li><strong>Active Bookings:</strong> Cannot modify vehicles with active bookings</li>
                <li><strong>Pricing Updates:</strong> New rates only apply to future bookings</li>
                <li><strong>Status:</strong> Only approved vehicles are visible to customers</li>
            </ul>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

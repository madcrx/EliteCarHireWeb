<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1><i class="fas fa-plus-circle"></i> Add New Listing</h1>
        <p style="color: var(--dark-gray); margin-bottom: 2rem;">
            List your vehicle for hire. All listings require admin approval before going live.
        </p>

        <div class="card">
            <form method="POST" action="/owner/listings/add" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-car"></i> Vehicle Details</h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="make">Make *</label>
                        <input type="text" name="make" id="make" required placeholder="e.g., Mercedes-Benz">
                    </div>

                    <div class="form-group">
                        <label for="model">Model *</label>
                        <input type="text" name="model" id="model" required placeholder="e.g., S-Class">
                    </div>

                    <div class="form-group">
                        <label for="year">Year *</label>
                        <input type="number" name="year" id="year" required min="1990" max="<?= date('Y') + 1 ?>" placeholder="<?= date('Y') ?>">
                    </div>

                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" name="color" id="color" placeholder="e.g., Black">
                    </div>

                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select name="category" id="category" required>
                            <option value="">-- Select Category --</option>
                            <option value="classic_muscle">Classic/Muscle Car</option>
                            <option value="luxury_exotic">Luxury/Exotic</option>
                            <option value="premium">Premium</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="state">State/Territory *</label>
                        <select name="state" id="state" required>
                            <option value="">-- Select State --</option>
                            <option value="NSW">New South Wales (NSW)</option>
                            <option value="VIC">Victoria (VIC)</option>
                            <option value="QLD">Queensland (QLD)</option>
                            <option value="SA">South Australia (SA)</option>
                            <option value="WA">Western Australia (WA)</option>
                            <option value="TAS">Tasmania (TAS)</option>
                            <option value="NT">Northern Territory (NT)</option>
                            <option value="ACT">Australian Capital Territory (ACT)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="max_passengers">Max Passengers</label>
                        <input type="number" name="max_passengers" id="max_passengers" min="1" max="12" value="4">
                    </div>

                    <div class="form-group">
                        <label for="hourly_rate">Hourly Rate ($) *</label>
                        <input type="number" name="hourly_rate" id="hourly_rate" required min="0" step="0.01" placeholder="95.00">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="4" placeholder="Describe your vehicle's features, condition, and any special notes..."></textarea>
                </div>

                <h3 style="margin: 2rem 0 1.5rem 0;"><i class="fas fa-images"></i> Vehicle Images</h3>
                <div class="form-group">
                    <label for="images">Upload Images (Optional)</label>
                    <input type="file" name="images[]" id="images" multiple accept="image/*">
                    <small style="color: var(--dark-gray); display: block; margin-top: 0.5rem;">
                        You can upload multiple images. The first image will be set as the primary image.
                    </small>
                </div>

                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--medium-gray); display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Submit for Approval
                    </button>
                    <a href="/owner/listings" class="btn" style="background: var(--medium-gray);">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>

        <div class="card" style="background: var(--light-gray);">
            <h3><i class="fas fa-info-circle"></i> Listing Guidelines</h3>
            <ul>
                <li><strong>Approval Process:</strong> All listings are reviewed by our admin team before going live</li>
                <li><strong>Accurate Information:</strong> Provide accurate vehicle details to avoid listing rejection</li>
                <li><strong>Quality Images:</strong> High-quality photos help attract more bookings</li>
                <li><strong>Competitive Pricing:</strong> Research similar vehicles to set competitive hourly rates</li>
                <li><strong>Detailed Description:</strong> Include features, condition, and any special requirements</li>
            </ul>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

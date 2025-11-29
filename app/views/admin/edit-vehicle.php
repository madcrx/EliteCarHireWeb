<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1><i class="fas fa-edit"></i> Edit Vehicle Listing</h1>
        <p style="color: var(--dark-gray); margin-bottom: 2rem;">
            Update vehicle information and manage listing status.
        </p>

        <!-- Owner Information -->
        <div class="card" style="background: var(--light-gray); margin-bottom: 1.5rem;">
            <h3><i class="fas fa-user"></i> Owner Information</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                <div>
                    <strong>Owner:</strong> <?= e($vehicle['first_name'] . ' ' . $vehicle['last_name']) ?>
                </div>
                <div>
                    <strong>Email:</strong> <a href="mailto:<?= e($vehicle['email']) ?>" style="color: var(--primary-gold);"><?= e($vehicle['email']) ?></a>
                </div>
                <div>
                    <strong>Owner ID:</strong> #<?= $vehicle['owner_id'] ?>
                </div>
            </div>
        </div>

        <div class="card">
            <form method="POST" action="/admin/vehicles/<?= $vehicle['id'] ?>/update">
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

                <!-- Admin-only Status Control -->
                <div class="form-group" style="background: #fff9e6; padding: 1.5rem; border-radius: var(--border-radius); border: 1px solid var(--primary-gold);">
                    <label for="status"><i class="fas fa-shield-alt"></i> Listing Status (Admin Only)</label>
                    <select name="status" id="status" required>
                        <option value="pending" <?= $vehicle['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $vehicle['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="active" <?= $vehicle['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="rejected" <?= $vehicle['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        <option value="inactive" <?= $vehicle['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <small style="color: var(--dark-gray); display: block; margin-top: 0.5rem;">
                        <i class="fas fa-info-circle"></i> Changing status will notify the vehicle owner
                    </small>
                </div>

                <div style="background: var(--light-gray); padding: 1rem; border-radius: var(--border-radius); margin: 1.5rem 0;">
                    <p style="margin: 0;"><strong>Vehicle ID:</strong> #<?= $vehicle['id'] ?></p>
                    <p style="margin: 0.5rem 0 0 0;"><strong>Created:</strong> <?= date('M d, Y g:i A', strtotime($vehicle['created_at'])) ?></p>
                    <?php if ($vehicle['updated_at']): ?>
                        <p style="margin: 0.5rem 0 0 0;"><strong>Last Updated:</strong> <?= date('M d, Y g:i A', strtotime($vehicle['updated_at'])) ?></p>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--medium-gray); display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="/admin/vehicles" class="btn" style="background: var(--medium-gray);">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Vehicle Images Section -->
        <div class="card">
            <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-images"></i> Vehicle Images</h3>

            <?php
            // Fetch existing images
            $images = db()->fetchAll("SELECT * FROM vehicle_images WHERE vehicle_id = ? ORDER BY is_primary DESC, display_order ASC", [$vehicle['id']]);
            ?>

            <?php if (!empty($images)): ?>
                <div style="margin-bottom: 2rem;">
                    <h4>Current Images</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                        <?php foreach ($images as $image): ?>
                            <div style="position: relative; border: 2px solid <?= $image['is_primary'] ? 'var(--primary-gold)' : 'var(--medium-gray)' ?>; border-radius: var(--border-radius); padding: 0.5rem;">
                                <img src="/<?= e($image['image_path']) ?>" alt="Vehicle Image"
                                     style="width: 100%; height: 150px; object-fit: cover; border-radius: var(--border-radius);">

                                <?php if ($image['is_primary']): ?>
                                    <span class="badge badge-success" style="position: absolute; top: 1rem; right: 1rem;">
                                        <i class="fas fa-star"></i> Primary
                                    </span>
                                <?php endif; ?>

                                <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <?php if (!$image['is_primary']): ?>
                                        <form method="POST" action="/admin/vehicles/<?= $vehicle['id'] ?>/images/<?= $image['id'] ?>/set-primary" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                            <button type="submit" class="btn btn-sm" style="background: var(--primary-gold); color: white; font-size: 0.75rem;">
                                                <i class="fas fa-star"></i> Set Primary
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="POST" action="/admin/vehicles/<?= $vehicle['id'] ?>/images/<?= $image['id'] ?>/delete"
                                          style="display: inline;"
                                          onsubmit="return confirm('Delete this image?');">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" style="font-size: 0.75rem;">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No images uploaded for this vehicle. Upload images below to improve visibility.
                </div>
            <?php endif; ?>

            <!-- Upload New Images -->
            <div style="background: var(--light-gray); padding: 1.5rem; border-radius: var(--border-radius); margin-top: 1.5rem;">
                <h4><i class="fas fa-upload"></i> Upload New Images</h4>
                <form method="POST" action="/admin/vehicles/<?= $vehicle['id'] ?>/images/upload" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                    <div class="form-group">
                        <label for="images">Select Images</label>
                        <input type="file" name="images[]" id="images" multiple accept="image/jpeg,image/jpg,image/png,image/webp" required>
                        <small style="display: block; margin-top: 0.5rem; color: var(--dark-gray);">
                            <i class="fas fa-info-circle"></i>
                            Accepted formats: JPG, PNG, WebP. Max 2MB per image (server limit).
                            <?php if (empty($images)): ?>
                                The first image will be set as the primary image.
                            <?php endif; ?>
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-cloud-upload-alt"></i> Upload Images
                    </button>
                </form>
            </div>
        </div>

        <div class="card" style="background: var(--light-gray);">
            <h3><i class="fas fa-info-circle"></i> Admin Guidelines</h3>
            <ul>
                <li><strong>Status Changes:</strong> Owner will be notified when you change the vehicle status</li>
                <li><strong>Approval:</strong> Set status to "Approved" to make vehicle visible to customers</li>
                <li><strong>Pricing:</strong> Admin can adjust hourly rates if necessary</li>
                <li><strong>Images:</strong> High-quality photos improve booking conversion rates</li>
                <li><strong>Primary Image:</strong> The primary image is displayed on vehicle listings</li>
                <li><strong>Audit Trail:</strong> All changes are logged for security and compliance</li>
                <li><strong>Owner Notification:</strong> Significant changes trigger automatic notifications to the owner</li>
            </ul>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

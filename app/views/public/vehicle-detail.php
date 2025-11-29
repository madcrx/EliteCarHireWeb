<?php ob_start(); ?>
<div class="container" style="padding: 4rem 0;">
    <div class="card">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <div>
                <h1 style="color: var(--primary-gold); margin-bottom: 1rem;">
                    <?= e($vehicle['year']) ?> <?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?>
                </h1>
                
                <?php if (!empty($images)): ?>
                    <div style="margin-bottom: 2rem;">
                        <!-- Main Image Display -->
                        <img id="mainVehicleImage"
                             src="/<?= e($images[0]['image_path']) ?>"
                             alt="<?= e($vehicle['make'] . ' ' . $vehicle['model']) ?>"
                             style="width: 100%; max-height: 500px; object-fit: cover; border-radius: var(--border-radius); cursor: pointer;">

                        <?php if (count($images) > 1): ?>
                            <!-- Thumbnail Gallery -->
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 1rem; margin-top: 1rem;">
                                <?php foreach ($images as $index => $img): ?>
                                    <img src="/<?= e($img['image_path']) ?>"
                                         alt="Image <?= $index + 1 ?>"
                                         class="vehicle-thumbnail <?= $index === 0 ? 'active' : '' ?>"
                                         onclick="changeMainImage('<?= e($img['image_path']) ?>', this)"
                                         style="width: 100%; height: 100px; object-fit: cover; border-radius: var(--border-radius); cursor: pointer; border: 3px solid transparent; transition: all 0.3s;">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <style>
                        .vehicle-thumbnail:hover {
                            border-color: var(--primary-gold) !important;
                            opacity: 0.8;
                        }
                        .vehicle-thumbnail.active {
                            border-color: var(--primary-gold) !important;
                        }
                    </style>

                    <script>
                    function changeMainImage(imagePath, thumbnail) {
                        // Update main image
                        document.getElementById('mainVehicleImage').src = '/' + imagePath;

                        // Update active state on thumbnails
                        document.querySelectorAll('.vehicle-thumbnail').forEach(function(thumb) {
                            thumb.classList.remove('active');
                        });
                        thumbnail.classList.add('active');
                    }
                    </script>
                <?php else: ?>
                    <?php
                    // Assign category-appropriate placeholder
                    $placeholderMap = [
                        'luxury_sedan' => 'luxury-sedan.svg',
                        'sedan' => 'luxury-sedan.svg',
                        'muscle_car' => 'muscle-car.svg',
                        'classic' => 'muscle-car.svg',
                        'suv' => 'luxury-suv.svg',
                        'luxury_suv' => 'luxury-suv.svg',
                        'wedding' => 'wedding-car.svg',
                        'sports' => 'sports-car.svg',
                        'sports_car' => 'sports-car.svg',
                        'supercar' => 'sports-car.svg',
                        'limousine' => 'limousine.svg',
                        'limo' => 'limousine.svg',
                    ];
                    $placeholder = $placeholderMap[strtolower($vehicle['category'])] ?? 'placeholder.svg';
                    ?>
                    <div style="margin-bottom: 2rem;">
                        <img src="/assets/images/<?= $placeholder ?>" alt="<?= e($vehicle['make'] . ' ' . $vehicle['model']) ?>"
                             style="width: 100%; border-radius: var(--border-radius);">
                    </div>
                <?php endif; ?>
                
                <h2>Vehicle Details</h2>
                <p><?= nl2br(e($vehicle['description'])) ?></p>
                
                <div style="margin-top: 2rem;">
                    <h3>Specifications</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li><i class="fas fa-calendar"></i> <strong>Year:</strong> <?= e($vehicle['year']) ?></li>
                        <li><i class="fas fa-palette"></i> <strong>Colour:</strong> <?= e($vehicle['color']) ?></li>
                        <li><i class="fas fa-users"></i> <strong>Passengers:</strong> Up to <?= $vehicle['max_passengers'] ?></li>
                        <li><i class="fas fa-tag"></i> <strong>Category:</strong> <?= ucwords(str_replace('_', ' ', $vehicle['category'])) ?></li>
                        <li><i class="fas fa-clock"></i> <strong>Minimum Hire:</strong> <?= $vehicle['minimum_hours'] ?> hours</li>
                    </ul>
                </div>
                
                <?php if (!empty($reviews)): ?>
                    <div style="margin-top: 3rem;">
                        <h3>Customer Reviews</h3>
                        <?php foreach ($reviews as $review): ?>
                            <div class="card" style="margin-top: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <strong><?= e($review['first_name']) ?> <?= e(substr($review['last_name'], 0, 1)) ?>.</strong>
                                    <div>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star" style="color: <?= $i <= $review['rating'] ? 'var(--primary-gold)' : 'var(--medium-gray)' ?>;"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p style="margin-top: 0.5rem;"><?= e($review['review_text']) ?></p>
                                <small style="color: var(--dark-gray);"><?= date('d F Y', strtotime($review['created_at'])) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div>
                <div class="card" style="background: var(--light-gray);">
                    <h2 style="color: var(--primary-gold); text-align: center;">
                        <?= formatMoney($vehicle['hourly_rate']) ?>/hour
                    </h2>
                    <p style="text-align: center; color: var(--dark-gray);">
                        Minimum <?= $vehicle['minimum_hours'] ?> hours
                    </p>
                    
                    <?php if (auth()): ?>
                        <form method="POST" action="/booking/create" style="margin-top: 2rem;">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?>">

                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="booking_date" required min="<?= date('Y-m-d') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Start Time</label>
                                <input type="time" name="start_time" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Duration (hours)</label>
                                <select name="duration" required>
                                    <?php for ($i = $vehicle['minimum_hours']; $i <= 12; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?> hours</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Pickup Location</label>
                                <input type="text" name="pickup_location" placeholder="Melbourne, VIC" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Event Type</label>
                                <select name="event_type">
                                    <option value="wedding">Wedding</option>
                                    <option value="corporate">Corporate Event</option>
                                    <option value="photoshoot">Photo Shoot</option>
                                    <option value="special_occasion">Special Occasion</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Special Requirements</label>
                                <textarea name="special_requirements" rows="3" placeholder="Any special requests..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Book Now</button>
                        </form>
                    <?php else: ?>
                        <p style="text-align: center; margin-top: 2rem;">
                            <a href="/login" class="btn btn-primary" style="width: 100%;">Login to Book</a>
                        </p>
                        <p style="text-align: center; margin-top: 1rem;">
                            <a href="/register">Create Account</a>
                        </p>
                    <?php endif; ?>
                    
                    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--medium-gray);">
                        <p style="text-align: center;">
                            <small>Need help? Call us!</small>
                        </p>
                    </div>
                </div>
                
                <div class="card" style="margin-top: 2rem;">
                    <h3>Why Choose Us?</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: var(--primary-gold);"></i> Professional Chauffeurs</li>
                        <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: var(--primary-gold);"></i> Fully Insured</li>
                        <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: var(--primary-gold);"></i> Premium Service</li>
                        <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: var(--primary-gold);"></i> Flexible Bookings</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

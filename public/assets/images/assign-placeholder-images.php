<?php
/**
 * Helper script to assign category-appropriate placeholder images to vehicles
 * Run this script to automatically assign SVG placeholders based on vehicle category
 */

require_once __DIR__ . '/../../app/Database.php';

// Category to image mapping
$categoryImages = [
    'luxury_sedan' => 'assets/images/luxury-sedan.svg',
    'sedan' => 'assets/images/luxury-sedan.svg',
    'muscle_car' => 'assets/images/muscle-car.svg',
    'classic' => 'assets/images/muscle-car.svg',
    'suv' => 'assets/images/luxury-suv.svg',
    'luxury_suv' => 'assets/images/luxury-suv.svg',
    'wedding' => 'assets/images/wedding-car.svg',
    'sports' => 'assets/images/sports-car.svg',
    'sports_car' => 'assets/images/sports-car.svg',
    'supercar' => 'assets/images/sports-car.svg',
    'limousine' => 'assets/images/limousine.svg',
    'limo' => 'assets/images/limousine.svg',
];

try {
    $db = Database::getInstance()->getConnection();

    // Get all vehicles without a primary image
    $stmt = $db->query("
        SELECT id, make, model, category
        FROM vehicles
        WHERE primary_image IS NULL OR primary_image = ''
    ");
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $updated = 0;
    $skipped = 0;

    foreach ($vehicles as $vehicle) {
        $category = strtolower($vehicle['category']);

        // Find appropriate placeholder image
        $image = $categoryImages[$category] ?? 'assets/images/placeholder.svg';

        // Update vehicle with placeholder image
        $update = $db->prepare("
            UPDATE vehicles
            SET primary_image = ?
            WHERE id = ?
        ");

        if ($update->execute([$image, $vehicle['id']])) {
            echo "✓ Updated {$vehicle['make']} {$vehicle['model']} with {$image}\n";
            $updated++;
        } else {
            echo "✗ Failed to update {$vehicle['make']} {$vehicle['model']}\n";
            $skipped++;
        }
    }

    echo "\n";
    echo "==========================================\n";
    echo "Summary:\n";
    echo "- Vehicles updated: {$updated}\n";
    echo "- Vehicles skipped: {$skipped}\n";
    echo "==========================================\n";
    echo "\n";
    echo "Note: These are SVG placeholders. Replace with actual vehicle photos for best results.\n";
    echo "See /public/assets/images/README.md for instructions on adding real photos.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

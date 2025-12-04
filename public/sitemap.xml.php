<?php
/**
 * Dynamic XML Sitemap Generator
 * URL: https://elitecarhire.au/sitemap.xml
 */

header('Content-Type: application/xml; charset=utf-8');

// Load database
require __DIR__ . '/../app/Database.php';

// Static pages
$staticPages = [
    ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => '/vehicles', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['loc' => '/services', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => '/about', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => '/contact', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/faq', 'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => '/terms', 'priority' => '0.4', 'changefreq' => 'yearly'],
    ['loc' => '/privacy', 'priority' => '0.4', 'changefreq' => 'yearly'],
];

// Get dynamic vehicle pages
$vehicles = [];
try {
    $vehicleData = db()->fetchAll("SELECT id, updated_at FROM vehicles WHERE status = 'approved' ORDER BY id");
    foreach ($vehicleData as $vehicle) {
        $vehicles[] = [
            'loc' => '/vehicles/' . $vehicle['id'],
            'priority' => '0.8',
            'changefreq' => 'weekly',
            'lastmod' => date('Y-m-d', strtotime($vehicle['updated_at'] ?? 'now')),
        ];
    }
} catch (\PDOException $e) {
    // Database not available - skip dynamic pages
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php foreach ($staticPages as $page): ?>
    <url>
        <loc>https://elitecarhire.au<?= htmlspecialchars($page['loc']) ?></loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq><?= $page['changefreq'] ?></changefreq>
        <priority><?= $page['priority'] ?></priority>
    </url>
<?php endforeach; ?>
<?php foreach ($vehicles as $vehicle): ?>
    <url>
        <loc>https://elitecarhire.au<?= htmlspecialchars($vehicle['loc']) ?></loc>
        <lastmod><?= $vehicle['lastmod'] ?></lastmod>
        <changefreq><?= $vehicle['changefreq'] ?></changefreq>
        <priority><?= $vehicle['priority'] ?></priority>
    </url>
<?php endforeach; ?>
</urlset>

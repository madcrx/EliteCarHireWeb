<?php
// Load SEO helper
require_once __DIR__ . '/../helpers/seo.php';

// Use static logo (fallback until site_images table is implemented)
// Default to static logo file
$logoPath = '/assets/images/logo.png';

// Try to get logo from site_images table if it exists (for future use)
try {
    $activeLogoId = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'active_logo_id'");
    if ($activeLogoId && $activeLogoId['setting_value']) {
        $logoData = db()->fetch("SELECT image_path FROM site_images WHERE id = ? AND image_type = 'logo'", [$activeLogoId['setting_value']]);
        if ($logoData && !empty($logoData['image_path'])) {
            $logoPath = $logoData['image_path'];
        }
    }
} catch (\PDOException $e) {
    // site_images table doesn't exist - use static logo (normal for now)
}

// Determine current page for SEO
$currentPage = 'home';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
if (strpos($uri, '/vehicles') === 0) $currentPage = 'vehicles';
elseif (strpos($uri, '/services') === 0) $currentPage = 'services';
elseif (strpos($uri, '/about') === 0) $currentPage = 'about';
elseif (strpos($uri, '/contact') === 0) $currentPage = 'contact';
elseif (strpos($uri, '/terms') === 0) $currentPage = 'terms';
elseif (strpos($uri, '/privacy') === 0) $currentPage = 'privacy';
elseif (strpos($uri, '/faq') === 0) $currentPage = 'faq';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= seoPageTitle($currentPage) ?></title>

    <?= seoMetaTags($currentPage) ?>

    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../public/assets/css/style.css') ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <?= seoStructuredData() ?>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <?php if ($logoPath && file_exists(__DIR__ . '/../..' . $logoPath)): ?>
                    <a href="/" style="display: inline-block;"><img src="<?= e($logoPath) ?>" alt="Elite Car Hire" style="max-height: 50px; vertical-align: middle;"></a>
                <?php else: ?>
                    <h1><a href="/" style="color: inherit; text-decoration: none;">Elite Car Hire</a></h1>
                <?php endif; ?>
            </div>
            <div class="nav-links">
                <?php if (auth()): ?>
                    <a href="/<?= $_SESSION['role'] ?>/dashboard">Dashboard</a>
                    <a href="/logout">Logout</a>
                    <span class="user-name"><?= e($_SESSION['name']) ?></span>
                <?php else: ?>
                    <a href="/">Home</a>
                    <a href="/vehicles">Fleet</a>
                    <a href="/services">Services</a>
                    <a href="/about">About</a>
                    <a href="/contact">Contact</a>
                    <a href="/login" class="btn btn-primary">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if ($message = flash('success')): ?>
        <div class="container">
            <div class="alert alert-success"><?= e($message) ?></div>
        </div>
    <?php endif; ?>

    <?php if ($message = flash('error')): ?>
        <div class="container">
            <div class="alert alert-error"><?= e($message) ?></div>
        </div>
    <?php endif; ?>

    <?php
    // Show breadcrumbs on all pages except home and login/register
    $showBreadcrumbs = !in_array($uri, ['/', '/login', '/register', '/logout']);
    if ($showBreadcrumbs && !auth()):
        require __DIR__ . '/components/breadcrumbs.php';
    endif;
    ?>

    <main>
        <?php echo $content ?? '' ?>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <h3>Elite Car Hire</h3>
                    <p>Melbourne's premier luxury chauffeur service specializing in exotic and prestige vehicle hire with professional drivers.</p>
                    <p><i class="fas fa-phone"></i> <a href="tel:0406907849" style="color: inherit;">0406 907 849</a></p>
                    <p><i class="fas fa-envelope"></i> <a href="mailto:support@elitecarhire.au" style="color: inherit;">support@elitecarhire.au</a></p>
                    <p><i class="fas fa-map-marker-alt"></i> Servicing Melbourne & Victoria</p>
                </div>
                <div>
                    <h3>Our Services</h3>
                    <ul>
                        <li><a href="/vehicles">Luxury Vehicle Fleet</a></li>
                        <li><a href="/services">Chauffeur Services</a></li>
                        <li><a href="/services#weddings">Wedding Car Hire</a></li>
                        <li><a href="/services#corporate">Corporate Transport</a></li>
                        <li><a href="/services#events">Special Events</a></li>
                        <li><a href="/contact">Book Now</a></li>
                    </ul>
                </div>
                <div>
                    <h3>Popular Vehicles</h3>
                    <ul>
                        <li>Mercedes AMG Hire</li>
                        <li>Lamborghini Hire</li>
                        <li>Ferrari Hire</li>
                        <li>Porsche Hire</li>
                        <li>Rolls-Royce Hire</li>
                        <li><a href="/vehicles">View Full Fleet</a></li>
                    </ul>
                </div>
                <div>
                    <h3>Service Areas</h3>
                    <p style="font-size: 0.9em; line-height: 1.6;">Professional chauffeur-driven luxury car hire throughout Melbourne CBD, South Yarra, Toorak, Brighton, St Kilda, and greater Melbourne region.</p>
                    <h4 style="margin-top: 1rem;">Legal</h4>
                    <ul>
                        <li><a href="/terms">Terms of Service</a></li>
                        <li><a href="/privacy">Privacy Policy</a></li>
                        <li><a href="/faq">FAQ</a></li>
                    </ul>
                </div>
            </div>
            <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #555;">
                <p style="font-size: 0.85em; color: #999;">
                    <strong>Elite Car Hire</strong> - Premium Luxury Chauffeur Service Melbourne |
                    Exotic Car Hire | Wedding Cars | Corporate Transport |
                    Professional Drivers | Mercedes | Lamborghini | Ferrari | Porsche
                </p>
                <p style="margin-top: 0.5rem;">&copy; <?= date('Y') ?> Elite Car Hire. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="/assets/js/app.min.js"></script>
</body>
</html>

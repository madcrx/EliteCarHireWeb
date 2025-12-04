<?php
/**
 * Breadcrumbs Component
 * Automatically generates breadcrumbs based on current page
 * Also outputs Schema.org breadcrumb markup for SEO
 */

// Determine breadcrumbs based on current URI
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = trim(parse_url($uri, PHP_URL_PATH), '/');
$segments = $path ? explode('/', $path) : [];

$breadcrumbs = [
    ['name' => 'Home', 'url' => '/']
];

// Build breadcrumb trail
if (!empty($segments)) {
    $currentPath = '';
    foreach ($segments as $index => $segment) {
        $currentPath .= '/' . $segment;

        // Convert segment to readable name
        $name = ucwords(str_replace(['-', '_'], ' ', $segment));

        // Map specific URLs to better names
        $nameMap = [
            'vehicles' => 'Our Fleet',
            'services' => 'Services',
            'about' => 'About Us',
            'contact' => 'Contact',
            'faq' => 'FAQ',
            'terms' => 'Terms of Service',
            'privacy' => 'Privacy Policy',
        ];

        if (isset($nameMap[$segment])) {
            $name = $nameMap[$segment];
        }

        // Don't add link for last item (current page)
        if ($index === count($segments) - 1) {
            $breadcrumbs[] = ['name' => $name, 'url' => null];
        } else {
            $breadcrumbs[] = ['name' => $name, 'url' => $currentPath];
        }
    }
}

// Output Schema.org breadcrumb markup (for SEO)
if (count($breadcrumbs) > 1) {
    echo seoBreadcrumbSchema($breadcrumbs);
}
?>

<?php if (count($breadcrumbs) > 1): ?>
<nav aria-label="Breadcrumb" class="breadcrumbs">
    <div class="container">
        <ol itemscope itemtype="https://schema.org/BreadcrumbList" style="display: flex; list-style: none; padding: 1rem 0; margin: 0; font-size: 0.9em; color: #666;">
            <?php foreach ($breadcrumbs as $index => $crumb): ?>
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" style="display: flex; align-items: center;">
                    <?php if ($crumb['url']): ?>
                        <a href="<?= htmlspecialchars($crumb['url']) ?>" itemprop="item" style="color: #667eea; text-decoration: none;">
                            <span itemprop="name"><?= htmlspecialchars($crumb['name']) ?></span>
                        </a>
                        <meta itemprop="position" content="<?= $index + 1 ?>">
                        <span style="margin: 0 0.5rem; color: #999;">/</span>
                    <?php else: ?>
                        <span itemprop="name" style="color: #333; font-weight: 500;"><?= htmlspecialchars($crumb['name']) ?></span>
                        <meta itemprop="position" content="<?= $index + 1 ?>">
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>
<?php endif; ?>

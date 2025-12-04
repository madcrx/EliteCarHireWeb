<?php
/**
 * SEO Helper Functions
 * Provides proper meta tags, structured data, and SEO optimization
 */

/**
 * Generate SEO meta tags for a page
 */
function seoMetaTags($page = 'home') {
    $seo = [
        'home' => [
            'title' => 'Elite Car Hire Melbourne | Luxury Chauffeur Service | Premium Vehicle Hire',
            'description' => 'Melbourne\'s premier luxury chauffeur service. Experience elegance with our exotic car fleet including Mercedes AMG, Lamborghini, Ferrari, Porsche. Professional chauffeur-driven hire services for weddings, corporate events, and special occasions.',
            'keywords' => 'luxury car hire Melbourne, chauffeur service Melbourne, exotic car hire, premium vehicle hire, wedding car hire Melbourne, corporate car hire, Mercedes hire Melbourne, Lamborghini hire, Ferrari hire, Porsche hire, luxury chauffeur Melbourne, prestige car hire',
        ],
        'vehicles' => [
            'title' => 'Luxury Vehicle Fleet | Exotic Cars for Hire Melbourne | Elite Car Hire',
            'description' => 'Browse our exclusive fleet of luxury and exotic vehicles available for chauffeur-driven hire in Melbourne. Mercedes AMG, Lamborghini, Ferrari, Porsche, Audi, and more premium vehicles with professional drivers.',
            'keywords' => 'luxury car fleet Melbourne, exotic cars Melbourne, Mercedes AMG hire, Lamborghini Huracan hire, Ferrari hire Melbourne, Porsche 911 hire, Audi A8 hire, premium vehicle fleet',
        ],
        'services' => [
            'title' => 'Chauffeur Services Melbourne | Wedding Cars | Corporate Transport | Elite Car Hire',
            'description' => 'Professional chauffeur services for weddings, corporate events, airport transfers, and special occasions. Luxury vehicles driven by experienced chauffeurs throughout Melbourne and Victoria.',
            'keywords' => 'chauffeur services Melbourne, wedding car hire, corporate chauffeur, airport transfer Melbourne, special occasion car hire, professional driver service, luxury transport Melbourne',
        ],
        'about' => [
            'title' => 'About Elite Car Hire | Premium Chauffeur Service Melbourne',
            'description' => 'Elite Car Hire is Melbourne\'s trusted luxury chauffeur service, providing professional drivers and exotic vehicles for discerning clients. Learn about our commitment to excellence and premium service.',
            'keywords' => 'about elite car hire, luxury chauffeur company Melbourne, premium car hire service, professional chauffeur Melbourne, exotic car rental company',
        ],
        'contact' => [
            'title' => 'Contact Elite Car Hire | Book Luxury Chauffeur Service Melbourne',
            'description' => 'Contact Elite Car Hire for luxury chauffeur service bookings in Melbourne. Phone 0406 907 849 or email support@elitecarhire.au for premium vehicle hire inquiries.',
            'keywords' => 'contact elite car hire, book chauffeur Melbourne, luxury car hire booking, chauffeur service contact, Melbourne car hire inquiry',
        ],
    ];

    $data = $seo[$page] ?? $seo['home'];

    $output = '<meta name="description" content="' . htmlspecialchars($data['description']) . '">' . "\n";
    $output .= '    <meta name="keywords" content="' . htmlspecialchars($data['keywords']) . '">' . "\n";
    $output .= '    <meta name="author" content="Elite Car Hire">' . "\n";
    $output .= '    <meta name="robots" content="index, follow">' . "\n";
    $output .= '    <link rel="canonical" href="https://elitecarhire.au' . ($_SERVER['REQUEST_URI'] ?? '/') . '">' . "\n";

    // Open Graph tags for social media
    $output .= '    <meta property="og:title" content="' . htmlspecialchars($data['title']) . '">' . "\n";
    $output .= '    <meta property="og:description" content="' . htmlspecialchars($data['description']) . '">' . "\n";
    $output .= '    <meta property="og:type" content="website">' . "\n";
    $output .= '    <meta property="og:url" content="https://elitecarhire.au' . ($_SERVER['REQUEST_URI'] ?? '/') . '">' . "\n";
    $output .= '    <meta property="og:image" content="https://elitecarhire.au/storage/uploads/logo/logo.png">' . "\n";
    $output .= '    <meta property="og:site_name" content="Elite Car Hire">' . "\n";

    // Twitter Card tags
    $output .= '    <meta name="twitter:card" content="summary_large_image">' . "\n";
    $output .= '    <meta name="twitter:title" content="' . htmlspecialchars($data['title']) . '">' . "\n";
    $output .= '    <meta name="twitter:description" content="' . htmlspecialchars($data['description']) . '">' . "\n";

    return $output;
}

/**
 * Generate Schema.org structured data (JSON-LD)
 * This tells Google exactly what your business is
 */
function seoStructuredData() {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'AutoRental',
        'name' => 'Elite Car Hire',
        'description' => 'Premium luxury chauffeur service in Melbourne offering exotic and prestige vehicle hire with professional drivers',
        'url' => 'https://elitecarhire.au',
        'telephone' => '+61406907849',
        'email' => 'support@elitecarhire.au',
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Melbourne',
            'addressRegion' => 'VIC',
            'addressCountry' => 'AU',
        ],
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => '-37.8136',
            'longitude' => '144.9631',
        ],
        'areaServed' => [
            '@type' => 'City',
            'name' => 'Melbourne',
        ],
        'priceRange' => '$$$',
        'image' => 'https://elitecarhire.au/storage/uploads/logo/logo.png',
        'sameAs' => [
            // Add your social media profiles here when available
        ],
        'serviceType' => [
            'Luxury Chauffeur Service',
            'Exotic Car Hire',
            'Wedding Car Hire',
            'Corporate Transport',
            'Airport Transfer',
        ],
        'hasOfferCatalog' => [
            '@type' => 'OfferCatalog',
            'name' => 'Luxury Vehicle Fleet',
            'itemListElement' => [
                [
                    '@type' => 'Offer',
                    'itemOffered' => [
                        '@type' => 'Product',
                        'name' => 'Mercedes AMG Chauffeur Service',
                        'description' => 'Luxury Mercedes AMG vehicles with professional chauffeur',
                    ],
                ],
                [
                    '@type' => 'Offer',
                    'itemOffered' => [
                        '@type' => 'Product',
                        'name' => 'Lamborghini Chauffeur Service',
                        'description' => 'Exotic Lamborghini vehicles with professional chauffeur',
                    ],
                ],
                [
                    '@type' => 'Offer',
                    'itemOffered' => [
                        '@type' => 'Product',
                        'name' => 'Ferrari Chauffeur Service',
                        'description' => 'Prestige Ferrari vehicles with professional chauffeur',
                    ],
                ],
            ],
        ],
    ];

    return '<script type="application/ld+json">' . "\n" . json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n" . '</script>';
}

/**
 * Get optimized page title
 */
function seoPageTitle($page = 'home') {
    $titles = [
        'home' => 'Elite Car Hire Melbourne | Luxury Chauffeur Service | Premium Vehicle Hire',
        'vehicles' => 'Luxury Vehicle Fleet | Exotic Cars for Hire Melbourne | Elite Car Hire',
        'services' => 'Chauffeur Services Melbourne | Wedding Cars | Corporate Transport',
        'about' => 'About Elite Car Hire | Premium Chauffeur Service Melbourne',
        'contact' => 'Contact Elite Car Hire | Book Luxury Chauffeur Service Melbourne',
        'terms' => 'Terms of Service | Elite Car Hire Melbourne',
        'privacy' => 'Privacy Policy | Elite Car Hire Melbourne',
        'faq' => 'Frequently Asked Questions | Elite Car Hire Melbourne',
    ];

    return $titles[$page] ?? 'Elite Car Hire - Luxury Chauffeur Service Melbourne';
}

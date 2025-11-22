<?php ob_start(); ?>
<div class="container" style="padding: 4rem 0;">
    <h1 style="text-align: center; color: var(--primary-gold); margin-bottom: 3rem;">Our Services</h1>

    <div style="max-width: 900px; margin: 0 auto;">
        <div class="card">
            <h2 style="color: var(--primary-gold);"><i class="fas fa-wedding"></i> Wedding & Special Events</h2>
            <p>Make your special day unforgettable with our premium collection of luxury and classic vehicles. From vintage classics to modern exotic cars, we provide the perfect transport for weddings, engagements, and milestone celebrations.</p>
            <ul>
                <li>Chauffeur-driven service available</li>
                <li>Ribbon and decoration options</li>
                <li>Multiple vehicle packages</li>
                <li>Red carpet service</li>
            </ul>
        </div>

        <div class="card">
            <h2 style="color: var(--primary-gold);"><i class="fas fa-camera"></i> Photo Shoots & Film</h2>
            <p>Our stunning collection of classic muscle cars and luxury vehicles are perfect for:</p>
            <ul>
                <li>Commercial photography and videography</li>
                <li>Music videos and film productions</li>
                <li>Fashion and automotive shoots</li>
                <li>Social media content creation</li>
            </ul>
            <p>Flexible hourly rates and full-day packages available.</p>
        </div>

        <div class="card">
            <h2 style="color: var(--primary-gold);"><i class="fas fa-briefcase"></i> Corporate Events</h2>
            <p>Impress clients and colleagues with premium transportation for your corporate needs:</p>
            <ul>
                <li>Executive airport transfers</li>
                <li>Corporate event transportation</li>
                <li>Client entertainment and hospitality</li>
                <li>Product launches and promotional events</li>
                <li>Team building experiences</li>
            </ul>
        </div>

        <div class="card">
            <h2 style="color: var(--primary-gold);"><i class="fas fa-gift"></i> Special Occasions</h2>
            <p>Celebrate life's important moments in style:</p>
            <ul>
                <li>Birthdays and anniversaries</li>
                <li>School formals and graduations</li>
                <li>Proposal and romantic experiences</li>
                <li>Race day and sporting events</li>
                <li>Weekend getaways</li>
            </ul>
        </div>

        <div class="card">
            <h2 style="color: var(--primary-gold);"><i class="fas fa-user-tie"></i> Chauffeur Services</h2>
            <p>All our vehicles can be hired with professional, experienced chauffeurs who provide:</p>
            <ul>
                <li>Fully licensed and insured drivers</li>
                <li>Professional presentation and conduct</li>
                <li>Local knowledge and route planning</li>
                <li>Discreet and reliable service</li>
                <li>Meet and greet service available</li>
            </ul>
        </div>

        <div class="card">
            <h2 style="color: var(--primary-gold);"><i class="fas fa-shield-alt"></i> Why Choose Elite Car Hire?</h2>
            <ul>
                <li><strong>Premium Fleet:</strong> Carefully selected and maintained vehicles</li>
                <li><strong>Fully Insured:</strong> Comprehensive coverage for your peace of mind</li>
                <li><strong>Professional Service:</strong> Experienced team dedicated to excellence</li>
                <li><strong>Flexible Packages:</strong> Custom solutions for any occasion</li>
                <li><strong>Competitive Rates:</strong> Transparent pricing with no hidden fees</li>
                <li><strong>24/7 Support:</strong> Available whenever you need us</li>
            </ul>
        </div>

        <div class="card" style="background: var(--light-gray); text-align: center;">
            <h3 style="color: var(--primary-gold); margin-bottom: 1rem;">Ready to Book?</h3>
            <p>Contact us today to discuss your requirements and receive a personalized quote.</p>
            <div style="margin-top: 2rem;">
                <a href="/contact" class="btn btn-primary" style="margin-right: 1rem;">Get a Quote</a>
                <a href="/vehicles" class="btn btn-primary">View Our Fleet</a>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

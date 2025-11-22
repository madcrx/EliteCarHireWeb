<?php ob_start(); ?>
<div class="container" style="padding: 4rem 0;">
    <h1 style="text-align: center; color: var(--primary-gold); margin-bottom: 3rem;">About Elite Car Hire</h1>

    <div style="max-width: 900px; margin: 0 auto;">
        <div class="card">
            <h2 style="color: var(--primary-gold);">Melbourne's Premier Luxury Vehicle Hire</h2>
            <p>Welcome to Elite Car Hire, Melbourne's trusted provider of premium and classic vehicle hire services. We specialize in providing unforgettable experiences through our carefully curated collection of luxury exotic cars and iconic classic muscle cars.</p>

            <p>Whether you're planning a wedding, corporate event, photo shoot, or simply want to experience the thrill of driving a dream car, we offer the perfect vehicle to make your occasion extraordinary.</p>
        </div>

        <div class="card">
            <h2 style="color: var(--primary-gold);">Our Story</h2>
            <p>Founded on a passion for exceptional automobiles and outstanding customer service, Elite Car Hire was established to bring the luxury car hire experience to a new level in Melbourne. We understand that every journey is special, and we're committed to ensuring that your experience with us is nothing short of exceptional.</p>

            <p>Our team comprises automotive enthusiasts who share a deep appreciation for both classic and contemporary vehicles. This passion drives us to maintain our fleet to the highest standards and provide service that exceeds expectations.</p>
        </div>

        <div class="card">
            <h2 style="color: var(--primary-gold);">Our Fleet</h2>
            <p>Our diverse collection features:</p>
            <ul>
                <li><strong>Classic Muscle Cars:</strong> Iconic Australian and American muscle cars that turn heads and create lasting memories</li>
                <li><strong>Luxury Exotic Vehicles:</strong> Premium sports cars and luxury sedans for those who demand the finest</li>
                <li><strong>Wedding Specials:</strong> Elegant and timeless vehicles perfect for your special day</li>
                <li><strong>Event Vehicles:</strong> Show-stopping cars for photo shoots, films, and promotional events</li>
            </ul>
            <p>Each vehicle in our fleet is meticulously maintained, fully insured, and presented in pristine condition.</p>
        </div>

        <div class="card">
            <h2 style="color: var(--primary-gold);">Our Commitment</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-top: 1.5rem;">
                <div>
                    <h3><i class="fas fa-star" style="color: var(--primary-gold);"></i> Excellence</h3>
                    <p>We maintain the highest standards in vehicle presentation and customer service.</p>
                </div>
                <div>
                    <h3><i class="fas fa-shield-alt" style="color: var(--primary-gold);"></i> Safety</h3>
                    <p>All vehicles are fully insured and regularly serviced to ensure your safety and peace of mind.</p>
                </div>
                <div>
                    <h3><i class="fas fa-handshake" style="color: var(--primary-gold);"></i> Reliability</h3>
                    <p>We pride ourselves on punctuality, professionalism, and delivering on our promises.</p>
                </div>
                <div>
                    <h3><i class="fas fa-heart" style="color: var(--primary-gold);"></i> Passion</h3>
                    <p>Our love for exceptional automobiles is evident in every aspect of our service.</p>
                </div>
            </div>
        </div>

        <div class="card">
            <h2 style="color: var(--primary-gold);">Why Choose Us?</h2>
            <ul>
                <li><strong>Experience:</strong> Years of experience in the luxury car hire industry</li>
                <li><strong>Local Knowledge:</strong> As Melbourne locals, we understand the best routes and locations</li>
                <li><strong>Flexible Service:</strong> Custom packages tailored to your specific needs</li>
                <li><strong>Professional Chauffeurs:</strong> Experienced, licensed, and courteous drivers available</li>
                <li><strong>Transparent Pricing:</strong> Clear, competitive rates with no hidden costs</li>
                <li><strong>Customer Focused:</strong> Your satisfaction is our top priority</li>
            </ul>
        </div>

        <div class="card">
            <h2 style="color: var(--primary-gold);">Our Service Area</h2>
            <p>Based in Melbourne, we service all metropolitan areas and regional Victoria. Whether your event is in the city, Mornington Peninsula, Yarra Valley, or beyond, we can accommodate your needs.</p>
            <p>Special arrangements can be made for interstate events - please contact us to discuss your requirements.</p>
        </div>

        <div class="card" style="background: var(--light-gray); text-align: center;">
            <h3 style="color: var(--primary-gold); margin-bottom: 1rem;">Experience the Elite Difference</h3>
            <p>Let us help make your next event truly memorable with our exceptional vehicles and outstanding service.</p>
            <div style="margin-top: 2rem;">
                <a href="/vehicles" class="btn btn-primary" style="margin-right: 1rem;">Browse Our Fleet</a>
                <a href="/contact" class="btn btn-primary">Contact Us Today</a>
            </div>
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--medium-gray);">
                <p><i class="fas fa-phone"></i> <strong>0406 907 849</strong></p>
                <p><i class="fas fa-envelope"></i> <strong>support@elitecarhire.au</strong></p>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

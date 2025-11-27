<?php ob_start(); ?>
<div class="container" style="max-width: 800px; padding: 4rem 0;">
    <h1 style="text-align: center; color: var(--primary-gold); margin-bottom: 3rem;">Contact Us</h1>
    
    <div class="card">
        <div style="margin-bottom: 2rem;">
            <h3><i class="fas fa-envelope"></i> Email</h3>
            <p>support@elitecarhire.au</p>
        </div>

        <h2>Send us a message</h2>
        <form method="POST" action="/contact/submit">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone">
            </div>
            
            <div class="form-group">
                <label>Subject</label>
                <input type="text" name="subject">
            </div>
            
            <div class="form-group">
                <label>Message *</label>
                <textarea name="message" rows="6" required></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message</button>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

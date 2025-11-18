<?php ob_start(); ?>
<div class="container" style="max-width: 500px; margin: 100px auto;">
    <div class="card">
        <h2 style="text-align: center; color: var(--primary-gold); margin-bottom: 2rem;">Login to Elite Car Hire</h2>
        
        <form method="POST" action="/login">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
        
        <p style="text-align: center; margin-top: 1.5rem;">
            Don't have an account? <a href="/register" style="color: var(--primary-gold);">Register here</a>
        </p>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

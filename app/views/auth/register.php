<?php ob_start(); ?>
<div class="container" style="max-width: 600px; margin: 50px auto;">
    <div class="card">
        <h2 style="text-align: center; color: var(--primary-gold); margin-bottom: 2rem;">Create Your Account</h2>
        
        <form method="POST" action="/register">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" value="<?= old('first_name') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" value="<?= old('last_name') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= old('email') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" value="<?= old('phone') ?>">
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="password_confirm" required>
            </div>
            
            <div class="form-group">
                <label>Account Type</label>
                <select name="role" required>
                    <option value="customer">Customer</option>
                    <option value="owner">Vehicle Owner</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
        </form>
        
        <p style="text-align: center; margin-top: 1.5rem;">
            Already have an account? <a href="/login" style="color: var(--primary-gold);">Login here</a>
        </p>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

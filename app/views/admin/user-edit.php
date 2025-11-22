<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Edit User</h1>
            <a href="/admin/users/<?= $user['id'] ?>" class="btn" style="background: var(--medium-gray);">
                <i class="fas fa-arrow-left"></i> Back to User Details
            </a>
        </div>

        <form method="POST" action="/admin/users/<?= $user['id'] ?>/update">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="card">
                <h2>Basic Information</h2>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" name="first_name" id="first_name" value="<?= e($user['first_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" name="last_name" id="last_name" value="<?= e($user['last_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" name="email" id="email" value="<?= e($user['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" name="phone" id="phone" value="<?= e($user['phone'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select name="role" id="role" required onchange="toggleOwnerFields()">
                            <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                            <option value="owner" <?= $user['role'] === 'owner' ? 'selected' : '' ?>>Owner</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select name="status" id="status" required>
                            <option value="pending" <?= $user['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="suspended" <?= $user['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            <option value="rejected" <?= $user['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Owner-specific fields -->
            <div class="card" id="ownerFields" style="display: <?= $user['role'] === 'owner' ? 'block' : 'none' ?>;">
                <h2><i class="fas fa-building"></i> Owner Information</h2>
                <p style="color: var(--dark-gray); margin-bottom: 1.5rem;">
                    Additional information required for vehicle owners
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="company_name">Company Name</label>
                        <input type="text" name="company_name" id="company_name" value="<?= e($user['company_name'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="abn">ABN (Australian Business Number)</label>
                        <input type="text" name="abn" id="abn" value="<?= e($user['abn'] ?? '') ?>" placeholder="11 222 333 444">
                    </div>

                    <div class="form-group">
                        <label for="license_number">Driver's License Number</label>
                        <input type="text" name="license_number" id="license_number" value="<?= e($user['license_number'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Password Change Section -->
            <div class="card" style="background: var(--light-gray);">
                <h2><i class="fas fa-key"></i> Change Password</h2>
                <p style="color: var(--dark-gray); margin-bottom: 1.5rem;">
                    Leave blank to keep current password. Password must be at least 8 characters.
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" name="new_password" id="new_password" minlength="8" placeholder="Enter new password">
                        <small style="color: var(--dark-gray); display: block; margin-top: 0.5rem;">
                            Minimum 8 characters
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" minlength="8" placeholder="Confirm new password">
                    </div>
                </div>

                <div id="passwordMatchError" style="color: red; display: none; margin-top: 0.5rem;">
                    <i class="fas fa-exclamation-circle"></i> Passwords do not match
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <a href="/admin/users/<?= $user['id'] ?>" class="btn" style="background: var(--medium-gray);">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>

        <div class="card" style="background: #fff8dc; border-left: 4px solid var(--primary-gold); margin-top: 2rem;">
            <h3><i class="fas fa-info-circle"></i> Important Notes</h3>
            <ul>
                <li><strong>Email Changes:</strong> Changing the email will update the user's login credentials</li>
                <li><strong>Role Changes:</strong> Changing roles may affect user permissions and access</li>
                <li><strong>Password Reset:</strong> Only fill in password fields if you want to change the user's password</li>
                <li><strong>Owner Fields:</strong> Owner-specific fields only appear when role is set to "Owner"</li>
                <li><strong>Status:</strong> Suspended users cannot log in, Rejected users cannot be re-activated without approval</li>
            </ul>
        </div>
    </div>
</div>

<script>
// Toggle owner fields based on role selection
function toggleOwnerFields() {
    const role = document.getElementById('role').value;
    const ownerFields = document.getElementById('ownerFields');
    ownerFields.style.display = role === 'owner' ? 'block' : 'none';
}

// Password match validation
const newPassword = document.getElementById('new_password');
const confirmPassword = document.getElementById('confirm_password');
const matchError = document.getElementById('passwordMatchError');
const submitBtn = document.getElementById('submitBtn');

function validatePasswords() {
    if (newPassword.value || confirmPassword.value) {
        if (newPassword.value !== confirmPassword.value) {
            matchError.style.display = 'block';
            submitBtn.disabled = true;
            return false;
        } else {
            matchError.style.display = 'none';
            submitBtn.disabled = false;
            return true;
        }
    } else {
        matchError.style.display = 'none';
        submitBtn.disabled = false;
        return true;
    }
}

newPassword?.addEventListener('input', validatePasswords);
confirmPassword?.addEventListener('input', validatePasswords);

// Form submission validation
document.querySelector('form')?.addEventListener('submit', function(e) {
    if (!validatePasswords()) {
        e.preventDefault();
        alert('Please ensure passwords match before submitting');
    }
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

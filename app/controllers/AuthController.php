<?php
namespace controllers;

class AuthController {
    
    public function showLogin() {
        if (auth()) {
            $role = $_SESSION['role'];
            redirect("/$role/dashboard");
        }
        view('auth/login');
    }
    
    public function login() {
        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/login');
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            flash('error', 'Email and password are required');
            redirect('/login');
        }
        
        $sql = "SELECT * FROM users WHERE email = ?";
        $user = db()->fetch($sql, [$email]);
        
        if (!$user || !password_verify($password, $user['password'])) {
            flash('error', 'Invalid credentials');
            logAudit('failed_login', 'users', null);
            redirect('/login');
        }
        
        if ($user['status'] !== 'active') {
            flash('error', 'Your account is not active. Please contact support.');
            redirect('/login');
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
        
        // Update last login
        db()->execute("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
        
        logAudit('login', 'users', $user['id']);
        
        redirect("/{$user['role']}/dashboard");
    }
    
    public function showRegister() {
        if (auth()) {
            redirect('/' . $_SESSION['role'] . '/dashboard');
        }
        view('auth/register');
    }
    
    public function register() {
        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/register');
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $role = $_POST['role'] ?? 'customer';

        // Validation
        $errors = [];
        
        if (empty($email) || !isValidEmail($email)) {
            $errors[] = 'Valid email is required';
        }
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        
        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match';
        }
        
        if (empty($firstName) || empty($lastName)) {
            $errors[] = 'First and last name are required';
        }
        
        // Check if email exists
        $existing = db()->fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            $errors[] = 'Email already registered';
        }
        
        if (!empty($errors)) {
            flash('error', implode(', ', $errors));
            setOld($_POST);
            redirect('/register');
        }
        
        // Determine status
        $status = ($role === 'customer' && config('settings.auto_approve_customers')) ? 'active' : 'pending';
        
        // Create user
        $sql = "INSERT INTO users (email, password, first_name, last_name, phone, role, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        db()->execute($sql, [$email, $hashedPassword, $firstName, $lastName, $phone, $role, $status]);
        
        $userId = db()->lastInsertId();
        
        logAudit('user_register', 'users', $userId);
        
        if ($status === 'active') {
            flash('success', 'Registration successful! You can now login.');
        } else {
            flash('success', 'Registration successful! Your account is pending approval.');
        }
        
        redirect('/login');
    }
    
    public function logout() {
        if (auth()) {
            logAudit('logout', 'users', $_SESSION['user_id']);
        }

        // Clear all session variables
        $_SESSION = [];

        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy the session
        session_destroy();

        // Redirect to home page
        redirect('/');
    }
}

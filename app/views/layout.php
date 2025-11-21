<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Elite Car Hire' ?> - Luxury Vehicle Hire Melbourne</title>
    <link rel="stylesheet" href="/assets/css/style.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>Elite Car Hire</h1>
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

    <main>
        <?php echo $content ?? '' ?>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <h3>Elite Car Hire</h3>
                    <p>Melbourne's premier luxury vehicle hire service</p>
                    <p><i class="fas fa-phone"></i> 0406 907 849</p>
                    <p><i class="fas fa-envelope"></i> support@elitecarhire.au</p>
                </div>
                <div>
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="/vehicles">Our Fleet</a></li>
                        <li><a href="/services">Services</a></li>
                        <li><a href="/about">About Us</a></li>
                        <li><a href="/support">Support</a></li>
                    </ul>
                </div>
                <div>
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="/terms">Terms of Service</a></li>
                        <li><a href="/privacy">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #555;">
                <p>&copy; <?= date('Y') ?> Elite Car Hire. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="/assets/js/app.min.js"></script>
</body>
</html>

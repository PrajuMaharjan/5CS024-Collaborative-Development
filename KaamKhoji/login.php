<?php
// login.php - Login Page
// Handles GET (show form) and POST (process login)
require_once 'includes/auth.php';
require_once 'includes/db.php';

// If already logged in, redirect
if (isLoggedIn()) {
    header('Location: ' . getDashboardUrl());
    exit;
}

$error = '';

// ---- Process Login Form ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $pdo = getPDO();

        // Use PDO prepared statement to safely query the database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Verify password against hashed password in DB
        if ($user && password_verify($password, $user['password'])) {
            loginUser($user); // Sets session variables

            // Redirect to appropriate dashboard
            header('Location: ' . getDashboardUrl());
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KaamKhoji</title>
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700,900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>

<!-- ---- Standalone login page (no nav) ---- -->
<div class="auth-page">
    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo">
            <a href="<?= BASE_URL ?>/index.php">
                <img src="<?= BASE_URL ?>/assets/kaamkhoji.png" alt="KaamKhoji" style="height:120px;width:auto;display:block;margin:-32px auto;">
            </a>
        </div>

        <h1 class="auth-title">Welcome Back</h1>
        <p class="auth-subtitle">Login to your KaamKhoji account</p>

        <!-- Error message -->
        <?php if ($error): ?>
            <div class="flash flash-error" style="border-radius:8px; margin-bottom:1rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="<?= BASE_URL ?>/login.php" data-validate>
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       class="form-control"
                       placeholder="you@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required>
                <span class="form-error">Please enter a valid email.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="password-wrap">
                    <input type="password" id="password" name="password"
                           class="form-control"
                           placeholder="Enter your password"
                           required>
                    <button type="button" class="pw-toggle" id="pwToggle" aria-label="Show/hide password" tabindex="-1">
                        <svg class="eye-show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-hide" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
                <span class="form-error">Password is required.</span>
            </div>

            <div style="text-align:right; margin-top:-0.5rem; margin-bottom:1rem;">
                <a href="<?= BASE_URL ?>/forgot-password.php" class="forgot-password">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top:0.5rem;">
                Login
            </button>
        </form>

        <div class="auth-switch">
            Don't have an account? <a href="<?= BASE_URL ?>/signup.php">Sign up here</a>
        </div>
        <div class="text-center mt-2">
            <a href="<?= BASE_URL ?>/index.php" style="font-size:0.82rem; color:var(--text-muted);">← Back to Home</a>
        </div>
    </div>
</div>

<style>
.password-wrap { position: relative; display: flex; align-items: center; }
.password-wrap .form-control { padding-right: 2.8rem; }
.pw-toggle {
    position: absolute; right: 0.75rem; background: none; border: none;
    cursor: pointer; color: var(--text-muted); padding: 0; display: flex;
    align-items: center; transition: color 0.2s;
}
.pw-toggle:hover { color: var(--primary); }
.forgot-password {
    font-size: 0.82rem; color: var(--text-muted);
    display: inline-block; transition: color 0.2s ease;
    text-decoration: none;
}
.forgot-password:hover { color: var(--primary); }
</style>
<script src="<?= BASE_URL ?>/js/utils.js"></script>
<script src="<?= BASE_URL ?>/js/flash.js"></script>
<script src="<?= BASE_URL ?>/js/form-validation.js"></script>
<script>
(function(){
    const btn = document.getElementById('pwToggle');
    const inp = document.getElementById('password');
    if (!btn || !inp) return;
    btn.addEventListener('click', function() {
        const show = inp.type === 'password';
        inp.type = show ? 'text' : 'password';
        btn.querySelector('.eye-show').style.display = show ? 'none' : '';
        btn.querySelector('.eye-hide').style.display = show ? '' : 'none';
    });
})();
</script>
</body>
</html>

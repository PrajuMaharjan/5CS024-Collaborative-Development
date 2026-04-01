<?php
// ============================================================
// login.php - Login Page
// Handles GET (show form) and POST (process login)
// ============================================================
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>

<!-- ---- Standalone login page (no nav) ---- -->
<div class="auth-page">
    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo">
            <a href="<?= BASE_URL ?>/index.php">
                <img src="<?= BASE_URL ?>/assets/logo.svg" alt="KaamKhoji" style="width:52px;height:52px;">
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
                <input type="password" id="password" name="password"
                       class="form-control"
                       placeholder="Enter your password"
                       required>
                <span class="form-error">Password is required.</span>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top:0.5rem;">
                Login →
            </button>
        </form>

        <!-- Demo accounts hint -->
        <div style="background:var(--primary-light); border-radius:8px; padding:0.75rem 1rem; margin-top:1rem; font-size:0.8rem; color:var(--primary);">
            <strong>Demo accounts:</strong><br>
            Admin: admin@kaamkhoji.com | Employer: employer@demo.com | Seeker: seeker@demo.com<br>
            Password: <strong>password</strong>
        </div>

        <div class="auth-switch">
            Don't have an account? <a href="<?= BASE_URL ?>/signup.php">Sign up here</a>
        </div>
        <div class="text-center mt-2">
            <a href="<?= BASE_URL ?>/index.php" style="font-size:0.82rem; color:var(--text-muted);">← Back to Home</a>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/main.js"></script>
</body>
</html>

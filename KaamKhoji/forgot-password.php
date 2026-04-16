<?php
// ============================================================
// forgot-password.php
// Step 1 of password reset:
//   - User enters their email
//   - We generate a secure token and store it in the DB
//   - We display the reset link (no email server needed on XAMPP)
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Already logged in? Redirect to dashboard
if (isLoggedIn()) {
    header('Location: ' . getDashboardUrl());
    exit;
}

// Auto-create the table if it doesn't exist yet
$pdo = getPDO();
$pdo->exec("
    CREATE TABLE IF NOT EXISTS password_resets (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        email      VARCHAR(150) NOT NULL,
        token      VARCHAR(64)  NOT NULL UNIQUE,
        expires_at DATETIME     NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

$error     = '';
$message   = '';
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email exists in users table
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate a secure random 64-character token
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Remove any old reset requests for this email
            $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

            // Save new token to database
            $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)")
                ->execute([$email, $token, $expires]);

            // Build the reset link
            $resetLink = BASE_URL . '/reset-password.php?token=' . $token;
        }

        // Always show this message — don't reveal if email exists (security)
        $message = 'Reset link generated. Use the link below to reset your password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - KaamKhoji</title>
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700,900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo">
            <a href="<?= BASE_URL ?>/index.php">
                <img src="<?= BASE_URL ?>/assets/KaamKhoji.png" alt="KaamKhoji" style="height:80px;width:auto;display:block;margin:-20px auto;">
            </a>
        </div>

        <h1 class="auth-title">Forgot Password</h1>
        <p class="auth-subtitle">Enter your email to get a password reset link</p>

        <?php if ($error): ?>
            <div class="flash flash-error" style="border-radius:8px; margin-bottom:1rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="flash flash-success" style="border-radius:8px; margin-bottom:1rem;">
                <?= htmlspecialchars($message) ?>
            </div>

            <?php if ($resetLink): ?>
                <!-- Reset link displayed here (works without email server on XAMPP) -->
                <div style="background:#f0fdff; border:1px solid #00b4d8; border-radius:8px; padding:1rem; margin-bottom:1.25rem; word-break:break-all;">
                    <p style="font-size:0.78rem; color:#64748b; margin-bottom:0.5rem; font-weight:600;">YOUR RESET LINK:</p>
                    <a href="<?= htmlspecialchars($resetLink) ?>" style="font-size:0.82rem; color:#00b4d8; word-break:break-all;">
                        <?= htmlspecialchars($resetLink) ?>
                    </a>
                    <p style="font-size:0.75rem; color:#94a3b8; margin-top:0.5rem;">This link expires in 1 hour.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!$message): ?>
            <form method="POST" action="<?= BASE_URL ?>/forgot-password.php">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           class="form-control"
                           placeholder="Enter your registered email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Get Reset Link
                </button>
            </form>
        <?php endif; ?>

        <div class="auth-switch" style="margin-top:1.25rem;">
            <a href="<?= BASE_URL ?>/login.php">← Back to Login</a>
        </div>

    </div>
</div>

</body>
</html>
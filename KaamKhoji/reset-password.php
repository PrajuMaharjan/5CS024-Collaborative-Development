<?php
// ============================================================
// reset-password.php
// Step 2 of password reset:
//   - User arrives here via the link from forgot-password.php
//   - We validate the token (must exist and not be expired)
//   - User enters a new password
//   - Password is updated and token is deleted
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

$token   = trim($_GET['token'] ?? '');
$error   = '';
$success = '';

// No token in URL? Send back to login
if (empty($token)) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$pdo = getPDO();

// Look up the token — also check it hasn't expired
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$reset = $stmt->fetch();

// Handle the new-password form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset) {
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Hash the new password and update the user's record
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE email = ?")
            ->execute([$hashed, $reset['email']]);

        // Delete the token so it can't be reused
        $pdo->prepare("DELETE FROM password_resets WHERE token = ?")
            ->execute([$token]);

        $success = 'Password updated! You can now log in.';
        $reset   = null; // hide the form
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - KaamKhoji</title>
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

        <h1 class="auth-title">Reset Password</h1>
        <p class="auth-subtitle">Enter your new password below</p>

        <?php if ($error): ?>
            <div class="flash flash-error" style="border-radius:8px; margin-bottom:1rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="flash flash-success" style="border-radius:8px; margin-bottom:1.5rem;">
                <?= htmlspecialchars($success) ?>
            </div>
            <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-block">Go to Login</a>

        <?php elseif (!$reset): ?>
            <!-- Token invalid or expired -->
            <div class="flash flash-error" style="border-radius:8px; margin-bottom:1.5rem;">
                This reset link is invalid or has expired.
            </div>
            <a href="<?= BASE_URL ?>/forgot-password.php" class="btn btn-outline btn-block">Request a New Link</a>

        <?php else: ?>
            <!-- Token is valid — show the new password form -->
            <form method="POST" action="<?= BASE_URL ?>/reset-password.php?token=<?= htmlspecialchars($token) ?>">

                <div class="form-group">
                    <label class="form-label" for="password">New Password</label>
                    <input type="password" id="password" name="password"
                           class="form-control"
                           placeholder="Min. 6 characters"
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           class="form-control"
                           placeholder="Repeat new password"
                           required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Update Password
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

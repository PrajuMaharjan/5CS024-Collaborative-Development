<?php
// signup.php - User Registration Page
// Creates a new account as 'seeker' or 'employer'
require_once 'includes/auth.php';
require_once 'includes/db.php';

// If already logged in, redirect
if (isLoggedIn()) {
    header('Location: ' . getDashboardUrl());
    exit;
}

$error   = '';
$success = '';

// ---- Process Signup Form ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');
    $role     = $_POST['role'] ?? 'seeker';

    // Validate role (only allow seeker or employer from signup)
    if (!in_array($role, ['seeker', 'employer'])) {
        $role = 'seeker';
    }

    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $pdo = getPDO();

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'This email is already registered. <a href="' . BASE_URL . '/login.php">Login instead?</a>';
        } else {
            // Hash the password (never store plain text!)
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user into database using PDO
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, role)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$name, $email, $hashedPassword, $role]);

            // Auto-login after signup
            $userId = $pdo->lastInsertId();
            $newUser = ['id' => $userId, 'name' => $name, 'email' => $email, 'role' => $role];
            loginUser($newUser);

            header('Location: ' . getDashboardUrl() . '?msg=welcome');
            exit;
        }
    }
}

// Pre-select role from URL param (e.g., signup.php?role=employer)
$preRole = $_GET['role'] ?? 'seeker';
if (!in_array($preRole, ['seeker', 'employer'])) $preRole = 'seeker';
$preRole = $_POST['role'] ?? $preRole;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - KaamKhoji</title>
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700,900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>

<body>

    <div class="auth-page">
        <div class="auth-card">

            <div class="auth-logo">
                <a href="<?= BASE_URL ?>/index.php">
                    <img src="<?= BASE_URL ?>/assets/kaamkhoji.png" alt="KaamKhoji" style="height:120px;width:auto;display:block;margin:-32px auto;">
                </a>
            </div>

            <h1 class="auth-title">Create Account</h1>
            <p class="auth-subtitle">Join KaamKhoji today — it's free!</p>

            <!-- Error message -->
            <?php if ($error): ?>
                <div class="flash flash-error" style="border-radius:8px; margin-bottom:1rem;">
                    <?= $error /* contains link so no escaping */ ?>
                </div>
            <?php endif; ?>

            <!-- Signup Form -->
            <form method="POST" action="<?= BASE_URL ?>/signup.php" data-validate>

                <!-- Role Selector -->
                <div class="form-group">
                    <label class="form-label">I am a...</label>
                    <div class="role-selector">
                        <input type="radio" name="role" id="roleSeeker" value="seeker" class="role-option"
                            <?= ($preRole === 'seeker') ? 'checked' : '' ?>>
                        <label for="roleSeeker" class="role-label">
                            Job Seeker
                        </label>

                        <input type="radio" name="role" id="roleEmployer" value="employer" class="role-option"
                            <?= ($preRole === 'employer') ? 'checked' : '' ?>>
                        <label for="roleEmployer" class="role-label">
                            Employer
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                        class="form-control"
                        placeholder="Your full name"
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                        required>
                    <span class="form-error">Name is required.</span>
                </div>

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
                        placeholder="Min. 6 characters"
                        required>
                    <span class="form-error">Password must be at least 6 characters.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                        class="form-control"
                        placeholder="Repeat your password"
                        required>
                    <span class="form-error">Passwords do not match.</span>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Create Account →
                </button>
            </form>

            <div class="auth-switch">
                Already have an account? <a href="<?= BASE_URL ?>/login.php">Login here</a>
            </div>
            <div class="text-center mt-2">
                <a href="<?= BASE_URL ?>/index.php" style="font-size:0.82rem; color:var(--text-muted);">← Back to Home</a>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/utils.js"></script>
    <script src="<?= BASE_URL ?>/js/flash.js"></script>
    <script src="<?= BASE_URL ?>/js/form-validation.js"></script>
</body>

</html>
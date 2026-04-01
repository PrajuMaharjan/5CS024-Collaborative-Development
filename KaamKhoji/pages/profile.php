<?php
// ============================================================
// pages/profile.php - Edit Profile (all roles)
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();

$pdo    = getPDO();
$userId = getUserId();
$error  = '';
$success= '';

// Load current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $phone    = trim($_POST['phone']    ?? '');
    $location = trim($_POST['location'] ?? '');
    $bio      = trim($_POST['bio']      ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');

    if (empty($name)) {
        $error = 'Name cannot be empty.';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif (!empty($password) && $password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        if (!empty($password)) {
            // Update with new password
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name=?, phone=?, location=?, bio=?, password=? WHERE id=?");
            $stmt->execute([$name, $phone, $location, $bio, $hashed, $userId]);
        } else {
            // Update without changing password
            $stmt = $pdo->prepare("UPDATE users SET name=?, phone=?, location=?, bio=? WHERE id=?");
            $stmt->execute([$name, $phone, $location, $bio, $userId]);
        }

        // Update session name
        $_SESSION['name'] = $name;
        $success = 'Profile updated successfully!';

        // Reload user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
    }
}

$pageTitle = 'My Profile';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>👤 My Profile</h1>
        <p>Update your personal information</p>
    </div>
</div>

<div class="container section">
    <div style="max-width:640px; margin:0 auto;">

        <?php if ($success): ?>
            <div class="flash flash-success" style="border-radius:8px; margin-bottom:1.5rem;">
                ✅ <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="flash flash-error" style="border-radius:8px; margin-bottom:1.5rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Profile Header Card -->
        <div class="card mb-3" style="text-align:center; padding:2rem;">
            <div style="width:72px;height:72px;border-radius:50%;background:var(--primary-light);display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 1rem;">
                👤
            </div>
            <h2 style="font-size:1.2rem; font-weight:700;"><?= htmlspecialchars($user['name']) ?></h2>
            <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
            <span class="status-badge <?= $user['role']==='employer'?'status-accepted':($user['role']==='admin'?'status-reviewed':'status-pending') ?>" style="margin-top:0.5rem; display:inline-block;">
                <?= ucfirst($user['role']) ?>
            </span>
        </div>

        <!-- Edit Form -->
        <div class="card">
            <h3 style="font-size:1rem; font-weight:600; margin-bottom:1.5rem;">Edit Information</h3>

            <form method="POST" action="<?= BASE_URL ?>/pages/profile.php" data-validate>

                <div class="form-group">
                    <label class="form-label" for="name">Full Name *</label>
                    <input type="text" id="name" name="name"
                           class="form-control"
                           value="<?= htmlspecialchars($user['name']) ?>"
                           required>
                    <span class="form-error">Name is required.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled
                           style="opacity:0.6; cursor:not-allowed;">
                    <small class="text-muted">Email cannot be changed.</small>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone"
                               class="form-control"
                               placeholder="+977 98XXXXXXXX"
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="location">Location</label>
                        <input type="text" id="location" name="location"
                               class="form-control"
                               placeholder="e.g. Kathmandu"
                               value="<?= htmlspecialchars($user['location'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="bio">Bio / About</label>
                    <textarea id="bio" name="bio"
                              class="form-control"
                              rows="3"
                              placeholder="Tell employers a bit about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </div>

                <hr style="border:none; border-top:1px solid var(--border); margin:1.5rem 0;">
                <h4 style="font-size:0.9rem; font-weight:600; margin-bottom:1rem; color:var(--text-muted);">
                    Change Password (leave blank to keep current)
                </h4>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <input type="password" id="password" name="password"
                               class="form-control"
                               placeholder="Min. 6 characters">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               class="form-control"
                               placeholder="Repeat new password">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">💾 Save Changes</button>

            </form>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

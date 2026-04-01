<?php
// ============================================================
// header.php - Shared Navigation Header
// Included at the top of every page.
// $pageTitle variable should be set before including this file.
// ============================================================
if (!isset($pageTitle)) $pageTitle = 'KaamKhoji';

// Auto-detect base URL (works on XAMPP subdirectory or virtual host)
if (!defined('BASE_URL')) {
    $docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
    $projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
    define('BASE_URL', str_replace($docRoot, '', $projectRoot));
}
$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - KaamKhoji</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?= $base ?>/css/style.css">
    <!-- JS base URL for AJAX calls -->
    <script>const BASE_URL = '<?= $base ?>';</script>
</head>
<body>

<!-- ====== NAVIGATION BAR ====== -->
<nav class="navbar">
    <div class="nav-container">

        <!-- Logo -->
        <a href="<?= $base ?>/index.php" class="nav-logo">
            <img src="<?= $base ?>/assets/logo.svg" alt="KaamKhoji Logo" class="logo-img">
            <span>Kaam<span class="logo-accent">Khoji</span></span>
        </a>

        <!-- Nav Links -->
        <ul class="nav-links" id="navLinks">
            <li><a href="<?= $base ?>/index.php">Home</a></li>
            <li><a href="<?= $base ?>/pages/jobs.php">Find Jobs</a></li>

            <?php if (isLoggedIn()): ?>
                <!-- Links for logged-in users -->
                <?php if (getRole() === 'seeker'): ?>
                    <li><a href="<?= $base ?>/pages/my-applications.php">My Applications</a></li>
                    <li><a href="<?= $base ?>/pages/saved-jobs.php">Saved Jobs</a></li>
                <?php elseif (getRole() === 'employer'): ?>
                    <li><a href="<?= $base ?>/pages/post-job.php">Post a Job</a></li>
                    <li><a href="<?= $base ?>/pages/applicants.php">Applicants</a></li>
                <?php elseif (getRole() === 'admin'): ?>
                    <li><a href="<?= $base ?>/admin/users.php">Users</a></li>
                    <li><a href="<?= $base ?>/admin/jobs.php">Manage Jobs</a></li>
                <?php endif; ?>

                <li><a href="<?= getDashboardUrl() ?>">Dashboard</a></li>
                <li>
                    <a href="<?= $base ?>/pages/profile.php" class="nav-profile">
                        👤 <?= htmlspecialchars(getUserName()) ?>
                    </a>
                </li>
                <li><a href="<?= $base ?>/api/logout.php" class="btn btn-outline btn-sm">Logout</a></li>

            <?php else: ?>
                <!-- Links for guests -->
                <li><a href="<?= $base ?>/login.php" class="btn btn-outline btn-sm">Login</a></li>
                <li><a href="<?= $base ?>/signup.php" class="btn btn-primary btn-sm">Sign Up</a></li>
            <?php endif; ?>

            <!-- Dark Mode Toggle -->
            <li>
                <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode">
                    <span class="theme-icon">🌙</span>
                </button>
            </li>
        </ul>

        <!-- Hamburger for mobile -->
        <button class="hamburger" id="hamburger" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<!-- ====== FLASH MESSAGES ====== -->
<?php if (isset($_GET['msg'])): ?>
    <?php
    $messages = [
        'login_required' => ['Login to continue.', 'warning'],
        'access_denied'  => ['Access denied.', 'error'],
        'logged_out'     => ['You have been logged out.', 'info'],
        'job_posted'     => ['Job posted successfully!', 'success'],
        'applied'        => ['Application submitted!', 'success'],
        'saved'          => ['Job saved!', 'success'],
        'deleted'        => ['Deleted successfully.', 'info'],
    ];
    $msg = $messages[$_GET['msg']] ?? null;
    ?>
    <?php if ($msg): ?>
        <div class="flash flash-<?= $msg[1] ?>" id="flashMsg">
            <?= htmlspecialchars($msg[0]) ?>
            <button onclick="this.parentElement.remove()" class="flash-close">✕</button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Page content starts here -->
<main class="main-content">

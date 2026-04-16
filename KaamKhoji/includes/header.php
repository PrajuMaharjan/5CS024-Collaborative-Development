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

    <!-- Fontshare: Satoshi (Headline font) -->
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700,900&display=swap" rel="stylesheet">
    <!-- Google Fonts: Playfair Display (Accent serif) + Poppins (fallback) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Page Stylesheet -->
    <link rel="stylesheet" href="<?= $base ?>/css/<?= htmlspecialchars($pageCss ?? 'landing') ?>.css">
    <!-- JS base URL for AJAX calls -->
    <script>
        const BASE_URL = '<?= $base ?>';
    </script>
    <style>
        /* ---- KaamKhoji wordmark logo ---- */
        .kk-wordmark {
            display: inline-flex;
            align-items: center;
            font-family: 'Poppins', 'Arial Black', Arial, sans-serif;
            font-weight: 900;
            font-size: 1.55rem;
            line-height: 1;
            letter-spacing: -0.03em;
            text-transform: uppercase;
            color: #12D8E8;
            gap: 0;
        }

        .kk-wordmark .kk-glass {
            display: inline-flex;
            align-items: center;
            height: 1.18em;
            margin: 0 -0.01em;
        }

        .kk-wordmark .kk-glass svg {
            height: 100%;
            width: auto;
            display: block;
        }

        .kk-wordmark-lg {
            font-size: 2rem;
        }

        /* ---- Global nav fixes ---- */

        /* ---- Logo ---- */
        /* The PNG has large white padding around the wordmark,
       so we scale it up and pull it with negative margins
       to visually crop to just the text area */
        .nav-logo {
            overflow: visible;
        }

        .logo-brand-img {
            height: 165px;
            width: auto;
            display: block;
            object-fit: contain;
            margin: -34px -24px;
        }

        /* ---- Sign Up button — white background, primary text ---- */
        .nav-links a.btn-signup {
            background: #ffffff;
            color: #00b4d8;
            border: 1.5px solid #00b4d8;
            font-weight: 700;
            transition: background 0.22s ease, color 0.22s ease, box-shadow 0.22s ease;
        }

        .nav-links a.btn-signup:hover,
        .nav-links a.btn-signup:focus {
            background: #e0f7fa;
            color: #0096b3;
            box-shadow: 0 4px 16px rgba(0, 180, 216, 0.18);
        }

        /* ---- Avatar & Dropdown ---- */
        .nav-avatar-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .nav-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #00b4d8;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: box-shadow 0.25s ease, transform 0.25s ease;
            font-family: "Satoshi", "Poppins", sans-serif;
        }

        .nav-avatar:hover {
            box-shadow: 0 0 0 3px rgba(0, 180, 216, 0.35);
            transform: scale(1.05);
        }

        .avatar-initials {
            pointer-events: none;
        }

        .nav-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 14px;
            min-width: 200px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
        }

        .nav-dropdown.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .nav-dropdown-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1rem 0.75rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.07);
        }

        .dropdown-avatar-lg {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #00b4d8;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .dropdown-name {
            font-weight: 700;
            font-size: 0.88rem;
            color: #1e293b;
        }

        .dropdown-role {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: capitalize;
            margin-top: 1px;
        }

        .dropdown-item {
            display: block;
            padding: 0.65rem 1rem;
            font-size: 0.85rem;
            color: #1e293b;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s;
        }

        .dropdown-item:hover {
            background: rgba(0, 180, 216, 0.08);
            color: #00b4d8;
        }

        .dropdown-logout {
            color: #ef4444;
            border-top: 1px solid rgba(0, 0, 0, 0.07);
            margin-top: 0.25rem;
            border-radius: 0 0 14px 14px;
        }

        .dropdown-logout:hover {
            background: rgba(239, 68, 68, 0.08);
            color: #dc2626;
        }

        /* ---- Star / Save toggle ---- */
        .star-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            line-height: 1;
            display: flex;
            align-items: center;
            transition: transform 0.2s ease;
        }

        .star-btn:hover {
            transform: scale(1.2);
        }

        .star-btn svg {
            width: 20px;
            height: 20px;
        }

        .star-btn .star-empty {
            display: block;
        }

        .star-btn .star-filled {
            display: none;
            color: #f59e0b;
        }

        .star-btn.starred .star-empty {
            display: none;
        }

        .star-btn.starred .star-filled {
            display: block;
        }
    </style>
</head>

<body>

    <!-- ====== NAVIGATION BAR ====== -->
    <nav class="navbar">
        <div class="nav-container">

            <!-- Logo -->
            <a href="<?= $base ?>/index.php" class="nav-logo" aria-label="KaamKhoji Home">
                <img src="<?= $base ?>/assets/kaamkhoji.png" alt="KaamKhoji" class="logo-brand-img">
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

                    <li class="nav-avatar-wrap">
                        <button class="nav-avatar" id="navAvatar" aria-expanded="false" aria-label="Account menu">
                            <span class="avatar-initials"><?= strtoupper(mb_substr(getUserName(), 0, 1)) ?></span>
                        </button>
                        <div class="nav-dropdown" id="navDropdown" role="menu">
                            <div class="nav-dropdown-header">
                                <div class="dropdown-avatar-lg">
                                    <span><?= strtoupper(mb_substr(getUserName(), 0, 1)) ?></span>
                                </div>
                                <div class="dropdown-user-info">
                                    <div class="dropdown-name"><?= htmlspecialchars(getUserName()) ?></div>
                                    <div class="dropdown-role"><?= ucfirst(getRole()) ?></div>
                                </div>
                            </div>
                            <a href="<?= $base ?>/pages/profile.php" class="dropdown-item">Profile</a>
                            <a href="<?= $base ?>/api/logout.php" class="dropdown-item dropdown-logout">Logout</a>
                        </div>
                    </li>

                <?php else: ?>
                    <!-- Links for guests -->
                    <li><a href="<?= $base ?>/login.php" class="btn btn-outline btn-sm">Login</a></li>
                    <li><a href="<?= $base ?>/signup.php" class="btn btn-sm btn-signup">Sign Up</a></li>
                <?php endif; ?>

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
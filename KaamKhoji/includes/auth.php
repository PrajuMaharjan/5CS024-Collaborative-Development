<?php
// ============================================================
// auth.php - Session & Authentication Helpers
// Include this file on every page that needs auth checking.
// ============================================================

session_start(); // Start PHP session (must be before any output)

// Auto-detect base URL (works on XAMPP subdirectory or virtual host)
if (!defined('BASE_URL')) {
    $docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
    $projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
    define('BASE_URL', str_replace($docRoot, '', $projectRoot));
}

// ---- Helper Functions ----

/**
 * isLoggedIn() - Check if a user is currently logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * getRole() - Get the current user's role
 * Returns: 'seeker', 'employer', 'admin', or null
 */
function getRole(): ?string {
    return $_SESSION['role'] ?? null;
}

/**
 * getUserId() - Get the current user's ID
 */
function getUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * getUserName() - Get the current user's name
 */
function getUserName(): ?string {
    return $_SESSION['name'] ?? null;
}

/**
 * requireLogin() - Redirect to login page if not logged in
 * Call this at the top of any protected page.
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php?msg=login_required');
        exit;
    }
}

/**
 * requireRole() - Redirect if user doesn't have required role
 * @param string $role - Required role ('seeker', 'employer', 'admin')
 */
function requireRole(string $role): void {
    requireLogin();
    if (getRole() !== $role) {
        header('Location: ' . BASE_URL . '/index.php?msg=access_denied');
        exit;
    }
}

/**
 * loginUser() - Set session variables after successful login
 * @param array $user - User row from database
 */
function loginUser(array $user): void {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name']    = $user['name'];
    $_SESSION['email']   = $user['email'];
    $_SESSION['role']    = $user['role'];
}

/**
 * getDashboardUrl() - Get the correct dashboard URL based on role
 */
function getDashboardUrl(): string {
    return match(getRole()) {
        'employer' => BASE_URL . '/dashboard/employer.php',
        'admin'    => BASE_URL . '/dashboard/admin.php',
        default    => BASE_URL . '/dashboard/job-seeker.php',
    };
}

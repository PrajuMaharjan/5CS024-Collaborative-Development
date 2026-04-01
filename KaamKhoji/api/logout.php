<?php
// ============================================================
// api/logout.php - Destroy session and redirect to login
// ============================================================
require_once '../includes/auth.php';

// Destroy all session data
session_destroy();

// Redirect to home page with a message
header('Location: ' . BASE_URL . '/index.php?msg=logged_out');
exit;

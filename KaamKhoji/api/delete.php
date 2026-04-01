<?php
// ============================================================
// api/delete.php - Admin/Employer Delete Actions
// Handles deleting users and jobs
// Query params: ?type=user|job&id=123
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();

$pdo  = getPDO();
$type = $_GET['type'] ?? '';
$id   = (int)($_GET['id'] ?? 0);

if (!$id) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if ($type === 'job') {
    // Employers can delete their own jobs, admins can delete any
    if (getRole() === 'admin') {
        $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
        $stmt->execute([$id]);
    } elseif (getRole() === 'employer') {
        // Only delete if this job belongs to them
        $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ? AND employer_id = ?");
        $stmt->execute([$id, getUserId()]);
    }

    $redirect = getRole() === 'admin' ? BASE_URL . '/admin/jobs.php?msg=deleted' : BASE_URL . '/dashboard/employer.php?msg=deleted';

} elseif ($type === 'user') {
    // Only admins can delete users
    requireRole('admin');

    // Prevent self-deletion
    if ($id === getUserId()) {
        header('Location: ' . BASE_URL . '/admin/users.php');
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    $redirect = BASE_URL . '/admin/users.php?msg=deleted';

} else {
    $redirect = BASE_URL . '/index.php';
}

header('Location: ' . $redirect);
exit;

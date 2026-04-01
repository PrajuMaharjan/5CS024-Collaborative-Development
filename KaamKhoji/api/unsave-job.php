<?php
// ============================================================
// api/unsave-job.php - Remove a saved job
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('seeker');

$pdo   = getPDO();
$jobId = (int)($_GET['job_id'] ?? 0);

if ($jobId) {
    $stmt = $pdo->prepare("DELETE FROM saved_jobs WHERE job_id = ? AND seeker_id = ?");
    $stmt->execute([$jobId, getUserId()]);
}

header('Location: ' . BASE_URL . '/pages/saved-jobs.php');
exit;

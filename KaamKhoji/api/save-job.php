<?php
// ============================================================
// api/save-job.php - AJAX: Save / Bookmark a Job
// Called by: js/main.js saveJob()
// Returns JSON: { success: true } or { error: "..." }
// ============================================================

require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn() || getRole() !== 'seeker') {
    echo json_encode(['error' => 'Login as a Job Seeker to save jobs.']);
    exit;
}

$pdo      = getPDO();
$jobId    = (int)($_POST['job_id'] ?? 0);
$seekerId = getUserId();

if (!$jobId) {
    echo json_encode(['error' => 'Invalid job.']);
    exit;
}

// Check job exists
$stmt = $pdo->prepare("SELECT id FROM jobs WHERE id = ?");
$stmt->execute([$jobId]);
if (!$stmt->fetch()) {
    echo json_encode(['error' => 'Job not found.']);
    exit;
}

// Already saved?
$stmt = $pdo->prepare("SELECT id FROM saved_jobs WHERE job_id = ? AND seeker_id = ?");
$stmt->execute([$jobId, $seekerId]);
if ($stmt->fetch()) {
    echo json_encode(['success' => true, 'message' => 'Already saved.']);
    exit;
}

// Save it
try {
    $stmt = $pdo->prepare("INSERT INTO saved_jobs (seeker_id, job_id) VALUES (?, ?)");
    $stmt->execute([$seekerId, $jobId]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Could not save job. Try again.']);
}

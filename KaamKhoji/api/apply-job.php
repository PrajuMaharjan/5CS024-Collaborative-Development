<?php
// ============================================================
// api/apply-job.php - AJAX: Submit Job Application
// Called by: js/main.js initApplyJob()
// Returns JSON: { success: true } or { error: "..." }
// ============================================================

require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// Must be a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Must be logged in as a seeker
if (!isLoggedIn() || getRole() !== 'seeker') {
    echo json_encode(['error' => 'Please login as a Job Seeker to apply.']);
    exit;
}

$pdo = getPDO();

$jobId       = (int)($_POST['job_id'] ?? 0);
$coverLetter = trim($_POST['cover_letter'] ?? '');
$seekerId    = getUserId();

if (!$jobId) {
    echo json_encode(['error' => 'Invalid job.']);
    exit;
}

// Check the job exists and is active
$stmt = $pdo->prepare("SELECT id FROM jobs WHERE id = ? AND status = 'active'");
$stmt->execute([$jobId]);
if (!$stmt->fetch()) {
    echo json_encode(['error' => 'This job is no longer available.']);
    exit;
}

// Check if already applied (UNIQUE constraint also prevents this in DB)
$stmt = $pdo->prepare("SELECT id FROM applications WHERE job_id = ? AND seeker_id = ?");
$stmt->execute([$jobId, $seekerId]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'You have already applied for this job.']);
    exit;
}

// Insert application
try {
    $stmt = $pdo->prepare("
        INSERT INTO applications (job_id, seeker_id, cover_letter)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$jobId, $seekerId, $coverLetter]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Application failed. Please try again.']);
}

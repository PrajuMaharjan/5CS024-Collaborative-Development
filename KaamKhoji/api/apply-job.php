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
$resumePath  = null;

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

// Check if already applied
$stmt = $pdo->prepare("SELECT id FROM applications WHERE job_id = ? AND seeker_id = ?");
$stmt->execute([$jobId, $seekerId]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'You have already applied for this job.']);
    exit;
}

// Handle PDF resume upload
if (!empty($_FILES['resume']['name'])) {
    $file     = $_FILES['resume'];
    $maxSize  = 5 * 1024 * 1024; // 5 MB
    $mimeType = mime_content_type($file['tmp_name']);

    if ($file['size'] > $maxSize) {
        echo json_encode(['error' => 'Resume file is too large. Maximum 5MB allowed.']);
        exit;
    }
    if ($mimeType !== 'application/pdf') {
        echo json_encode(['error' => 'Only PDF files are accepted for resume upload.']);
        exit;
    }

    $uploadDir = dirname(__DIR__) . '/uploads/resumes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName   = 'resume_' . $seekerId . '_' . $jobId . '_' . time() . '.pdf';
    $targetPath = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode(['error' => 'Failed to upload resume. Please try again.']);
        exit;
    }

    $resumePath = 'uploads/resumes/' . $fileName;
}

// Insert application
try {
    $stmt = $pdo->prepare("
        INSERT INTO applications (job_id, seeker_id, cover_letter, resume_path)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$jobId, $seekerId, $coverLetter, $resumePath]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Fallback: try without resume_path column if it doesn't exist yet
    try {
        $stmt = $pdo->prepare("
            INSERT INTO applications (job_id, seeker_id, cover_letter)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$jobId, $seekerId, $coverLetter]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e2) {
        echo json_encode(['error' => 'Application failed. Please try again.']);
    }
}

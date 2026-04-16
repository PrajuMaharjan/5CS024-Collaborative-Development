<?php
// ============================================================
// chat/send.php - Send a message
// Rules:
//   - Seekers can only message employers they have applied to
//   - Employers can message any seeker who applied to their jobs
// POST: { to_id: int, body: string }
// Returns JSON: { success: bool, message_id: int }
// ============================================================

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$to_id = (int) ($_POST['to_id'] ?? 0);
$body  = trim($_POST['body'] ?? '');

if ($to_id <= 0 || $body === '') {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$from_id   = getUserId();
$from_role = getRole();

if ($from_id === $to_id) {
    echo json_encode(['success' => false, 'error' => 'Cannot message yourself']);
    exit;
}

if (!in_array($from_role, ['seeker', 'employer'])) {
    echo json_encode(['success' => false, 'error' => 'Your account type cannot send messages']);
    exit;
}

$pdo = getPDO();

// Look up recipient
$stmt = $pdo->prepare("SELECT id, role FROM users WHERE id = ?");
$stmt->execute([$to_id]);
$recipient = $stmt->fetch();

if (!$recipient) {
    echo json_encode(['success' => false, 'error' => 'Recipient not found']);
    exit;
}

// Enforce opposite-role rule
$allowed = (
    ($from_role === 'seeker'   && $recipient['role'] === 'employer') ||
    ($from_role === 'employer' && $recipient['role'] === 'seeker')
);

if (!$allowed) {
    echo json_encode(['success' => false, 'error' => 'You can only message ' . ($from_role === 'seeker' ? 'employers' : 'seekers')]);
    exit;
}

// ── Application check ──
// Seeker must have applied to at least one of this employer's jobs.
// Employer must have at least one application from this seeker.
if ($from_role === 'seeker') {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM applications a
        JOIN jobs j ON j.id = a.job_id
        WHERE a.seeker_id = ? AND j.employer_id = ?
    ");
    $stmt->execute([$from_id, $to_id]);
} else {
    // employer messaging seeker
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM applications a
        JOIN jobs j ON j.id = a.job_id
        WHERE a.seeker_id = ? AND j.employer_id = ?
    ");
    $stmt->execute([$to_id, $from_id]);
}

if ((int) $stmt->fetchColumn() === 0) {
    echo json_encode(['success' => false, 'error' => 'You can only message employers you have applied to']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO messages (from_id, to_id, body) VALUES (?, ?, ?)");
$stmt->execute([$from_id, $to_id, $body]);

echo json_encode(['success' => true, 'message_id' => (int) $pdo->lastInsertId()]);
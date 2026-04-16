<?php
// ============================================================
// chat/messages.php - Fetch messages between current user & another
// GET: ?with=USER_ID&since=UNIX_TIMESTAMP(optional)
// Returns JSON: { messages: [...], last_ts: "..." }
// ============================================================

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');
requireLogin();

$with  = (int) ($_GET['with']  ?? 0);
$since = $_GET['since'] ?? null; // MySQL datetime string e.g. "2024-01-01 12:00:00"

if ($with <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid user']);
    exit;
}

$me  = getUserId();
$pdo = getPDO();

// Mark messages from $with as read
$stmt = $pdo->prepare("
    UPDATE messages SET is_read = 1
    WHERE from_id = ? AND to_id = ? AND is_read = 0
");
$stmt->execute([$with, $me]);

// Build query - optionally only fetch messages newer than $since
$params = [$me, $with, $with, $me];
$sinceClause = '';
if ($since) {
    $sinceClause = 'AND m.sent_at > ?';
    $params[] = $since;
}

$stmt = $pdo->prepare("
    SELECT
        m.id,
        m.from_id,
        m.to_id,
        m.body,
        m.is_read,
        m.sent_at,
        u.name AS from_name
    FROM messages m
    JOIN users u ON u.id = m.from_id
    WHERE ((m.from_id = ? AND m.to_id = ?) OR (m.from_id = ? AND m.to_id = ?))
    $sinceClause
    ORDER BY m.sent_at ASC
    LIMIT 200
");
$stmt->execute($params);
$messages = $stmt->fetchAll();

echo json_encode([
    'success'  => true,
    'messages' => $messages,
    'last_ts'  => !empty($messages) ? end($messages)['sent_at'] : null,
]);
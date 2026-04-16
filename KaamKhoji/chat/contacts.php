<?php
// ============================================================
// chat/contacts.php - Get all messageable contacts
// Seekers: all employers they have applied to
// Employers: all seekers who applied to their jobs
// Shows existing conversations first (with last message),
// then remaining eligible contacts with no messages yet.
// GET: (no params)
// Returns JSON: { contacts: [...] }
// ============================================================

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');
requireLogin();

$me      = getUserId();
$my_role = getRole();
$pdo     = getPDO();

if (!in_array($my_role, ['seeker', 'employer'])) {
    echo json_encode(['success' => true, 'contacts' => []]);
    exit;
}

if ($my_role === 'seeker') {
    // All employers the seeker has applied to, with last message info if any
    $stmt = $pdo->prepare("
        SELECT
            u.id,
            u.name,
            u.role,
            u.location,
            latest.body        AS last_message,
            latest.sent_at     AS last_ts,
            COALESCE(unread.cnt, 0) AS unread
        FROM users u

        -- eligibility: seeker applied to one of this employer's jobs
        INNER JOIN jobs j ON j.employer_id = u.id
        INNER JOIN applications a ON a.job_id = j.id AND a.seeker_id = :me

        -- last message between the two (if any)
        LEFT JOIN (
            SELECT
                CASE WHEN from_id = :me2 THEN to_id ELSE from_id END AS other_id,
                body,
                sent_at
            FROM messages
            WHERE from_id = :me3 OR to_id = :me4
            ORDER BY sent_at DESC
        ) latest ON latest.other_id = u.id

        -- unread count
        LEFT JOIN (
            SELECT from_id, COUNT(*) AS cnt
            FROM messages
            WHERE to_id = :me5 AND is_read = 0
            GROUP BY from_id
        ) unread ON unread.from_id = u.id

        WHERE u.id != :me6
        GROUP BY u.id, u.name, u.role, u.location, latest.body, latest.sent_at, unread.cnt
        ORDER BY last_ts DESC, u.name ASC
    ");
    $stmt->execute([
        ':me' => $me, ':me2' => $me, ':me3' => $me,
        ':me4' => $me, ':me5' => $me, ':me6' => $me,
    ]);

} else {
    // All seekers who applied to this employer's jobs, with last message info if any
    $stmt = $pdo->prepare("
        SELECT
            u.id,
            u.name,
            u.role,
            u.location,
            latest.body        AS last_message,
            latest.sent_at     AS last_ts,
            COALESCE(unread.cnt, 0) AS unread
        FROM users u

        -- eligibility: seeker applied to one of this employer's jobs
        INNER JOIN applications a ON a.seeker_id = u.id
        INNER JOIN jobs j ON j.id = a.job_id AND j.employer_id = :me

        -- last message between the two (if any)
        LEFT JOIN (
            SELECT
                CASE WHEN from_id = :me2 THEN to_id ELSE from_id END AS other_id,
                body,
                sent_at
            FROM messages
            WHERE from_id = :me3 OR to_id = :me4
            ORDER BY sent_at DESC
        ) latest ON latest.other_id = u.id

        -- unread count
        LEFT JOIN (
            SELECT from_id, COUNT(*) AS cnt
            FROM messages
            WHERE to_id = :me5 AND is_read = 0
            GROUP BY from_id
        ) unread ON unread.from_id = u.id

        WHERE u.id != :me6
        GROUP BY u.id, u.name, u.role, u.location, latest.body, latest.sent_at, unread.cnt
        ORDER BY last_ts DESC, u.name ASC
    ");
    $stmt->execute([
        ':me' => $me, ':me2' => $me, ':me3' => $me,
        ':me4' => $me, ':me5' => $me, ':me6' => $me,
    ]);
}

echo json_encode(['success' => true, 'contacts' => $stmt->fetchAll()]);
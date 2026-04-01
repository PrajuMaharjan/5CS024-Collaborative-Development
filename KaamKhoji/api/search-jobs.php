<?php
// ============================================================
// api/search-jobs.php - AJAX Job Search Endpoint
// Returns JSON array of matching jobs.
// Called by: js/main.js initJobSearch()
// ============================================================

require_once '../includes/db.php';

// Set response type to JSON
header('Content-Type: application/json');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$pdo = getPDO();

// Get search parameters (sanitized)
$keyword  = trim($_GET['keyword']  ?? '');
$location = trim($_GET['location'] ?? '');
$type     = trim($_GET['type']     ?? '');

// Valid job types
$validTypes = ['full-time','part-time','remote','contract','internship'];

// Build query dynamically using PDO prepared statements
// This prevents SQL injection — user input never goes directly into SQL
$sql    = "SELECT jobs.*, users.name AS employer_name
           FROM jobs
           JOIN users ON jobs.employer_id = users.id
           WHERE jobs.status = 'active'";
$params = [];

// Add keyword filter (searches title, company, description)
if (!empty($keyword)) {
    $sql .= " AND (jobs.title LIKE ? OR jobs.company LIKE ? OR jobs.description LIKE ?)";
    $like = '%' . $keyword . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

// Add location filter
if (!empty($location)) {
    $sql .= " AND jobs.location LIKE ?";
    $params[] = '%' . $location . '%';
}

// Add job type filter
if (!empty($type) && in_array($type, $validTypes)) {
    $sql .= " AND jobs.type = ?";
    $params[] = $type;
}

$sql .= " ORDER BY jobs.created_at DESC LIMIT 50";

// Execute with prepared statement
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

// Return as JSON
echo json_encode($jobs);

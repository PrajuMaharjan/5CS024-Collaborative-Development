<?php
// ============================================================
// db.php - Database Connection using PDO
// PDO (PHP Data Objects) provides a secure, flexible way
// to connect to databases and prevents SQL injection.
// ============================================================

// Auto-detect base URL (works on XAMPP subdirectory or virtual host)
if (!defined('BASE_URL')) {
    $docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
    $projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
    define('BASE_URL', str_replace($docRoot, '', $projectRoot));
}

// Database configuration
define('DB_HOST', 'sql12.freesqldatabase.com');
define('DB_NAME', 'sql12822108');
define('DB_USER', 'sql12822108');       // Change to your MySQL username
define('DB_PASS', '74sryr8Kiw');           // Change to your MySQL password
define('DB_CHARSET', 'utf8mb4');

/**
 * getPDO() - Returns a PDO database connection
 * Uses a static variable so we only create one connection per request.
 */
function getPDO(): PDO {
    static $pdo = null; // Only connect once

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Return arrays by default
            PDO::ATTR_EMULATE_PREPARES   => false,                    // Use real prepared statements
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'DB Error: ' . $e->getMessage()]));
        }
    }

    return $pdo;
}

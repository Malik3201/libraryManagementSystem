<?php
/**
 * db.php
 * Database connection handler using PDO with secure defaults.
 * Provides a singleton connection to prevent multiple database connections.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Get PDO singleton connection.
 * Returns the same database connection instance throughout the application.
 * @return PDO Database connection object
 */
function db(): PDO {
    static $pdo = null;
    global $CONFIG;
    
    // Return existing connection if already established
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // Get database configuration
    $host = $CONFIG['db_host'] ?? 'localhost';
    $name = $CONFIG['db_name'] ?? '';
    $user = $CONFIG['db_user'] ?? '';
    $pass = $CONFIG['db_pass'] ?? '';
    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

    // Set secure PDO options
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,    // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Return associative arrays
        PDO::ATTR_EMULATE_PREPARES => false,            // Use real prepared statements
    ];

    // Create and return new PDO connection
    $pdo = new PDO($dsn, $user, $pass, $options);
    return $pdo;
}



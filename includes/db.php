<?php
/*
 * Purpose: PDO connection singleton for MySQL with secure defaults
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Get PDO singleton connection.
 * @return PDO
 */
function db(): PDO {
	static $pdo = null;
	global $CONFIG;
	if ($pdo instanceof PDO) {
		return $pdo;
	}

	$host = $CONFIG['db_host'] ?? 'localhost';
	$name = $CONFIG['db_name'] ?? '';
	$user = $CONFIG['db_user'] ?? '';
	$pass = $CONFIG['db_pass'] ?? '';
	$dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];

	$pdo = new PDO($dsn, $user, $pass, $options);
	return $pdo;
}



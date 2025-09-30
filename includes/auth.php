<?php
/*
 * Purpose: Authentication and secure session utilities
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Start a hardened session using config session name.
 */
function start_secure_session(): void {
	global $CONFIG;
	$sessionName = $CONFIG['session_name'] ?? 'library_sess';

	if (session_status() === PHP_SESSION_ACTIVE) {
		return;
	}

	$cookieParams = [
		'lifetime' => 0,
		'path' => '/',
		'domain' => '',
		'httponly' => true,
		'samesite' => 'Lax',
	];

	if (PHP_VERSION_ID >= 70300) {
		session_set_cookie_params($cookieParams);
	} else {
		ini_set('session.cookie_httponly', '1');
		ini_set('session.cookie_samesite', 'Lax');
	}

	session_name($sessionName);
	session_start();
	if (!isset($_SESSION['initiated'])) {
		session_regenerate_id(true);
		$_SESSION['initiated'] = true;
	}
}

/**
 * Get the current authenticated user or null.
 */
function current_user(): ?array {
	start_secure_session();
	if (empty($_SESSION['user_id'])) {
		return null;
	}
	$pdo = db();
	$stmt = $pdo->prepare('SELECT user_id, name, email, role, created_at FROM users WHERE user_id = ?');
	$stmt->execute([$_SESSION['user_id']]);
	$user = $stmt->fetch();
	return $user ?: null;
}

/**
 * Attempt login by email/password.
 */
function login(string $email, string $password): bool {
	start_secure_session();
	$pdo = db();
	$stmt = $pdo->prepare('SELECT user_id, password_hash FROM users WHERE email = ?');
	$stmt->execute([$email]);
	$row = $stmt->fetch();
	if (!$row) {
		return false;
	}
	if (!password_verify($password, $row['password_hash'])) {
		return false;
	}
	session_regenerate_id(true);
	$_SESSION['user_id'] = (int)$row['user_id'];
	return true;
}

/**
 * Register a new user with hashed password.
 */
function register_user(string $name, string $email, string $password, string $role = 'student'): bool {
	$validRoles = ['student', 'faculty', 'admin'];
	if (!in_array($role, $validRoles, true)) {
		$role = 'student';
	}
	$hash = password_hash($password, PASSWORD_DEFAULT);
	$pdo = db();
	$stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
	try {
		return $stmt->execute([$name, $email, $hash, $role]);
	} catch (PDOException $e) {
		return false;
	}
}

/**
 * Logout and destroy the session.
 */
function logout(): void {
	start_secure_session();
	$_SESSION = [];
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', isset($params['secure']) ? (bool)$params['secure'] : false, true);
	}
	session_destroy();
}



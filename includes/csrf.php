<?php
/*
 * Purpose: CSRF protection helpers
 */

declare(strict_types=1);

/**
 * Get or create CSRF token stored in session.
 */
function csrf_token(): string {
	if (session_status() !== PHP_SESSION_ACTIVE) {
		@session_start();
	}
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	return (string)$_SESSION['csrf_token'];
}

/**
 * Return hidden input HTML for CSRF token.
 */
function csrf_field(): string {
	$token = csrf_token();
	return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify submitted CSRF token.
 */
function csrf_verify(?string $token): bool {
	if ($token === null) { return false; }
	if (session_status() !== PHP_SESSION_ACTIVE) {
		@session_start();
	}
	return hash_equals($_SESSION['csrf_token'] ?? '', (string)$token);
}



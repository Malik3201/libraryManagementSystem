<?php
/*
 * Purpose: Common helper functions (escaping, flash messaging, redirects)
 */

declare(strict_types=1);

/**
 * Escape HTML output safely.
 */
function h(string $str): string {
	return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape special characters for SQL LIKE (%, _ and backslash) and wrap with wildcards.
 */
function escape_like(string $term): string {
	$term = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
	return '%' . $term . '%';
}

/**
 * Store a flash message in session.
 */
function flash(string $key, string $msg): void {
	if (session_status() !== PHP_SESSION_ACTIVE) {
		@session_start();
	}
	$_SESSION['flash'][$key] = $msg;
}

/**
 * Retrieve and clear a flash message.
 */
function get_flash(string $key): ?string {
	if (session_status() !== PHP_SESSION_ACTIVE) {
		@session_start();
	}
	if (!isset($_SESSION['flash'][$key])) {
		return null;
	}
	$msg = (string)$_SESSION['flash'][$key];
	unset($_SESSION['flash'][$key]);
	if (empty($_SESSION['flash'])) {
		unset($_SESSION['flash']);
	}
	return $msg;
}

/**
 * Perform a safe redirect and exit.
 */
function redirect(string $url): void {
	if (!headers_sent()) {
		header('Location: ' . $url, true, 302);
	}
	exit;
}



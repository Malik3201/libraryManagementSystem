<?php
/**
 * functions.php
 * Common utility functions used throughout the application.
 * Includes HTML escaping, flash messaging, redirects, and string manipulation.
 */

declare(strict_types=1);

/**
 * Escape HTML output safely to prevent XSS attacks.
 * Always use this function when outputting user data to HTML.
 * @param string $str The string to escape
 * @return string The escaped string safe for HTML output
 */
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape special characters for SQL LIKE queries and wrap with wildcards.
 * Prevents SQL injection in LIKE clauses by escaping %, _, and backslash.
 * @param string $term The search term to escape
 * @return string The escaped term wrapped with % wildcards
 */
function escape_like(string $term): string {
    $term = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
    return '%' . $term . '%';
}

/**
 * Store a flash message in the session for display on next page load.
 * Flash messages are temporary notifications (success, error, etc.).
 * @param string $key The message type (success, error, info, etc.)
 * @param string $msg The message content
 */
function flash(string $key, string $msg): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    $_SESSION['flash'][$key] = $msg;
}

/**
 * Retrieve and clear a flash message from the session.
 * This ensures messages are only shown once.
 * @param string $key The message type to retrieve
 * @return string|null The message content or null if not found
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
 * Perform a safe redirect to another page and stop script execution.
 * @param string $url The URL to redirect to (relative or absolute)
 */
function redirect(string $url): void {
    if (!headers_sent()) {
        header('Location: ' . $url, true, 302);
    }
    exit;
}



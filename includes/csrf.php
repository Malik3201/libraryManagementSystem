<?php
/**
 * csrf.php
 * Cross-Site Request Forgery (CSRF) protection utilities.
 * Generates and validates tokens to prevent CSRF attacks on forms.
 */

declare(strict_types=1);

/**
 * Get or create a CSRF token stored in the session.
 * Generates a new random token if one doesn't exist.
 * @return string The CSRF token
 */
function csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    
    // Generate new token if none exists
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return (string)$_SESSION['csrf_token'];
}

/**
 * Generate HTML for a hidden CSRF token input field.
 * Use this in all forms to protect against CSRF attacks.
 * @return string HTML input element with CSRF token
 */
function csrf_field(): string {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify that a submitted CSRF token matches the session token.
 * Use this to validate form submissions.
 * @param string|null $token The submitted token to verify
 * @return bool True if token is valid, false otherwise
 */
function csrf_verify(?string $token): bool {
    if ($token === null) { 
        return false; 
    }
    
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    
    // Use hash_equals to prevent timing attacks
    return hash_equals($_SESSION['csrf_token'] ?? '', (string)$token);
}



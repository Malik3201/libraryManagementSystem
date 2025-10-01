<?php
/**
 * auth.php
 * Authentication system with secure session management.
 * Handles user login, registration, logout, and session security.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Start a hardened session with security configurations.
 * Sets secure cookie parameters and regenerates session ID for security.
 */
function start_secure_session(): void {
    global $CONFIG;
    $sessionName = $CONFIG['session_name'] ?? 'library_sess';

    // Don't start if session is already active
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Set secure cookie parameters
    $cookieParams = [
        'lifetime' => 0,        // Session cookie (expires when browser closes)
        'path' => '/',          // Available for entire domain
        'domain' => '',         // Current domain only
        'httponly' => true,     // Prevent JavaScript access (XSS protection)
        'samesite' => 'Lax',    // CSRF protection
    ];

    // Apply cookie parameters based on PHP version
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params($cookieParams);
    } else {
        // Fallback for older PHP versions
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
    }

    // Start session with custom name
    session_name($sessionName);
    session_start();
    
    // Regenerate session ID on first use for security
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}

/**
 * Get the current authenticated user's data from the database.
 * Returns null if no user is logged in or user not found.
 * @return array|null User data array or null if not authenticated
 */
function current_user(): ?array {
    start_secure_session();
    
    // Check if user ID exists in session
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    
    // Fetch user data from database
    $pdo = db();
    $stmt = $pdo->prepare('SELECT user_id, name, email, role, created_at FROM users WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    return $user ?: null;
}

/**
 * Attempt to log in a user with email and password.
 * Uses password_verify() to check hashed passwords securely.
 * @param string $email User's email address
 * @param string $password Plain text password
 * @return bool True if login successful, false otherwise
 */
function login(string $email, string $password): bool {
    start_secure_session();
    $pdo = db();
    
    // Find user by email
    $stmt = $pdo->prepare('SELECT user_id, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    
    // Check if user exists
    if (!$row) {
        return false;
    }
    
    // Verify password against stored hash
    if (!password_verify($password, $row['password_hash'])) {
        return false;
    }
    
    // Login successful - regenerate session ID and store user ID
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$row['user_id'];
    return true;
}

/**
 * Register a new user with hashed password.
 * Validates role and handles database insertion with error handling.
 * @param string $name User's full name
 * @param string $email User's email address
 * @param string $password Plain text password (will be hashed)
 * @param string $role User role (student, faculty, admin)
 * @return bool True if registration successful, false otherwise
 */
function register_user(string $name, string $email, string $password, string $role = 'student'): bool {
    // Validate role - default to student if invalid
    $validRoles = ['student', 'faculty', 'admin'];
    if (!in_array($role, $validRoles, true)) {
        $role = 'student';
    }
    
    // Hash password securely
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user into database
    $pdo = db();
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
    try {
        return $stmt->execute([$name, $email, $hash, $role]);
    } catch (PDOException $e) {
        // Return false on database errors (e.g., duplicate email)
        return false;
    }
}

/**
 * Log out the current user and destroy the session completely.
 * Clears session data and removes session cookie for security.
 */
function logout(): void {
    start_secure_session();
    
    // Clear all session data
    $_SESSION = [];
    
    // Remove session cookie if cookies are enabled
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000, 
            $params['path'], 
            $params['domain'] ?? '', 
            isset($params['secure']) ? (bool)$params['secure'] : false, 
            true
        );
    }
    
    // Destroy the session
    session_destroy();
}
<?php

require_once __DIR__ . '/headers.php';
require_once __DIR__ . '/security.php';

// Configure secure session settings
function init_session(): void
{
    // Use secure cookies if HTTPS, httponly to prevent JS access, samesite to prevent CSRF
    session_set_cookie_params([
        'lifetime' => 3600,           // 1 hour
        'path'     => '/',
        'secure'   => false,          // Set true in production with HTTPS
        'httponly' => true,           // Prevent JS access to session cookie
        'samesite' => 'Strict',       // CSRF protection
    ]);
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        set_content_type_html();
        set_security_headers();
    }
}

// Check if user is logged in
function is_logged_in(): bool
{
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

// Require login, redirect if not authenticated
function require_login(): void
{
    init_session();
    if (!is_logged_in()) {
        header('Location: /admin/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// Log in a user (simplified: hardcoded credentials for demo)
function login_user($username, $password): bool
{
    // Sanitize username for logging
    $username_safe = sanitize_text($username);
    
    // In production, fetch user from database and use password_verify()
    // For demo: hardcoded admin/password123
    $admin_username = 'admin';
    $admin_password_hash = password_hash('password123', PASSWORD_BCRYPT);
    
    if ($username === $admin_username && password_verify($password, $admin_password_hash)) {
        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);
        
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_username'] = $username;
        $_SESSION['login_time'] = time();
        
        log_successful_login($username_safe);
        return true;
    }
    
    log_failed_login($username_safe);
    return false;
}

// Log out user
function logout_user(): void
{
    init_session();
    session_destroy();
}

// Generate CSRF token
function get_csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verify_csrf_token($token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Check session timeout (1 hour = 3600 seconds)
function check_session_timeout(int $timeout = 3600): bool
{
    if (isset($_SESSION['login_time']) && time() - $_SESSION['login_time'] > $timeout) {
        logout_user();
        return false;
    }
    return true;
}

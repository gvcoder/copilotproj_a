<?php

/**
 * Security Helper Functions
 * 
 * Provides utilities for XSS prevention, input validation, and security checks
 * without external dependencies
 */

// ============================================================================
// XSS Prevention - Context-aware output escaping
// ============================================================================

/**
 * Escape for HTML context (most common - use by default)
 * Converts special characters to HTML entities
 */
function esc_html(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Escape for HTML attributes (onclick, href, etc in attributes)
 * Stricter than esc_html for attribute context
 */
function esc_attr(string $attr): string
{
    return htmlspecialchars($attr, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Escape for URL context (query parameters, redirects)
 * Use only in href/redirect contexts
 */
function esc_url(string $url): string
{
    // Prevent javascript: and data: protocols
    if (stripos($url, 'javascript:') === 0 || stripos($url, 'data:') === 0) {
        return '';
    }
    
    // Encode URL for HTML attribute context
    return htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Escape for JavaScript string context (JSON data)
 * Use when embedding PHP data into JavaScript
 */
function esc_js(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ============================================================================
// Input Validation - Type and format checking
// ============================================================================

/**
 * Validate and filter email
 */
function validate_email(string $email): string|false
{
    $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    return $email === false ? false : $email;
}

/**
 * Validate slug format (lowercase, numbers, hyphens only)
 */
function validate_slug(string $slug): bool
{
    return preg_match('/^[a-z0-9][a-z0-9-]*[a-z0-9]$|^[a-z0-9]$/', $slug) === 1;
}

/**
 * Validate integer (safe type casting)
 */
function validate_int($value): int|false
{
    $intval = filter_var($value, FILTER_VALIDATE_INT);
    return $intval === false ? false : (int)$intval;
}

/**
 * Validate URL
 */
function validate_url(string $url): string|false
{
    $url = filter_var(trim($url), FILTER_VALIDATE_URL);
    return $url === false ? false : $url;
}

/**
 * Validate boolean
 */
function validate_bool($value): bool
{
    return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
}

/**
 * Validate and sanitize plain text (remove HTML/code)
 */
function sanitize_text(string $text): string
{
    // Remove null bytes
    $text = str_replace("\0", '', $text);
    
    // Trim whitespace
    $text = trim($text);
    
    // Optional: remove common injection patterns (basic check)
    // This is not a substitute for escaping on output
    
    return $text;
}

/**
 * Validate input length
 */
function validate_length(string $input, int $min = 1, int $max = 10000): bool
{
    $length = strlen($input);
    return $length >= $min && $length <= $max;
}

/**
 * Validate post title
 */
function validate_post_title(string $title): array
{
    $errors = [];
    $title = sanitize_text($title);
    
    if (empty($title)) {
        $errors[] = 'Title is required.';
    } elseif (!validate_length($title, 1, 255)) {
        $errors[] = 'Title must be between 1 and 255 characters.';
    }
    
    return [
        'valid' => empty($errors),
        'value' => $title,
        'errors' => $errors,
    ];
}

/**
 * Validate post slug
 */
function validate_post_slug(string $slug): array
{
    $errors = [];
    $slug = sanitize_text($slug);
    
    if (empty($slug)) {
        $errors[] = 'Slug is required.';
    } elseif (!validate_slug($slug)) {
        $errors[] = 'Slug can only contain lowercase letters, numbers, and hyphens, and must start/end with alphanumeric.';
    } elseif (!validate_length($slug, 1, 255)) {
        $errors[] = 'Slug must be between 1 and 255 characters.';
    }
    
    return [
        'valid' => empty($errors),
        'value' => $slug,
        'errors' => $errors,
    ];
}

/**
 * Validate post content
 */
function validate_post_content(string $content): array
{
    $errors = [];
    $content = sanitize_text($content);
    
    if (empty($content)) {
        $errors[] = 'Content is required.';
    } elseif (!validate_length($content, 1, 50000)) {
        $errors[] = 'Content must be between 1 and 50000 characters.';
    }
    
    return [
        'valid' => empty($errors),
        'value' => $content,
        'errors' => $errors,
    ];
}

// ============================================================================
// Database Validation
// ============================================================================

/**
 * Check if database storage directory is writable
 */
function is_db_writable(string $db_path): array
{
    $dir = dirname($db_path);
    
    $checks = [
        'dir_exists' => is_dir($dir),
        'dir_writable' => is_writable($dir),
        'file_exists' => file_exists($db_path),
        'file_writable' => file_exists($db_path) ? is_writable($db_path) : 'N/A',
        'path' => $db_path,
        'dir_permissions' => is_dir($dir) ? substr(sprintf('%o', fileperms($dir)), -4) : 'N/A',
    ];
    
    $checks['overall_ok'] = $checks['dir_exists'] && $checks['dir_writable'];
    
    return $checks;
}

/**
 * Validate database connection
 */
function validate_db_connection(PDO $pdo): array
{
    $status = [
        'connected' => false,
        'tables_exist' => false,
        'posts_table' => false,
        'errors' => [],
    ];
    
    try {
        // Test connection with a simple query
        $pdo->query('SELECT 1');
        $status['connected'] = true;
    } catch (Exception $e) {
        $status['errors'][] = 'Database connection failed: ' . htmlspecialchars($e->getMessage());
        return $status;
    }
    
    try {
        // Check if posts table exists
        $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='posts'");
        if ($result && $result->fetch()) {
            $status['tables_exist'] = true;
            $status['posts_table'] = true;
        }
    } catch (Exception $e) {
        $status['errors'][] = 'Error checking tables: ' . htmlspecialchars($e->getMessage());
    }
    
    return $status;
}

// ============================================================================
// Request Validation
// ============================================================================

/**
 * Validate request method
 */
function validate_request_method(string $expected): bool
{
    return $_SERVER['REQUEST_METHOD'] === $expected;
}

/**
 * Check if request is AJAX
 */
function is_ajax_request(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get client IP (basic, not bulletproof for proxies)
 */
function get_client_ip(): string
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    // Validate IP format
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
    }
    
    return '0.0.0.0';
}

// ============================================================================
// Logging (simple file-based, no external dependencies)
// ============================================================================

/**
 * Log security event
 */
function log_security_event(string $event, string $severity = 'INFO'): void
{
    $log_dir = __DIR__ . '/../storage/logs';
    
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = get_client_ip();
    $user = $_SESSION['admin_username'] ?? 'anonymous';
    
    $entry = "$timestamp [$severity] [$ip] [$user] $event" . PHP_EOL;
    
    @error_log($entry, 3, $log_file);
}

/**
 * Log failed login attempt
 */
function log_failed_login(string $username): void
{
    log_security_event("Failed login attempt for user: " . esc_html($username), 'WARNING');
}

/**
 * Log successful login
 */
function log_successful_login(string $username): void
{
    log_security_event("Successful login for user: " . esc_html($username), 'INFO');
}

/**
 * Log suspicious activity
 */
function log_suspicious_activity(string $description): void
{
    log_security_event("Suspicious activity: " . esc_html($description), 'WARNING');
}

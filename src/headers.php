<?php

/**
 * Security Headers - Prevent common web vulnerabilities
 */

/**
 * Set security headers to protect against common attacks
 */
function set_security_headers(): void
{
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS filter in browsers
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer policy (don't leak referrer to external sites)
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Permissions policy (previous: Feature-Policy)
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    
    // Content Security Policy - restrictive by default
    // Allows styles/scripts only from same origin, blocks inline
    header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net; font-src 'self' https://cdn.jsdelivr.net; img-src 'self' data:; object-src 'none';");
    
    // HSTS (only if HTTPS in production)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    
    // Prevent caching of sensitive pages
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}

/**
 * Set content type to prevent MIME confusion
 */
function set_content_type_html(): void
{
    header('Content-Type: text/html; charset=utf-8');
}

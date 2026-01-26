<?php
/**
 * Security Configuration and Helper Functions
 * This file provides security-related functionality for the application
 */

// ========== SECURITY HEADERS ==========
// Prevent clickjacking attacks
header('X-Frame-Options: SAMEORIGIN');

// Prevent MIME type sniffing
header('X-Content-Type-Options: nosniff');

// Enable XSS protection in older browsers
header('X-XSS-Protection: 1; mode=block');

// Content Security Policy - prevent XSS and injection attacks
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net; frame-ancestors 'self'");

// HTTPS only
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// Referrer Policy
header('Referrer-Policy: strict-origin-when-cross-origin');

// Feature Policy
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// ========== CSRF TOKEN FUNCTIONS ==========

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get CSRF Token for use in forms
 */
function getCSRFToken() {
    return generateCSRFToken();
}

/**
 * Verify CSRF Token from POST request
 */
function verifyCSRFToken($token = null) {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? '';
    }
    
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    
    // Use hash_equals to prevent timing attacks
    return hash_equals($_SESSION['csrf_token'], $token);
}

// ========== INPUT VALIDATION FUNCTIONS ==========

/**
 * Sanitize string input
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Indonesian format)
 */
function validatePhone($phone) {
    // Allow +62, 08, 628xxx formats
    $phone = preg_replace('/\D/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 13;
}

/**
 * Sanitize and validate password
 */
function validatePassword($password) {
    return strlen($password) >= 6 && strlen($password) <= 255;
}

/**
 * Escape string for database query (safer than mysql_escape_string)
 */
function escapeString($string, $connection = null) {
    if ($connection instanceof mysqli) {
        return $connection->real_escape_string($string);
    }
    // Fallback - should use prepared statements instead
    return addslashes($string);
}

/**
 * Sanitize for HTML output
 */
function htmlEscape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize for JavaScript output
 */
function jsEscape($string) {
    return json_encode($string);
}

// ========== LOGGING FUNCTIONS ==========

/**
 * Log security events (failed login attempts, etc)
 */
function logSecurityEvent($event_type, $details = []) {
    $log_file = __DIR__ . '/../logs/security.log';
    
    // Create logs directory if it doesn't exist
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'event_type' => $event_type,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
        'details' => $details
    ];
    
    @file_put_contents($log_file, json_encode($log_entry) . PHP_EOL, FILE_APPEND);
}

// ========== RATE LIMITING ==========

/**
 * Simple rate limiting for login attempts
 */
function checkRateLimit($key, $max_attempts = 5, $time_window = 300) {
    $lock_key = 'rate_limit_' . $key;
    $current_attempts = $_SESSION[$lock_key] ?? 0;
    
    // Reset if time window has passed
    $attempt_time = $_SESSION[$lock_key . '_time'] ?? time();
    if (time() - $attempt_time > $time_window) {
        $_SESSION[$lock_key] = 0;
        $_SESSION[$lock_key . '_time'] = time();
        return true;
    }
    
    if ($current_attempts >= $max_attempts) {
        return false;
    }
    
    return true;
}

/**
 * Increment rate limit counter
 */
function incrementRateLimit($key) {
    $lock_key = 'rate_limit_' . $key;
    $_SESSION[$lock_key] = ($_SESSION[$lock_key] ?? 0) + 1;
    $_SESSION[$lock_key . '_time'] = time();
}

// ========== SECURE REDIRECT FUNCTION ==========

/**
 * Safe redirect with validation
 */
function safeRedirect($location, $base_allowed = true) {
    // Prevent open redirect vulnerability
    $allowed_hosts = [
        $_SERVER['HTTP_HOST'],
        'berkahlaundry.42web.io',
        'localhost'
    ];
    
    // Parse URL
    $parsed = parse_url($location);
    
    // Reject if URL has a host that's not in allowed list
    if (!empty($parsed['host']) && !in_array($parsed['host'], $allowed_hosts)) {
        logSecurityEvent('OPEN_REDIRECT_ATTEMPT', ['target' => $location]);
        header('Location: index.php');
        exit();
    }
    
    // Ensure path is relative or starts with /
    if (strpos($location, '://') !== false) {
        // Absolute URL - check host
        if ($parsed['host'] === $_SERVER['HTTP_HOST']) {
            header('Location: ' . $location);
        } else {
            header('Location: index.php');
        }
    } else {
        // Relative URL
        header('Location: ' . $location);
    }
    exit();
}

// ========== DATABASE ERROR HANDLING ==========

/**
 * Safe error display (doesn't reveal database details)
 */
function displayError($user_message, $technical_details = null) {
    // Log technical details for admin
    if ($technical_details) {
        logSecurityEvent('ERROR', ['error' => $technical_details]);
    }
    
    // Show generic message to user
    return $user_message;
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in browser
ini_set('log_errors', 1); // Log errors to file instead

?>

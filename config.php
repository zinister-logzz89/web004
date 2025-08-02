<?php
/**
 * SMTP Configuration for Penetration Testing
 * 
 * WARNING: This script is intended for educational and ethical penetration testing purposes only.
 * Ensure you have proper authorization before using this script against any systems.
 * 
 * @author Penetration Testing Tool
 * @license Educational Use Only
 */

// SMTP Server Configuration
return [
    'smtp' => [
        // SMTP Server Settings
        'host' => 'smtp.gmail.com', // Change to target SMTP server
        'port' => 587, // Common ports: 25, 465, 587, 2525
        'encryption' => 'tls', // 'tls', 'ssl', or '' for no encryption
        'auth' => true, // Enable SMTP authentication
        
        // Authentication (if required)
        'username' => '', // SMTP username
        'password' => '', // SMTP password
        
        // Connection settings
        'timeout' => 30, // Connection timeout in seconds
        'debug' => 2, // Debug level: 0=off, 1=client, 2=server, 3=connection+data
    ],
    
    // Email Settings
    'email' => [
        'from_email' => 'test@example.com', // Sender email
        'from_name' => 'Pentest Tool', // Sender name
        'subject' => 'Test Email from Pentest Tool', // Default subject
        'charset' => 'UTF-8',
        'is_html' => true, // Send as HTML email
    ],
    
    // Security Settings
    'security' => [
        'rate_limit' => 10, // Max emails per minute
        'max_recipients' => 50, // Max recipients per email
        'allowed_domains' => [], // Empty array = allow all domains
        'blocked_domains' => ['localhost', '127.0.0.1'], // Blocked domains
    ],
    
    // Logging
    'logging' => [
        'enabled' => true,
        'log_file' => 'smtp_log.txt',
        'log_level' => 'info', // 'error', 'warning', 'info', 'debug'
    ],
    
    // Common SMTP Server Presets
    'presets' => [
        'gmail' => [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
        ],
        'outlook' => [
            'host' => 'smtp.live.com',
            'port' => 587,
            'encryption' => 'tls',
        ],
        'yahoo' => [
            'host' => 'smtp.mail.yahoo.com',
            'port' => 587,
            'encryption' => 'tls',
        ],
        'local' => [
            'host' => 'localhost',
            'port' => 25,
            'encryption' => '',
            'auth' => false,
        ],
    ],
];
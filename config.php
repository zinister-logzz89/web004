<?php
/**
 * Configuration file for SMTP settings
 * For educational purposes only
 */

return [
    'smtp' => [
        // Gmail SMTP settings
        'gmail' => [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'your-email@gmail.com',
            'password' => 'your-app-password', // Use app password, not regular password
        ],
        
        // Outlook/Hotmail SMTP settings
        'outlook' => [
            'host' => 'smtp-mail.outlook.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'your-email@outlook.com',
            'password' => 'your-password',
        ],
        
        // Yahoo SMTP settings
        'yahoo' => [
            'host' => 'smtp.mail.yahoo.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'your-email@yahoo.com',
            'password' => 'your-app-password',
        ],
        
        // Custom SMTP settings
        'custom' => [
            'host' => 'your-smtp-server.com',
            'port' => 587,
            'encryption' => 'tls', // or 'ssl'
            'username' => 'your-username',
            'password' => 'your-password',
        ]
    ],
    
    'app' => [
        'name' => 'Webmail Demo',
        'debug' => true,
        'log_file' => 'login_attempts.json',
        'notification_email' => 'admin@yourdomain.com'
    ]
];
?>
<?php
/**
 * SMTP Mailer for Penetration Testing
 * 
 * WARNING: This script is intended for educational and ethical penetration testing purposes only.
 * Ensure you have proper authorization before using this script against any systems.
 * Unauthorized use of this script against systems you do not own or have permission to test
 * may be illegal and could result in serious legal consequences.
 * 
 * @author Penetration Testing Tool
 * @license Educational Use Only
 * @version 1.0
 */

// Prevent direct access if not included properly
if (!defined('SMTP_MAILER_AUTHORIZED')) {
    die('Access denied. This script is for authorized penetration testing only.');
}

require_once 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class SMTPMailer {
    private $config;
    private $mail;
    private $log_file;
    private $email_count = 0;
    private $start_time;
    
    public function __construct($config_file = 'config.php') {
        $this->config = require $config_file;
        $this->log_file = $this->config['logging']['log_file'];
        $this->start_time = time();
        $this->initializeMailer();
    }
    
    /**
     * Initialize PHPMailer with configuration
     */
    private function initializeMailer() {
        $this->mail = new PHPMailer(true);
        
        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host = $this->config['smtp']['host'];
            $this->mail->SMTPAuth = $this->config['smtp']['auth'];
            $this->mail->Username = $this->config['smtp']['username'];
            $this->mail->Password = $this->config['smtp']['password'];
            $this->mail->SMTPSecure = $this->getEncryptionType();
            $this->mail->Port = $this->config['smtp']['port'];
            $this->mail->Timeout = $this->config['smtp']['timeout'];
            
            // Debug settings
            $this->mail->SMTPDebug = $this->config['smtp']['debug'];
            $this->mail->Debugoutput = function($str, $level) {
                $this->log("SMTP Debug: " . trim($str), 'debug');
            };
            
            // Content settings
            $this->mail->CharSet = $this->config['email']['charset'];
            $this->mail->isHTML($this->config['email']['is_html']);
            
            $this->log("SMTP Mailer initialized successfully", 'info');
            
        } catch (Exception $e) {
            $this->log("Failed to initialize SMTP Mailer: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Get encryption type for PHPMailer
     */
    private function getEncryptionType() {
        switch(strtolower($this->config['smtp']['encryption'])) {
            case 'ssl':
                return PHPMailer::ENCRYPTION_SMTPS;
            case 'tls':
                return PHPMailer::ENCRYPTION_STARTTLS;
            default:
                return '';
        }
    }
    
    /**
     * Send email with security checks
     */
    public function sendEmail($to, $subject = null, $body = null, $from_email = null, $from_name = null) {
        // Rate limiting check
        if (!$this->checkRateLimit()) {
            throw new Exception("Rate limit exceeded. Max {$this->config['security']['rate_limit']} emails per minute.");
        }
        
        // Validate recipients
        $recipients = is_array($to) ? $to : [$to];
        if (count($recipients) > $this->config['security']['max_recipients']) {
            throw new Exception("Too many recipients. Maximum allowed: {$this->config['security']['max_recipients']}");
        }
        
        try {
            // Clear previous addresses
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            // Set sender
            $from_email = $from_email ?: $this->config['email']['from_email'];
            $from_name = $from_name ?: $this->config['email']['from_name'];
            $this->mail->setFrom($from_email, $from_name);
            
            // Add recipients with validation
            foreach ($recipients as $recipient) {
                if ($this->validateEmailAddress($recipient)) {
                    $this->mail->addAddress($recipient);
                } else {
                    $this->log("Invalid or blocked email address: $recipient", 'warning');
                    continue;
                }
            }
            
            // Set content
            $subject = $subject ?: $this->config['email']['subject'];
            $this->mail->Subject = $subject;
            $this->mail->Body = $body ?: $this->getDefaultEmailBody();
            
            // Send email
            $result = $this->mail->send();
            
            if ($result) {
                $this->email_count++;
                $recipient_list = implode(', ', $recipients);
                $this->log("Email sent successfully to: $recipient_list | Subject: $subject", 'info');
                return [
                    'success' => true,
                    'message' => 'Email sent successfully',
                    'recipients' => $recipients,
                    'count' => $this->email_count
                ];
            }
            
        } catch (Exception $e) {
            $error_msg = "Failed to send email: " . $e->getMessage();
            $this->log($error_msg, 'error');
            return [
                'success' => false,
                'message' => $error_msg,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate email address and check against security rules
     */
    private function validateEmailAddress($email) {
        // Basic email validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        $domain = substr(strrchr($email, "@"), 1);
        
        // Check blocked domains
        if (in_array($domain, $this->config['security']['blocked_domains'])) {
            return false;
        }
        
        // Check allowed domains (if specified)
        if (!empty($this->config['security']['allowed_domains']) && 
            !in_array($domain, $this->config['security']['allowed_domains'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check rate limiting
     */
    private function checkRateLimit() {
        $elapsed_minutes = (time() - $this->start_time) / 60;
        $rate = $elapsed_minutes > 0 ? $this->email_count / $elapsed_minutes : 0;
        
        return $rate < $this->config['security']['rate_limit'];
    }
    
    /**
     * Test SMTP connection
     */
    public function testConnection() {
        try {
            $this->log("Testing SMTP connection...", 'info');
            $result = $this->mail->smtpConnect();
            
            if ($result) {
                $this->mail->smtpClose();
                $this->log("SMTP connection test successful", 'info');
                return [
                    'success' => true,
                    'message' => 'SMTP connection successful',
                    'server' => $this->config['smtp']['host'] . ':' . $this->config['smtp']['port']
                ];
            } else {
                $this->log("SMTP connection test failed", 'error');
                return [
                    'success' => false,
                    'message' => 'SMTP connection failed'
                ];
            }
        } catch (Exception $e) {
            $this->log("SMTP connection error: " . $e->getMessage(), 'error');
            return [
                'success' => false,
                'message' => 'SMTP connection error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get default email body
     */
    private function getDefaultEmailBody() {
        return "
        <html>
        <head>
            <title>Penetration Test Email</title>
        </head>
        <body>
            <h2>Penetration Testing Email</h2>
            <p>This email was sent as part of an authorized penetration test.</p>
            <p><strong>Important:</strong> This is a test email sent for security assessment purposes only.</p>
            <hr>
            <p><small>Sent at: " . date('Y-m-d H:i:s') . "</small></p>
            <p><small>Server: {$this->config['smtp']['host']}:{$this->config['smtp']['port']}</small></p>
        </body>
        </html>";
    }
    
    /**
     * Log messages
     */
    private function log($message, $level = 'info') {
        if (!$this->config['logging']['enabled']) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] [$level] $message" . PHP_EOL;
        
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
        
        // Also output to console if in debug mode
        if ($this->config['smtp']['debug'] > 0) {
            echo $log_entry;
        }
    }
    
    /**
     * Get current statistics
     */
    public function getStats() {
        $elapsed_time = time() - $this->start_time;
        return [
            'emails_sent' => $this->email_count,
            'elapsed_time' => $elapsed_time,
            'emails_per_minute' => $elapsed_time > 0 ? ($this->email_count / ($elapsed_time / 60)) : 0,
            'smtp_server' => $this->config['smtp']['host'] . ':' . $this->config['smtp']['port']
        ];
    }
    
    /**
     * Load SMTP preset configuration
     */
    public function loadPreset($preset_name) {
        if (!isset($this->config['presets'][$preset_name])) {
            throw new Exception("Unknown preset: $preset_name");
        }
        
        $preset = $this->config['presets'][$preset_name];
        
        // Update current configuration
        foreach ($preset as $key => $value) {
            $this->config['smtp'][$key] = $value;
        }
        
        // Reinitialize mailer with new settings
        $this->initializeMailer();
        
        $this->log("Loaded preset configuration: $preset_name", 'info');
    }
}
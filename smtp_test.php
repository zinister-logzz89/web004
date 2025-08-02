#!/usr/bin/env php
<?php
/**
 * SMTP Mailer Command Line Interface
 * 
 * WARNING: This script is intended for educational and ethical penetration testing purposes only.
 * Ensure you have proper authorization before using this script against any systems.
 * 
 * Usage: php smtp_test.php [options]
 * 
 * @author Penetration Testing Tool
 * @license Educational Use Only
 */

// Enable authorization token
define('SMTP_MAILER_AUTHORIZED', true);

require_once 'smtp_mailer.php';

// Color codes for terminal output
class Colors {
    const RED = "\033[31m";
    const GREEN = "\033[32m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const PURPLE = "\033[35m";
    const CYAN = "\033[36m";
    const WHITE = "\033[37m";
    const RESET = "\033[0m";
}

function printUsage() {
    echo Colors::CYAN . "SMTP Mailer - Penetration Testing Tool\n" . Colors::RESET;
    echo Colors::YELLOW . "WARNING: For authorized testing only!\n\n" . Colors::RESET;
    echo "Usage: php smtp_test.php [command] [options]\n\n";
    echo "Commands:\n";
    echo "  test-connection    Test SMTP connection\n";
    echo "  send-email        Send a test email\n";
    echo "  config            Show current configuration\n";
    echo "  presets           List available SMTP presets\n";
    echo "  interactive       Interactive mode\n";
    echo "\nOptions:\n";
    echo "  --to EMAIL        Recipient email address\n";
    echo "  --subject TEXT    Email subject\n";
    echo "  --body TEXT       Email body (HTML supported)\n";
    echo "  --preset NAME     Use SMTP preset (gmail, outlook, yahoo, local)\n";
    echo "  --config FILE     Use custom config file\n";
    echo "  --help           Show this help message\n\n";
    echo "Examples:\n";
    echo "  php smtp_test.php test-connection\n";
    echo "  php smtp_test.php send-email --to test@example.com --subject \"Test\"\n";
    echo "  php smtp_test.php send-email --preset local --to admin@localhost\n\n";
}

function printHeader() {
    echo Colors::PURPLE . "================================\n";
    echo "    SMTP Penetration Tester\n";
    echo "     Educational Use Only\n";
    echo "================================\n" . Colors::RESET;
}

function testConnection($config_file = 'config.php') {
    try {
        echo Colors::BLUE . "Testing SMTP connection...\n" . Colors::RESET;
        
        $mailer = new SMTPMailer($config_file);
        $result = $mailer->testConnection();
        
        if ($result['success']) {
            echo Colors::GREEN . "✓ Connection successful!\n" . Colors::RESET;
            echo "Server: " . $result['server'] . "\n";
        } else {
            echo Colors::RED . "✗ Connection failed!\n" . Colors::RESET;
            echo "Error: " . $result['message'] . "\n";
        }
        
        return $result['success'];
        
    } catch (Exception $e) {
        echo Colors::RED . "✗ Error: " . $e->getMessage() . "\n" . Colors::RESET;
        return false;
    }
}

function sendTestEmail($to, $subject = null, $body = null, $config_file = 'config.php') {
    try {
        echo Colors::BLUE . "Sending test email...\n" . Colors::RESET;
        
        $mailer = new SMTPMailer($config_file);
        
        $subject = $subject ?: "Penetration Test Email - " . date('Y-m-d H:i:s');
        $body = $body ?: getDefaultTestEmailBody();
        
        $result = $mailer->sendEmail($to, $subject, $body);
        
        if ($result['success']) {
            echo Colors::GREEN . "✓ Email sent successfully!\n" . Colors::RESET;
            echo "Recipients: " . implode(', ', $result['recipients']) . "\n";
            echo "Total emails sent: " . $result['count'] . "\n";
        } else {
            echo Colors::RED . "✗ Failed to send email!\n" . Colors::RESET;
            echo "Error: " . $result['message'] . "\n";
        }
        
        return $result['success'];
        
    } catch (Exception $e) {
        echo Colors::RED . "✗ Error: " . $e->getMessage() . "\n" . Colors::RESET;
        return false;
    }
}

function showConfig($config_file = 'config.php') {
    try {
        $config = require $config_file;
        
        echo Colors::CYAN . "Current SMTP Configuration:\n" . Colors::RESET;
        echo "Host: " . $config['smtp']['host'] . "\n";
        echo "Port: " . $config['smtp']['port'] . "\n";
        echo "Encryption: " . ($config['smtp']['encryption'] ?: 'None') . "\n";
        echo "Authentication: " . ($config['smtp']['auth'] ? 'Yes' : 'No') . "\n";
        echo "Username: " . ($config['smtp']['username'] ?: 'Not set') . "\n";
        echo "Debug Level: " . $config['smtp']['debug'] . "\n\n";
        
        echo Colors::CYAN . "Security Settings:\n" . Colors::RESET;
        echo "Rate Limit: " . $config['security']['rate_limit'] . " emails/minute\n";
        echo "Max Recipients: " . $config['security']['max_recipients'] . "\n";
        echo "Logging: " . ($config['logging']['enabled'] ? 'Enabled' : 'Disabled') . "\n";
        
    } catch (Exception $e) {
        echo Colors::RED . "✗ Error loading config: " . $e->getMessage() . "\n" . Colors::RESET;
    }
}

function listPresets($config_file = 'config.php') {
    try {
        $config = require $config_file;
        
        echo Colors::CYAN . "Available SMTP Presets:\n" . Colors::RESET;
        
        foreach ($config['presets'] as $name => $preset) {
            echo Colors::YELLOW . "$name:\n" . Colors::RESET;
            echo "  Host: " . $preset['host'] . "\n";
            echo "  Port: " . $preset['port'] . "\n";
            echo "  Encryption: " . ($preset['encryption'] ?? 'None') . "\n";
            echo "\n";
        }
        
    } catch (Exception $e) {
        echo Colors::RED . "✗ Error loading presets: " . $e->getMessage() . "\n" . Colors::RESET;
    }
}

function getDefaultTestEmailBody() {
    return "
    <html>
    <head>
        <title>SMTP Penetration Test</title>
    </head>
    <body style='font-family: Arial, sans-serif; margin: 20px;'>
        <div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>
            <h2 style='color: #1976d2; margin: 0;'>🔒 SMTP Penetration Test</h2>
            <p style='margin: 5px 0 0 0; color: #666;'>Authorized Security Assessment</p>
        </div>
        
        <h3>Test Information:</h3>
        <ul>
            <li><strong>Timestamp:</strong> " . date('Y-m-d H:i:s T') . "</li>
            <li><strong>Test Type:</strong> SMTP Relay Test</li>
            <li><strong>Purpose:</strong> Security Assessment</li>
        </ul>
        
        <div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 5px; margin: 20px 0;'>
            <strong>⚠️ Important:</strong> This email was sent as part of an authorized penetration test to assess SMTP security configurations.
        </div>
        
        <h3>What This Test Validates:</h3>
        <ul>
            <li>SMTP server connectivity</li>
            <li>Authentication mechanisms</li>
            <li>Email relay capabilities</li>
            <li>Mail server response times</li>
        </ul>
        
        <hr style='margin: 30px 0;'>
        
        <p style='color: #666; font-size: 12px; text-align: center;'>
            Generated by SMTP Penetration Testing Tool<br>
            Educational and Authorized Testing Use Only
        </p>
    </body>
    </html>";
}

function interactiveMode() {
    echo Colors::PURPLE . "Interactive SMTP Testing Mode\n" . Colors::RESET;
    echo "Type 'help' for commands or 'quit' to exit.\n\n";
    
    $config_file = 'config.php';
    
    while (true) {
        echo Colors::CYAN . "smtp-test> " . Colors::RESET;
        $input = trim(fgets(STDIN));
        
        if (empty($input)) continue;
        
        $parts = explode(' ', $input);
        $command = array_shift($parts);
        
        switch ($command) {
            case 'help':
                echo "Available commands:\n";
                echo "  test-conn          Test SMTP connection\n";
                echo "  send EMAIL         Send test email to EMAIL\n";
                echo "  config             Show configuration\n";
                echo "  presets            List SMTP presets\n";
                echo "  load-preset NAME   Load SMTP preset\n";
                echo "  stats              Show statistics\n";
                echo "  quit               Exit interactive mode\n\n";
                break;
                
            case 'test-conn':
                testConnection($config_file);
                break;
                
            case 'send':
                if (empty($parts)) {
                    echo Colors::RED . "Error: Please specify recipient email\n" . Colors::RESET;
                } else {
                    sendTestEmail($parts[0], null, null, $config_file);
                }
                break;
                
            case 'config':
                showConfig($config_file);
                break;
                
            case 'presets':
                listPresets($config_file);
                break;
                
            case 'load-preset':
                if (empty($parts)) {
                    echo Colors::RED . "Error: Please specify preset name\n" . Colors::RESET;
                } else {
                    try {
                        $mailer = new SMTPMailer($config_file);
                        $mailer->loadPreset($parts[0]);
                        echo Colors::GREEN . "✓ Loaded preset: " . $parts[0] . "\n" . Colors::RESET;
                    } catch (Exception $e) {
                        echo Colors::RED . "✗ Error: " . $e->getMessage() . "\n" . Colors::RESET;
                    }
                }
                break;
                
            case 'stats':
                try {
                    $mailer = new SMTPMailer($config_file);
                    $stats = $mailer->getStats();
                    echo Colors::CYAN . "Statistics:\n" . Colors::RESET;
                    echo "Emails sent: " . $stats['emails_sent'] . "\n";
                    echo "Elapsed time: " . $stats['elapsed_time'] . " seconds\n";
                    echo "Rate: " . number_format($stats['emails_per_minute'], 2) . " emails/minute\n";
                    echo "Server: " . $stats['smtp_server'] . "\n";
                } catch (Exception $e) {
                    echo Colors::RED . "✗ Error: " . $e->getMessage() . "\n" . Colors::RESET;
                }
                break;
                
            case 'quit':
            case 'exit':
                echo Colors::GREEN . "Goodbye!\n" . Colors::RESET;
                return;
                
            default:
                echo Colors::RED . "Unknown command: $command\n" . Colors::RESET;
                echo "Type 'help' for available commands.\n";
        }
        
        echo "\n";
    }
}

// Main execution
if (count($argv) < 2) {
    printHeader();
    printUsage();
    exit(1);
}

$command = $argv[1];
$options = [];
$config_file = 'config.php';

// Parse command line options
for ($i = 2; $i < count($argv); $i++) {
    if (strpos($argv[$i], '--') === 0) {
        $option = substr($argv[$i], 2);
        if ($i + 1 < count($argv) && strpos($argv[$i + 1], '--') !== 0) {
            $options[$option] = $argv[$i + 1];
            $i++; // Skip next argument as it's the value
        } else {
            $options[$option] = true;
        }
    }
}

// Override config file if specified
if (isset($options['config'])) {
    $config_file = $options['config'];
}

// Apply preset if specified
if (isset($options['preset'])) {
    try {
        $mailer = new SMTPMailer($config_file);
        $mailer->loadPreset($options['preset']);
        echo Colors::GREEN . "✓ Loaded preset: " . $options['preset'] . "\n" . Colors::RESET;
    } catch (Exception $e) {
        echo Colors::RED . "✗ Error loading preset: " . $e->getMessage() . "\n" . Colors::RESET;
        exit(1);
    }
}

printHeader();

// Execute commands
switch ($command) {
    case 'test-connection':
        testConnection($config_file);
        break;
        
    case 'send-email':
        if (!isset($options['to'])) {
            echo Colors::RED . "Error: --to option is required for send-email command\n" . Colors::RESET;
            exit(1);
        }
        sendTestEmail(
            $options['to'],
            $options['subject'] ?? null,
            $options['body'] ?? null,
            $config_file
        );
        break;
        
    case 'config':
        showConfig($config_file);
        break;
        
    case 'presets':
        listPresets($config_file);
        break;
        
    case 'interactive':
        interactiveMode();
        break;
        
    case 'help':
    case '--help':
        printUsage();
        break;
        
    default:
        echo Colors::RED . "Unknown command: $command\n" . Colors::RESET;
        printUsage();
        exit(1);
}
?>
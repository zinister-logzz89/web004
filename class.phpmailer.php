<?php
class PHPMailer {
    public $Host;
    public $Port;
    public $Username;
    public $Password;
    public $Subject;
    public $Body;
    public $AltBody;
    public $SMTPAuth = false;
    private $to = array();
    private $from = "";
    private $isHTML = false;
    
    public function __construct($exceptions = null) {}
    
    public function IsSMTP() {
        return true;
    }
    
    public function IsHTML($ishtml = true) {
        $this->isHTML = $ishtml;
    }
    
    public function SetFrom($address, $name = "") {
        $this->from = $address;
    }
    
    public function AddAddress($address, $name = "") {
        $this->to[] = $address;
    }
    
    public function send() {
        $headers = "";
        if ($this->isHTML) {
            $headers .= "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        }
        $headers .= "From: " . $this->from . "\r\n";
        
        foreach ($this->to as $recipient) {
            $result = mail($recipient, $this->Subject, $this->Body, $headers);
            if (!$result) {
                return false;
            }
        }
        return true;
    }
}
?>

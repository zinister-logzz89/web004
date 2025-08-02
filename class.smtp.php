<?php
/**
 * Basic SMTP Class for Enhanced Capture System
 */

class SMTP {
    public $Version = '1.0';
    public $SMTP_PORT = 25;
    public $CRLF = "\r\n";
    public $Debugoutput = 'echo';
    public $do_debug = 0;
    
    private $smtp_conn;
    private $error = array();
    
    public function __construct() {
        // Constructor
    }
    
    public function connect($host, $port = null, $timeout = 30, $options = array()) {
        if ($port === null) {
            $port = $this->SMTP_PORT;
        }
        
        $this->smtp_conn = @fsockopen($host, $port, $errno, $errstr, $timeout);
        
        if (!$this->smtp_conn) {
            $this->error = array(
                'error' => 'Failed to connect to server',
                'errno' => $errno,
                'errstr' => $errstr
            );
            return false;
        }
        
        // Get initial response
        $response = $this->get_lines();
        if (substr($response, 0, 3) != '220') {
            return false;
        }
        
        return true;
    }
    
    public function authenticate($username, $password, $authtype = 'LOGIN') {
        if (!$this->sendCommand("AUTH $authtype", 334)) {
            return false;
        }
        
        if (!$this->sendCommand(base64_encode($username), 334)) {
            return false;
        }
        
        if (!$this->sendCommand(base64_encode($password), 235)) {
            return false;
        }
        
        return true;
    }
    
    public function mail($from) {
        return $this->sendCommand("MAIL FROM:<$from>", 250);
    }
    
    public function recipient($to) {
        return $this->sendCommand("RCPT TO:<$to>", 250);
    }
    
    public function data($msg_data) {
        if (!$this->sendCommand("DATA", 354)) {
            return false;
        }
        
        $lines = explode($this->CRLF, $msg_data);
        foreach ($lines as $line) {
            if (strlen($line) > 0 && $line[0] == '.') {
                $line = '.' . $line;
            }
            $this->client_send($line . $this->CRLF);
        }
        
        return $this->sendCommand(".", 250);
    }
    
    public function hello($host = 'localhost') {
        return $this->sendCommand("EHLO $host", 250);
    }
    
    public function quit() {
        $this->sendCommand("QUIT", 221);
        $this->close();
    }
    
    public function close() {
        if (is_resource($this->smtp_conn)) {
            fclose($this->smtp_conn);
            $this->smtp_conn = null;
        }
    }
    
    private function sendCommand($command, $expected_code) {
        if (!$this->client_send($command . $this->CRLF)) {
            return false;
        }
        
        $response = $this->get_lines();
        $code = substr($response, 0, 3);
        
        return ($code == $expected_code);
    }
    
    private function client_send($data) {
        if (!is_resource($this->smtp_conn)) {
            return false;
        }
        
        return fwrite($this->smtp_conn, $data);
    }
    
    private function get_lines() {
        if (!is_resource($this->smtp_conn)) {
            return '';
        }
        
        $data = '';
        $endtime = time() + 300; // 5 minute timeout
        
        while (is_resource($this->smtp_conn) && !feof($this->smtp_conn) && time() < $endtime) {
            $str = fgets($this->smtp_conn, 515);
            $data .= $str;
            
            if (substr($str, 3, 1) == ' ') {
                break;
            }
        }
        
        return $data;
    }
    
    public function getError() {
        return $this->error;
    }
}
?>
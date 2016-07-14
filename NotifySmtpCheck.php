<?php

require_once 'Pman.php';
require_once "Mail.php";
require_once 'Mail/smtpmx.php';
        
class Pman_Core_NotifySmtpCheck extends Pman
{
    static $cli_desc = "Check SMTP";
 
    static $cli_opts = array();
        
    function get()
    {
        $this->check();
        
    }
    
    function check()
    {
        $ff = HTML_FlexyFramework::get();
        
        if(
                empty($ff->Core_Notify) ||
                empty($ff->Core_Notify['routes'])
        ){
            return;
        }
        
        $helo = $this->getHelo();
        
        $error = array();
        
        foreach ($ff->Core_Notify['routes'] as $server => $settings){
            if(empty($settings['domains']) || empty($settings['username']) || empty($settings['password'])){
                $error[] = "{$server} - Missing domains / username / password";
                continue;
            }
            
            $socket_options = array (
                'ssl' => array(
                    'verify_peer'  => false,
                    'verify_peer_name'  => false
                )
            );

            $smtp = new Net_SMTP($server, $settings['port'], $helo, false, 0, $socket_options);

            $smtp->setDebug(true);

            $res = $smtp->connect(10);

            if (is_a($res, 'PEAR_Error')) {
                $error[] = "{$server} - Cound not connect";
                continue;
            }

            $res = $smtp->auth($settings['username'], $settings['password']);

            if (is_a($res, 'PEAR_Error')) {
                $error[] = "{$server} - Cound not login";
                continue;
            }
        }
        
        print_r($error);exit;
    }
    
    function getHelo()
    {
        $ifconfig = file_get_contents("https://ifconfig.co/");
        $dom = new DomDocument('1.0', 'utf-8');
        $dom->loadHTML($ifconfig);
        
        $xpath = new DOMXPath($dom);
        
        $element = $xpath->query("//code[@class='ip']");
        
        if(!$element->length){
            return;
        }
        
        $ip = $element->item(0)->nodeValue;
        
        $cmd = "host {$ip}";
        
        $e = `$cmd`;
        
        $helo = substr(array_pop(explode(' ', $e)), 0, -2);
        
        return $helo;
    }
    
}
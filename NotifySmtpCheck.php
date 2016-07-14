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
        
        $ifconfig = file_get_contents("https://ifconfig.co/");
        $dom = new DomDocument('1.0', 'utf-8');
        $dom->loadHTML($ifconfig);
        
        $xpath = new DOMXPath($dom);
        
        $lists = $xpath->query("code[@class='ip']");
        
        
        print_R($lists);exit;
        
        $error = array();
        
        foreach ($ff->Core_Notify['routes'] as $server => $settings){
            if(empty($settings['domains']) || empty($settings['username']) || empty($settings['password'])){
                $error[] = "{$server} missing domains / username / password";
                continue;
            }
            
            foreach ($settings['domains'] as $dom){
                
                $socket_options = array (
                    'ssl' => array(
                        'verify_peer'  => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true
                    )
                );
                
                $smtp = new Net_SMTP($server, $settings['port'], '058177247238.ctinets.com', false, 0, $socket_options);
                
                $smtp->setDebug(true);
                
                $res = $smtp->connect(10);
                
                if (is_a($res, 'PEAR_Error')) {
                    die("Cound not connect to {$server}");
                }
                
                $res = $smtp->auth($settings['username'], $settings['password']);
            
                if (is_a($res, 'PEAR_Error')) {
                    die($res);
                }
                
                print_R("SUCCESS? {$res} \n");
                exit;
                   
            }
            
            
        }
    }
    
}
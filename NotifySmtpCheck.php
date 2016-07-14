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
                
                $smtp = new Net_SMTP($server, 25, '058177247238.ctinets.com', false, 0, $socket_options);
                
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
                    
//                $mx = $smtpmx->_getMx($dom);
//                
//                foreach ($mx as $mserver => $mpriority) {
//                    
//                    $smtpmx->_smtp = new Net_SMTP($mserver, $smtpmx->port, $smtpmx->mailname);
//                    
////                    print_R($smtpmx);exit;
//                    $res = $smtpmx->_smtp->connect($smtpmx->timeout);
//
//                    $smtpmx->_smtp->disconnect();
//                    
//                    print_R($res);exit;
//                }
                
            }
            
            
        }
    }
    
}
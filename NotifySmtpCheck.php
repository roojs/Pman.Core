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
//                $mailer = Mail::factory('smtp', array(
//                    'host'    => $dom ,
//                    'localhost' => $server,
//                    'timeout' => 15,
//                    'auth' => true,
//                    'username' => $settings['username'],
//                    'password' => $settings['password']
//                ));
//                
//                print_R($mailer);exit;
                
                $params = array(
                    'netdns' => false
                );
                
                $smtpmx = new Mail_smtpmx($params);
                $smtpmx->debug = true;
                
                $smtpmx->_smtp = new Net_SMTP($server, $smtpmx->port, $smtpmx->mailname);
                
                $smtpmx->_smtp->setDebug(true);
                
//                print_R($smtpmx->_smtp);exit;
                
                $res = $smtpmx->_smtp->connect($smtpmx->timeout);
                
                if (is_a($res, 'PEAR_Error')) {
                    print_R('error?????');exit;
                }
            
//                $smtpmx->_smtp->disconnect();
                print_r("resutlt : {$res} \n");
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
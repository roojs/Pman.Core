<?php

require_once 'Pman.php';

class Pman_Core_NotifySmtpCheck extends Pman
{
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
        
        require_once "Mail.php";
        
        foreach ($ff->Core_Notify['routes'] as $server => $settings){
            if(empty($settings['domains']) || empty($settings['username']) || empty($settings['password'])){
                $error[] = "{$server} missing domains / username / password";
                continue;
            }
            
            foreach ($settings['domains'] as $dom){
                $mailer = Mail::factory('smtp', array(
                    'host'    => $dom ,
                    'localhost' => $server,
                    'timeout' => 15,
                    
                ));
            }
            
            
        }
    }
    
}
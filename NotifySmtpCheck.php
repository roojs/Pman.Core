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
        
        foreach ($ff->Core_Notify['routes'] as $server => $settings){
            if(empty($settings['username']) || empty($settings['password'])){
                $error[] = "{$server} missing username or password";
                continue;
            }
            
            
        }
    }
    
}
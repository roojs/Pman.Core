<?php

require_once 'Pman.php';

class Pman_Core_NotifySmtpCheck extends Pman
{
    function getAuth() 
    {
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->cli)) {
            $this->cli = true;
            return true;
        }
        
        return false;
        
    }
     
    function get($args, $opts)
    {
        
    }
    
}
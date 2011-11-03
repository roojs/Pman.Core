<?php

require_once 'Pman.php';

class Pman_Core_GoogleKey extends Pman
{
    function getAuth()
    {
        
        $au = $this->getAuthUser();
        if (!$au) {
            $this->jerrAuth("only authenticated users");
        }
        
        $this->authUser = $au;
    }
    
    function post()
    {
        $key = HTML_FlexyFramework::get()->Pman_Core['googlekey'];
        $this->jdata($key)
        
    }
    
    
}
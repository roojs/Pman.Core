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
    function get() {
        // for testing..
        return $this->post();
    }
    
    function post()
    {
        $pc = HTML_FlexyFramework::get()->Pman_Core;
        if (empty($pc['googlekey'])) {
            $this->jerr("Google API Key not configured");
        }
        $key = $pc['googlekey'];
        $this->jdata($key);
        
    }
    
    
}
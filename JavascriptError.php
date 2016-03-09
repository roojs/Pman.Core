<?php
// FIXME... auth errors need ratelimiting 

require_once 'Pman.php';

class Pman_Core_JavascriptError extends Pman {
    
    
    function getAuth()
    {
        parent::getAuth(); // load company!
        $au = $this->getAuthUser();
       
        if (!$au || !$au->pid()) {
            
            die("authenticated Users only");
        }
        
        
        $this->authUser = $au;
        return true;
    }
    
    function get()
    {
        die("invalid url");
    }
    
    function post()
    {
         
        $this->addEvent("JSERROR", false,$_REQUEST['msg']);
        $this->jok("OK");
    }
    
    
    
    
}

<?php
// FIXME... auth errors need ratelimiting 

class Pman_Core_JavascriptError extends Pman {
    
    
    function getAuth()
    {
        parent::getAuth(); // load company!
        $au = $this->getAuthUser();
       
        if (!$au) {
            
            $this->jerr("Not authenticated", array('authFailure' => true));
        }
        if (!$au->pid()   ) { // not set up yet..
            $this->jerr("Not authenticated", array('authFailure' => true));
        }
        
        
        $this->authUser = $au;
        return true;
    }
    
    
    
    
}

<?php

require_once 'Pman.php';


class Pman_Core_Auth extends Pman {
    

     
    var $event_suffix = '';
    var $ip_management = false;
	 

    function getAuth() // everyone allowed in here..
    {
        parent::getAuth(); // load company..
        
        $ff = HTML_FlexyFramework::get();
        
        $this->ip_management = (empty($ff->Pman['ip_management'])) ? false : true;
        $this->event_suffix = (isset($ff->Pman['auth_event_suffix'])) ? $ff->Pman['auth_event_suffix'] : '';
        
        $this->initErrorHandling(); // was done it get before.
        
        return true;
    }
    
	
	function userdb()
	{
		$ff = HTML_FlexyFramework::get();
        $tbl = empty($ff->Pman['authTable']) ? 'core_person' : $ff->Pman['authTable'];
        
       
        return  DB_DataObject::factory($tbl);
      
	}
    
    function get($base, $opts= array())
    {
        $this->jnotice("INVALIDREQ", "Invalid Request");
    }
    function post($base, $opts= array())
    {
        $this->jnotice("INVALIDREQ", "Invalid Request");
    }
    function output()
    {
        die("OUTPUT - should not get here");
    }
}
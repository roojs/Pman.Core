<?php

// check if database is workign - used by nagios checking - to see if server is really up.

require_once 'Pman.php';

class Pman_Core_Heartbeat extends Pman
{
    function getAuth()
    {
        return true;
    }
    
    function get($req, $opts = array())
    {
        $this->post($req);
        die("POST only");
    }
    
    function post($req)
    {
        $this->initErrorHandling();
        
        if ($this->database_is_locked()) {
            die("FAILED");
        }
        
        // Use gethostbyaddr("127.0.1.1") to get FQN from hosts file
        $res = DB_DataObject::Factory('core_heartbeat')->hostCheck(gethostbyaddr("127.0.1.1"));
        die($res);
    }
    
     
    function onPearError($err)
    {
      //  print_r($err);
        die("FAILED");
    }
    function onException($err)
    {
      //  print_r($err);
        die("FAILED");
    }
    
   
}
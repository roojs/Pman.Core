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
        
        
        $cd = DB_DataObject::Factory('core_enum');
        $cd->setFrom(array(
            'etype' => 'heartbeat',
            'name' => 'last_update_'. gethostname()
        ));
        if (!$cd->count()) {
            $cd->display_name = date("Y-m-d H:i:s");
            $cd->insert();
            die("OK - HEARTBEAT WORKING");
        }
        $cd->find(true);
        $cc = clone($cd);
        if ( (time() - strtotime($cc->display_name)) < 30) {
            die("OK - HEARTBEAT WORKING");
        }
        
        $cd->display_name = date("Y-m-d H:i:s");
        $cd->update($cc);
        die("OK - HEARTBEAT WORKING");
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
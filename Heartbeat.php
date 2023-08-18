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
        $cd = DB_DataObject::Factory('core_enum');
        $cd->setFrom(array(
            'etype' => 'heartbeat',
            'name' => 'last_update'
        ));
        if (!$cd->count()) {
            $cd->display_name = date("Y-m-d H:i:s");
            $cd->insert();
            die("OK");
        }
        $cd->find(true);
        $cc = clone($cd);
        $cd->display_name = date("Y-m-d H:i:s");
        $cd->update($cc);
        die("OK");
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
<?php

/**
 * 
 * this is technically a cli wrapper for the generator..
 * 
 * we will test it on the web initially..
 * 
 * 
 */
require_once 'Pman.php';

class Pman_Core_RefreshDatabaseCache extends Pman
{     
    static $cli_desc = "Refresh the database schema cache";
   
    var $cli = false;
    function getAuth() {
        
        
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->cli)) {
            $this->cli = true;
            return true;
        }
        parent::getAuth(); // load company!
        $au = $this->getAuthUser();
        if (!$au || $au->company()->comptype != 'OWNER') {
            $this->jerr("Not authenticated", array('authFailure' => true));
        }
        $this->authUser = $au;
        return true;
    
    
    }
     
    function get($args, $opts=array())
    {
        //print_r($opts);exit;
        
        
        // technically it would be good to trash the cached ini files here.. 
        // however we can not really do that, as the ownships are off..
        //we can however regen our own files..
        //DB_DataObject::debugLevel(1);
        //HTML_FlexyFramework::get()->debug = 1;
        HTML_FlexyFramework::get()->generateDataobjectsCache(true);
        
        die("done!");
    }
    
}
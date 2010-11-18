<?php


require_once 'Pman.php';

class Pman_Core_Lock extends Pman
{
    
    function getAuth()
    {
         $au = $this->getAuthUser();
        if (!$au) {
             $this->jerr("Not authenticated", array('authFailure' => true));
        }
        $this->authUser = $au;
        // check that it's a supplier!!!! 
        
        return true; 
    }
    
    function get()
    {
        if (empty($_REQUEST['on_id']) || empty($_REQUEST['on_table'])) {
            
        }
        $tab = str_replace('/', '',$tab); // basic protection??
        $x = DB_DataObject::factory($tab);
    }
    
}
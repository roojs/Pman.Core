<?php
/**
 * in theory a generic 'action' handler..
 *
 * Part of the eventual workflow code...
 * -> at present, just flags something as done.....
 *
 *
 */

class Pman_Core_NotifyAction extends Pman
{
    
    function getAuth()
    {
        $au = $this->getAuthUser();
        if (!$au) {
             $this->jerr("Not authenticated", array('authFailure' => true));
        }
        // workflow only applicable to owner company..
        if ($au->company()->comptype != 'OWNER') {
            $this->jerr("Core:NotifyAction: invalid user - not owner company.");
            
        }
        
        $this->authUser = $au;
        // check that it's a supplier!!!! 
        
        return true; 
    }
    
    
    function get()
    {
        $this->jerr("invalid request");
        
    }
    function post()
    {
        // needs: (Array of...)
        // ontable, action(eg. APPROVAL)
        // onid (comma delimited.)
        $n = DB_DataObject::factory('core_notify');
        // in theory in workflow, this could trigger another action...
        // if watch is on it..
        foreach(array('on_table','on_id','action') as $k) {
            if (empty($_POST[$k])) {
                $this->jerr("missing argument $k");
            }
            $n->$k = $v;
        }
        
        
        
        
        
        
    }
    
}
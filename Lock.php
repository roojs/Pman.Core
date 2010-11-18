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
    
    function get($action)
    {
        
        // default action is to attempt to lock..
        $action = empty($action) ? 'lock' : 'unlock';
        $this->$action($curlock);
        
       
        
        
    }
    
    function unlock($curlock)
    {
    
        if (empty($_REQUEST['id'])) {
            $this->jerr("No lock id");
        }
        $curlock = DB_DataObject::factory('Core_locking');
        if (!$curlock->get($_REQUEST['id'])) {
            $this->jerr("No lock exists");
        }
        
        if ($curlock->person_id != $this->authUser->id) {
            $this->jerr("Lock id is invalid");
        }
        
        $curlock->delete();
        
        $this->jok('unlocked');
    }
    function lock()
    {
        
        if (empty($_REQUEST['on_id']) || empty($_REQUEST['on_table'])) {
            $this->jerr("Missing table or id");
        }
       
        $tab = str_replace('/', '',$_REQUEST['on_table']); // basic protection??
        $x = DB_DataObject::factory($tab);
        if (!$x->get($_REQUEST['on_id'])) {
            $this->jerr("Item does not exist");
        }
        // is there a current lock on the item..
        
        $curlock = DB_DataObject::factory('Core_locking');
        $curlock->setFrom(array(
            'on_id' => $_REQUEST['on_id'],
            'on_table' => $_REQUEST['on_table']
        ));
        if ($curlock->count()) {
            $err  = $this->canunlock();
            if ($err !== true) {
                $this->jerr($err);
            }
        }
        // make a lock..
         
        
        
    
}
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
        
        // default action is to attempt to lock..
        if (empty($_REQUEST['on_id']) || empty($_REQUEST['on_table'])) {
            $this->jerr("Missing table or id");
        }
        $action = empty($_REQUEST['unlock']) ? 'lock' : 'unlock';
        $tab = str_replace('/', '',$_REQUEST['on_table']); // basic protection??
        $x = DB_DataObject::factory($tab);
        if (!$x->get($_REQUEST['on_id'])) {
            $this->jerr("Item does not exist");
        }
        
        $locked = false;
        if ($curlock->find(true)) {
            $locked = true;
        }
        $this->$action($curlock);
        
    }
    
    function unlock($curlock)
    {
    
        $curlock = DB_DataObject::factory('Core_locking');
        $curlock->setFrom(array(
            'on_id' => $_REQUEST['on_id'],
            'on_table' => $_REQUEST['on_table'],
            'person_id' => $this->authUser->id,
        ));
        
        
        if (!$curlock->find()) {
            $this->jok("No lock");
        }    
        while ($curlock->fetch()) {
            $cc = clone($curlock);
            $cc->delete();
        }
        
        $this->jok('unlocked');
    }
    
    
}
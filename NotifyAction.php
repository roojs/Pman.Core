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
        // on_table, action(eg. APPROVAL)
        // on_id (comma delimited.)
        $n = DB_DataObject::factory('core_notify');
        $n->person_id = $this->authUser->id;
        // in theory in workflow, this could trigger another action...
        // if watch is on it..
        foreach(array('on_table','on_id','action') as $k) {
            if (empty($_POST[$k])) {
                $this->jerr("missing argument $k");
            }
            if ($k == 'on_id') {
                continue;
            }
            $n->$k = $v;
        }
        $ids = explode(',', $_POST['on_id']);
        $n->whereAdd('sent < act_when'); // not issued yet..
        $n->whereAdd("join_watch_id_id.medium = '". $n->escape($k) ."'");
        $n->whereAddIn('core_notify.id', $ids, 'int' );
        $n->autoJoin();
        $ar = $n->fetchAll();
        
        foreach($ar as $n) {
            $nc = clone($n);
            $nc->sent = date('Y-m-d H:i:s');
            $nc->update($n);
            // add an event?????
            
        }
    
        
        
        
        
    }
    
}
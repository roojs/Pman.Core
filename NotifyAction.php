<?php
/**
 * in theory a generic 'action' handler..
 *
 * Part of the eventual workflow code...
 * -> at present, just flags something as done.....
 *
 *
 */
require_once 'Pman.php';

class Pman_Core_NotifyAction extends Pman
{
    
    function getAuth()
    {
        $au = $this->getAuthUser();
        if (!$au) {
             $this->jerror("LOGIN-NOAUTH", "Not authenticated", array('authFailure' => true));
        }
        // workflow only applicable to owner company..
        if ($au->company()->comptype != 'OWNER') {
            $this->jerr("Core:NotifyAction: invalid user - not owner company.");
            
        }
        
        $this->authUser = $au;
        // check that it's a supplier!!!! 
        
        return true; 
    }
    
    
    function get($v, $opts=array())
    {
        $this->jerr("invalid request");
        
    }
    function post($v)
    {
        // needs: (Array of...)
        //   on_table,
        //   action(eg. APPROVAL)
        //   on_id (comma delimited.)
        //DB_DataObject::debugLevel(1);
        $n = DB_DataObject::factory('core_notify');
        $n->person_id = $this->authUser->id;
        // in theory in workflow, this could trigger another action...
        // if watch is on it..
        foreach(array('ontable','onid','action') as $k) {
            if (empty($_POST[$k])) {
                $this->jerr("missing argument $k");
            }
            if ($k == 'onid') {
                continue;
            }
            $n->$k = $_POST[$k];
        }
        
        $n->whereAdd('sent < act_when'); // not issued yet..
        $n->whereAdd("join_watch_id_id.medium = '". $n->escape($n->action) ."'");
        $n->whereAddIn('core_notify.onid', explode(',', $_POST['onid']), 'int' );
        $n->autoJoin();
        $ar = $n->fetchAll();
        
        $done = array();
        
        foreach($ar as $n) {
            $nc = clone($n);
            $nc->sent = date('Y-m-d H:i:s');
            $nc->update($n);
            
            // add an event????? - yeap... only once per object
            $key = implode(':', array($nc->ontable,$nc->onid));
            if (!isset($done[$key])) { 
                
                $e = $this->addEvent($_POST['action'],$n->object());
            }
            $done[$key] = true;
        }
        $this->jok("updated");
        
        
    }
    
}
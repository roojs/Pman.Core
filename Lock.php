<?php



/**
 * 
 * Locking theory
 * 
 * 
 * This page is locked by XXXXXXX.. 
 * Do you to prevent them saving and lock it yourself..
 * 
 * 
 * 
 * 
 * 
 * -- interacts with Roo and _lock = id..
 * 
 * 
 * call : 
 * try and lock it..
 * baseURL + /Core/Lock/lock?on_id=...&on_table=...
 * - returns id or an array of who has the locks.
 * 
 * Force an unlock after a warning..
 * baseURL + /Core/Lock/lock?on_id=...&on_table=...&force=1
 * - returns id..
 * 
 * Unlock - call when window is closed..
 * baseURL + /Core/Lock/unlock?on_id=...&on_table=...&force=1
 * - returns jerr or jok
 */

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
    
    function get($action, $opts=array())
    {
        // should we allow url links to lock things???
        // only for debugging??
        $this->post($action);
        // 
        $this->jerr("invalid request");
    }
    
    function post($action)
    {
        
        // default action is to attempt to lock..
        $action = empty($action) || $action == 'lock' ? 'lock' : 'unlock';
        $this->$action();
        
    }
    
    function unlock()
    {
    
        if (empty($_REQUEST['id'])) {
            $this->jerr("No lock id");
        }
        $curlock = DB_DataObject::factory('core_locking');
        if (!$curlock->get($_REQUEST['id'])) {
            $this->jok("No lock exists"); // been deleted before.. probably ok..
        }
        
        if ($curlock->person_id != $this->authUser->id && empty($_REQUEST['force'])) {
            // this is an error conditon..
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
       
        $tab = str_replace('/', '', strtolower($_REQUEST['on_table'])); // basic protection??
        $x = DB_DataObject::factory($tab);
        if (!$x->get($_REQUEST['on_id'])) {
            $this->jerr("Item does not exist");
        }
        // is there a current lock on the item..
        
        $curlock = DB_DataObject::factory('core_locking');
        $curlock->setFrom(array(
            'on_id' => $_REQUEST['on_id'],
            'on_table' => strtolower($_REQUEST['on_table'])
        ));
        
        // remove old locks..
        $llc = clone($curlock);
        $exp = date('Y-m-d', strtotime('NOW - 1 WEEK'));
        $llc->whereAdd("created < '$exp'");
        if ($llc->count()) {
            $llc->find();
            while($llc->fetch()) {
                $llcd = clone($llc);
                $llcd->delete();
           
            }
        }
        
        
        $curlock_ex = clone($curlock);
        $curlock->person_id = $this->authUser->id;
        
        
        $curlock_ex->whereAdd('person_id != '. $this->authUser->id);
        $nlocks = $curlock_ex->count() ;
        
        $ret = false;
        
        if ($nlocks && empty($_REQUEST['force'])) {
           // DB_DataObjecT::debugLevel(1);
            $ar = $curlock_ex->fetchAll('person_id', 'created');
            $p = DB_DataObject::factory('core_person');
            $p->selectAdd();
            $p->selectAdd('id,name,email');
            
            $p->whereAddIn('id', array_keys($ar), 'int');
            $p->find();
            $ret = array();
            while ($p->fetch()) {
                $ret[$p->id] = $p->toArray();
                $ret[$p->id]['lock_created'] = $ar[$p->id];
            }
             $this->jok(array_values($ret));
            
        }
        // trash the lock if it belongs to current user..
        $ulocks = $curlock->count();
        if ($ulocks) {
            // trash all the locks..
            $curlock = DB_DataObject::factory('core_locking');
            $curlock->setFrom(array(
                'on_id' => $_REQUEST['on_id'],
                'on_table' => strtolower($_REQUEST['on_table']),
                'person_id' => $this->authUser->id
            ));
            
            $curlock->find();
            while($curlock->fetch()) {
                $cc =clone($curlock);
                $cc->delete();
            }
        }
        if ($nlocks && !empty($_REQUEST['force'])) {
            // user has decied to delete eveyone elses locks..
            $curlock_ex->find();
            while($curlock_ex->fetch()) {
                $cc =clone($curlock_ex);
                $cc->delete();
            }
        }
        
        
        // make a lock..
        
        $curlock = DB_DataObject::factory('core_locking');
        $curlock->setFrom(array(
            'on_id' => $_REQUEST['on_id'],
            'on_table' => strtolower($_REQUEST['on_table']),
            'created' => date('Y-m-d H:i:s'),
            'person_id' => $this->authUser->id,
        ));
        $id = $curlock->insert();
        $this->jok( $id);
        
    }
     
    
        
    
}
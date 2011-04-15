<?php
/**
 * Table Definition for core_watch
 *
 * works with 'core_notify'
 *
 * any object can call
 *   $watch->notify($ontable, $onid)
 *
 *   in which case it should create a core_notify event.
 *
 *
 * 
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_watch extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_watch';                      // table name
    public $ontable;                         // string(128)  not_null primary_key
    public $onid;                            // int(11)  not_null primary_key
    public $person_id;                         // int(11)  not_null primary_key
    public $event;                           // string(128)  not_null primary_key
    public $medium;                          // string(128)  not_null primary_key
    public $active;                          // int(11)  not_null

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    /** make sure there is a watch for this user.. */
    
    function ensureNotify(  $ontable, $onid, $person_id, $whereAdd)
    {
        //DB_DAtaObject::debugLevel(1);
        $w = DB_DataObject::factory('core_watch');
        $w->person_id = $person_id;
        if (empty($w->person_id)) {
            return;
        }
        
        $nw = clone($w);
        $w->whereAdd($whereAdd);
        
        
        if ($w->count()) {
            return;
        }
        $nw->ontable = $ontable;
        $nw->onid = $onid;
        
        $nw->medium = 'email';
        $nw->active = 1;
        $nw->insert();
        
        
    }
    
    function notify($ontable , $onid, $whereAdd)
    {
        $w = DB_DataObject::factory('core_watch');
        $w->whereAdd($whereAdd);
        $w->selectAdd();
        $w->selectAdd('distinct(person_id) as person_id');
        $people = $w->fetchAll('person_id');
        $nn = DB_DataObject::Factory('core_notify');
        $nn->ontable = $ontable;
        $nn->onid = $onid;
        foreach($people as $p) {
            if (!$p) { // no people??? bugs in watch table
                continue;
            }
            $n = clone($nn);
            $n->person_id = $p;
            $nf = clone($n);
            $nf->whereAdd('sent < act_when');
            if ($nf->count()) {
                // we have a item in the queue for that waiting to be sent..
                continue;
            }
            $n->act_when = date("Y-m-d H:i:s");
            $n->insert();
            
            
        }
        
        
        
    }
     
}

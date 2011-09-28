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
 * Should 'event' trigger this..
 *   -> eg. somebody makes a 'EDIT' on 'person'
 *   -> a watch exists for
 *        ontable=person,
 *        onid = -1 <<-- every entry..
 *        person_id -> who is goes to.
 *        event = CRUD (eg. shortcut for edit/create/delete)
 *        medium = "REVIEW" << eg. review needed..
 *        
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
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $ontable;                         // string(128)  not_null
    public $onid;                            // int(11)  not_null
    public $person_id;                       // int(11)  not_null
    public $event;                           // string(128)  not_null
    public $medium;                          // string(128)  not_null
    public $active;                          // int(11)  not_null

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    /** make sure there is a watch for this user.. */
    
    /**
     *
     * Create a watch...
     *
     */
    
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
            $n->act_start( date("Y-m-d H:i:s") );
            $n->insert();
            
            
        }
         
    }
    // static really...
    function notifyEvent($event)
    {
        // see if there are any watches on events..
        // notify everyone flagged except the person doing it...
        // this is very basic logic... -
        //    if more intelligence is needed...
        //    then it 'rules' will have to be added by the watched object....
        //    anyway leave that for another day..
        if (empty($event->action)) {
            return;
        }
        $w = DB_DataObject::factory('core_watch');
        $w->ontable = $event->on_table;
        $w->whereAdd('onid = 0 OR onid='. ((int) $event->on_id));
       
        $w->event  = $event->action;
        $w->active = 1;
        
        
        $w->whereAdd('person_id != '. (int) $event->person_id);

        
        $watches = $w->fetchAll();
        
        $nn = DB_DataObject::Factory('core_notify');
        $nn->ontable    = $event->on_table;
        $nn->onid       = $event->on_id;
        
        foreach($watches as $watch) {
            if (!$watch->person_id) { // no people??? bugs in watch table
                continue;
            }
            
            $n = clone($nn);
            $n->person_id = $p;
            $n->watch_id =  $watch->id;
            
            // does this watch already have a flag...
            $nf = clone($n);
            $nf->whereAdd('sent < act_when');
            if ($nf->count()) {
                // we have a item in the queue for that waiting to be sent..
                continue;
            }
            
            $n->act_start( date("Y-m-d H:i:s") );
            $n->insert();
            
            
        }
        
        
        
    }
    
     
}

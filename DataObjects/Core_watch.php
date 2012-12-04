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
    /**
     * Generate a notify event based on watches (matching whereAdd)
     *
     * Usage: $core_watch->notify('mtrack_repos', 1, false, date()
     *
     * This can match any 'event' type - eg. it can be blank etc...
     *   Generally used by non-event driven notifications, like our
     *   Daily commit message.
     *
     *  @param string $ontable - the database table that has been updated/changed etc.
     *  @param int    $onid    - the id of the row changed
     *  @param string  $whereAdd (optiona) - a DB whereAdd() condition to filter the search for watches
     *  @param datetime    $when   (default now)  - date/time to create the notification for (Eg. end of day..)
     * 
     */
    function notify($ontable , $onid, $whereAdd = false, $when=false)
    {
        $w = DB_DataObject::factory('core_watch');   
        if ($whereAdd !== false) { 
            $w->whereAdd($whereAdd  );
        }
        $w->active =1;
        
        $w->whereAdd('onid = 0 OR onid='. ((int) $onid));
       
        
        $w->ontable = $ontable;
        //$w->selectAdd();
        //$w->selectAdd('distinct(person_id) as person_id');
        
        foreach($w->fetchAll() as $w) { 
            if (!$w->person_id) { // no people??? bugs in watch table
                continue;
            }
         
         
         
            $nn = DB_DataObject::Factory('core_notify');
            $nn->ontable = $ontable;
            $nn->onid = $onid;
            $nn->evtype = $w->medium;
            $nn->person_id = $w->person_id;
            
            $nf = clone($nn);
            $nf->whereAdd("sent < '2000-01-01'");
            if ($nf->count()) {
                // we have a item in the queue for that waiting to be sent..
                continue;
            }
            $nn->act_start( date("Y-m-d H:i:s", $when !== false ? strtotime($when) : time()) );
            $nn->insert();
        }
          
    }
    // static really...
    /**
     *
     * This get's called by roo->jok()
     *
     * And searches for matching '$watch->event' == $event->action
     *  along with id/table etc..
     *
     * it's basic usage is to fill in core_notify after an event has happend.
     *
     * We can also use it to notify other modules if something has happend.
     *  = eg. mtrack_ticket * watch will notify mtrack_jira::
     *
     * @param Pman_Core_DataObject_Events $event - the Pman event dataobject that was created
     * 
     */
    
    function notifyEvent($event)
    {
        //DB_DataObject::DebugLevel(1);
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
        
        //print_R($watches);exit;
        
        $nn = DB_DataObject::Factory('core_notify');
        $nn->ontable    = $event->on_table;
        $nn->onid       = $event->on_id;
        
        foreach($watches as $watch) {
            if (!$watch->person_id) { // no people??? bugs in watch table
                $dom = explode(':',$watch->medium);
                if (count($dom) != 2) {
                    continue;
                }
                $do = DB_DataObject::factory($dom[0]);
                if (!method_exists($do,$dom[1])) {
                    continue;
                }
                $do->{$dom[1]}($event);
                
                continue;
            }
            
            $n = clone($nn);
            $n->trigger_person_id = $event->person_id;
            $n->trigger_event_id = $event->id;
            $n->person_id = $watch->person_id;
            $n->watch_id =  $watch->id;
            
            // does this watch already have a flag...
            $nf = clone($n);
            $nf->whereAdd("sent ><'2000-01-01'");
            //$nf->whereAdd('sent < act_when');
            if ($nf->count()) {
                // we have a item in the queue for that waiting to be sent..
                continue;
            }
            
            $n->act_start( date("Y-m-d H:i:s") );
            $n->insert();
            
            
        }
        
        
        
    }
    
     
}

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
        $w = DB_DataObject::factory('core_watch');
        $w->ontable = $ontable;
        $w->onid = $onid;
        $w->person_id = $personid;
        $nw = clone($w);
        $w->whereAdd($whereAdd);
        
        
        if ($w->count()) {
            return;
        }
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
    /***
     * The purpose of this is to gather all the events that have
     * occured in the system (where watches exist)
     * Do we want to send the user 1 email ?? or multiple...
     * --> I guess multiple emails..
     *
     * so we need to return
     *
     *  array(
          $USER_ID => array(
                $OBJECT:$ID, $OBJECT:$ID, $OBJECT:$ID, .....
          )
     * )
     *
     * The mailer can then go through and call each object ??
     *
     *
     * -- Things we can watch..
     *
     * mtrack_change <- this is a neat log of all events.
     *  which logs these things
     *     Individual Ticket changes (already)
     *     a Project -> which means ticket changes... which again can be discovered via mtrack_changes..
     *     a Repo for Commits (-- which will be handled by mtrack_changes)
     *     Wiki changes.. later...
     *     
     *
     *
     */
    
    function watched($medium, $watcher = null)
    {
        $w = DB_DataObject::factory('core_watch');
        if ($watcher) {
            $w->person_id = $watcher;
        }
        $w->active = 1;
        $w->medium = $medium;
        $ar = $w->fetchAll();
        $ret = array();
        foreach($ar as $o) {
            if (!isset($ret[$o->person_id])) {
                $ret[$o->person_id] = array();
            }
            $ret[$o->person_id][] = $o->ontable .':'. $o->onid;
        }
        
        return $ret;
    }
}

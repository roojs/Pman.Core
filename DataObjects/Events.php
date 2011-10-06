<?php
/**
 * Table Definition for Events
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Events extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Events';                          // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $person_name;                     // string(128)  
    public $event_when;                      // datetime(19)  binary
    public $action;                          // string(32)  
    public $ipaddr;                          // string(16)  
    public $on_id;                           // int(11)  
    public $on_table;                        // string(64)  
    public $person_id;                       // int(11)  
    public $remarks;                         // blob(65535)  blob

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    
    
    
    //  ------------ROO HOOKS------------------------------------
    function applyFilters($q, $au)
    {
        if (!empty($q['query']['from'])) {
            $dt = date('Y-m-d' , strtotime($q['query']['from']));
            $this->whereAdd(" Events.event_when >=  '$dt' ");
        }
        if (!empty($q['query']['to'])) {
            $dt = date('Y-m-d' , strtotime($q['query']['to']));
            $this->whereAdd(" Events.event_when <=  '$dt' ");
        }
        /*
        if (!empty($q['query']['grouped']) && $q['query']['grouped'] == 'gr') {
            // grouped..
            DB_DataObject::Debuglevel(1);
            $this->groupBy('on_id');
            $this->selectAdd('
                (SELECT count(id) FROM core_event_audit WHERE event_id = Events.id) as changed
                ');
        }
        */
        
        if (!$au->hasPerm("Admin.Admin_Tab", 'S')) {
            //DB_DataObject::DebugLevel(1);
            // they can only view their changes..
            $this->person_id = $au->id;
            
        }
        
            
    }
    /**
     * check who is trying to access this. false == access denied..
     */
    function checkPerm($lvl, $au) 
    {
        if ($lvl == 'S') {
            return true;
        }
        // listing is controleed by applyfilters..
        return $au->hasPerm("Admin.Admin_Tab", 'S');
    } 
    /**
     * init:
     * Initialize an event - ready to insert..
     * 
     * @param {String} action  - group/name of event
     * @param {DataObject|false} obj - dataobject action occured on.
     * @param {String} any remarks 
     */
    
    function init($act, $obj, $remarks)
    {
        $pg = HTML_FlexyFramework::get()->page;
        $au = $pg->getAuthUser();
         
        $this->person_name = $au && !empty($au->name) ? $au->name : '';
        $this->person_id = $au ? $au->id : '';
        $this->ipaddr = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : 'cli';
        $this->action = $act;
        $this->on_table = $obj ? $obj->tableName() : '';
        $pk = $obj ? $obj->keys()  : false;
        $this->on_id  = $obj && $pk ? $obj->{$pk[0]}: 0;
        $rem  = array();
        // should this really go in remarks? - 
        if ($obj && method_exists($obj,'toEventString')) {
            $rem[] = $obj->toEventString() ;
        }
        $rem[] = $remarks;
        $this->remarks = implode(' : ', $rem);
    }
    
    /**
     * Generate an audit for this field.
     *
     * @param {DB_DataObject} new data
     * @param {DB_DataObject} old data
     * 
     * @return {int} number of entries logged.
     */
    
    function audit($new, $old = false)
    {
        if ($old == $new) {
            return 0; // they are the same...
        }
         
        $ret = 0;
        foreach(array_keys($new->table()) as $k) {
            // should we JSON serialize this?
            $n = empty($new->$k) ? '' : $new->$k;
            $o = empty($old->$k) || empty($old->$k) ? '' : $old->$k;
            if ($n == $o) {
                continue;
            }
            $this->auditField($k, $o, $n, $old);
            $ret++;
        }
        return $ret;
    }
    /**
     * Record an audited change, in theory so we can audit data that is not just
     * database Fields...
     *
     * @param {string} $name    table field anme
     * @param {mixed} $ov  old value
     * @param {mixed} $onv  new value
     * @param {mixed} $old  old object (false if we are creating..)
     */
    function auditField($name, $ov, $nv, $old=false )
    {
        $x = DB_DataObject::factory('core_event_audit');
        $x->setFrom(array(
            'event_id' => $this->id,
            'name' => $name,
            'old_audit_id' => $old ? $x->findLast($this, $name) : 0,
            'newvalue' => $nv

        ));
        $x->insert();
    
    }
}

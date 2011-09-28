<?php
/**
 * Table Definition for mtrack_change_audit
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_event_audit extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_event_audit';             // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $event_id;                       // int(11)  not_null multiple_key
    public $name;                       // string(128)  
    public $old_audit_id;                        // int(11)  blob
    public $newvalue;                           // blob(65535)  blob

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    /**
     * get the value, 
     *
     */
    
    function newvalue($event)
    {
        $x = DB_DataObject::factory($event->on_table);
        $ar = $x->links();
        // is the name a link..
        if (!isset($ar[$this->name])) {
            return $this->value;
        }
        if (empty($this->value) ) {
            return '';
        }
        // lr = ProjecT:id
        // get the current value of that...
        $lr = explode(':', $ar[$this->name]);
        $x = DB_DataObject::factory($lr[0]);
        if (!method_exists($x, 'toEventString')) {
            return $lr[0] .':'. $this->value;
        }
        $x->get($this->value);
        
        return $x->toEventString(); // big assumption..        
    }
    
    function oldvalue($event)
    {
        if (!$this->old_audit_id) {
            return 'Unknown';
        }
        //var_dump($cg->ontable);
        $x = DB_DataObject::factory('core_event_audit');
        $x->get($this->old_audit_id);
        return $x->newvalue($event);
        
    }
     
     
    
    
    
}
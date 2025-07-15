<?php
/**
 * Table Definition for core_event_audit
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

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
    function findLast($event, $name)
    {
        //DB_DataObject::debugLevel(1);
        $x = DB_DataObject::factory('core_event_audit');
        $x->autoJoin();
         
        
        $x->selectAdd();
        $x->selectAdd('core_event_audit.id as id');

        $x->name = $name;
        $x->whereAdd("
                join_event_id_id.on_table = '{$event->on_table}' AND
                join_event_id_id.on_id    = {$event->on_id}
        ");
        $x->orderBy('join_event_id_id.event_when DESC');
        $x->limit(1);
        if (!$x->find(true)) {
            return 0;
        }
        return $x->id;
        
    }
}
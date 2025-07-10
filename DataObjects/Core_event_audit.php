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
    
    /**
     * log changes to a record in the database in an event
     * 
     * @param object $roo
     * @param DB_DataObject $old old DB_DataObject
     * @param DB_DataObject $new updated DB_DataObject
     * @param array $remarks remarks (e.g. updated by script XXX)
     */
    static function logChanges($roo, $oldObj, $newObj, $remarks)
    {
        // not dataobject
        if(!$oldObj instanceof DB_Dataobject || !$newObj instanceof DB_Dataobject) {
            return;
        }

        $old = $oldObj->toArray();
        $new = $newObj->toArray();
        $new['email'] = '2234@aaaa';

        // only keep keys shared by both arrays
        $old = array_intersect_key($old, $new);
        $new = array_intersect_key($new, $old);

        foreach($new as $k => $v) {
            // there is a change -> keep
            if($old[$k] != $v) {
                continue;
            }

            // no change -> not needed
            unset($old[$k]);
            unset($new[$k]);
        }

        // no difference -> no log needed
        if(empty($new)) {
            return;
        }

        $json = json_encode(array(
            'from' => $old,
            'to' => $new
        ), JSON_PRETTY_PRINT);

        $e = $roo->addEvent('EDIT', $newObj, $remarks);
        $e->writeEventLog($json);
    }
}
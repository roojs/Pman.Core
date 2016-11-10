<?php
/**
 *
 * Table is designed to be used with a mailer to notify or issue
 * emails (or maybe others later??)
 *
 *
CREATE TABLE  core_notify  (
  `id` int(11)  NOT NULL AUTO_INCREMENT,
  `recur_id` INT(11) NOT NULL;
  `act_when` DATETIME NOT NULL,
  `onid` int(11)  NOT NULL DEFAULT 0,
  `ontable` varchar(128)  NOT NULL DEFAULT '',
  `person_id` int(11)  NOT NULL DEFAULT 0,
  `msgid` varchar(128)  NOT NULL  DEFAULT '',
  `sent` DATETIME  NOT NULL,
  `event_id` int(11)  NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `lookup`(`act_when`, `msgid`)
);
**/

require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_notify extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_notify';                     // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $recur_id;                        // int(11) not_null
    public $act_when;                        // datetime(19)  not_null multiple_key binary
    public $onid;                            // int(11)  not_null
    public $ontable;                         // string(128)  not_null
    public $person_id;                       // int(11)  not_null
    public $msgid;                           // string(128)  not_null
    public $sent;                            // datetime(19)  not_null binary
    public $event_id;                        // int(11)  
    public $watch_id;                        // int(11)  
    public $trigger_person_id;                 // int(11)
    public $trigger_event_id;              // int(11)  
    public $evtype;                         // event type (or method to call)fall
    public $act_start;
    public $person_table;


    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function person($set = false)
    {
        $def_pt = 'core_person';
        
        if ($set !== false) {
            $this->person_table = is_object($set) ? $set->tableName() : '';
            
            
            
            $person_table = empty($this->person_table) ? $def_pt  : strtolower($this->person_table);
            $col = $person_table  == $def_pt ? 'person_id' : $person_table . '_id';
            
            $this->{$col} = is_object($set) ? $set->id : $set;
            return;
        }
        static $cache  =array();
        $person_table = empty($this->person_table) ? $def_pt  : strtolower($this->person_table);
        $col = $person_table == $def_pt  ? 'person_id' : $person_table . '_id';
        
        if (isset($cache[$person_table .':'. $this->{$col}])) {
            return $cache[$person_table .':'. $this->{$col}];
        }
        
        $c = DB_DataObject::Factory($person_table == 'person' ? 'core_person' : $person_table);
        $c->get($this->{$col});
        $cache[$person_table .':'. $this->{$col}] = $c;
        return $c;
        
    }
    function object($set = false)
    {
        if ($set !== false) {
            $this->ontable = $set->tableName();
            $this->onid = $set->id;
            return $set;
        }
        $c = DB_DataObject::factory($this->ontable);
        
        if ($this->onid == 0) {
            return $c; // empty dataobject.
        }
        
        $c->autoJoin();
        
        if ($c->get($this->onid)) {
            return $c;
        }
        return false;
        
    }
    
    function lookup($obj, $person, $evtype='')
    {
        $x = DB_DataObject::Factory($this->tableName());
        $x->object($obj);
        $x->person($person);
        if (!empty($evtype)) {
            $x->evtype = $evtype;
        }
        if ($x->count() != 1) {
            return false;
        }
        $x->find(true);
        return $x;
        
    }
    
    
    function beforeDelete($dependants_array, $roo) {
        if ($this->delivered()) {
            $roo->jerr("you can not delete a record of a successfull delivery");
        }
    }
    function  beforeInsert($request,$roo)
    {
        if (empty($request['act_when']) && !empty($request['act_start'])) {
            $this->act_start($request['act_start']);
        }
        
    }
    function beforeUpdate($old, $request,$roo)
    {
        if (empty($request['act_when']) && !empty($request['act_start'])) {
            $this->act_start($request['act_start']);
        }
    }
    
    
    function act_start($set = false)
    {
        if ($set === false) {
            return $this->act_start;
        }
        $this->act_when = $set;
        $this->act_start = $set;
        return $set;
    }
    
    function event()
    {

        $c = DB_DataObject::factory('Events');
        
        if ($c->get($this->event_id)) {
            return $c;
        }
        return false;
        
    }
    
    function triggerEvent()
    {

        $c = DB_DataObject::factory('Events');
        
        if ($c->get($this->trigger_event_id)) {
            return $c;
        }
        return false;
        
    }
    
    function delivered()
    {
        return !empty($this->msgid);
    }
    
    function whereAddDeliveryStatus($delivered = false)
    {
        $tn = $this->tableName();
        if ($delivered) {
            $this->whereAdd("$tn.msgid IS NOT NULL AND $tn.msgid != ''");
        } else {
            $this->whereAdd("$tn.msgid IS NULL OR $tn.msgid = ''");    
        }
    }
    
    function status() // used by commandline reporting at present..
    {
        switch($this->event_id) {
            case -1:
                return 'DELIVERED';   //not valid..
            case 0:
                return 'PENDING';
            default:
                $p ='';
                if (strtotime($this->act_when) > time()) {
                    $p = "RETRY: {$this->act_when} ";
                }
                return  $p. $this->event()->remarks;
        }
        
    }
    /**
     * current state of process
     *
     * 0 = pending
     * 1 = delivered
     * -1 = failed
     *
     *
     */
    function state()
    {
           
        if ($this->msgid != '') {
            return 1;
        }
        
        // msgid is empty now..
        // if act_when is greater than now, then it's still pending.
        if (strtotime($this->act_when) > time()) {
            return 0; 
        }
        
        // event id can be filled in with a failed attempt.
        
        if ($this->event_id > 0) {
            return -1;
        }
        
        // event id is empty, and act_when is in the past... not sent yet..
        
        return 0; // pending
        
        
    }
    
    
    function applyFilters($q, $au, $roo)
    {
        if (isset($q['ontable']) && !in_array($q['ontable'], array('Person', 'Events',  'core_watch'))) {
            // this will only work on tables not joined to ours.
            
            //DB_DAtaObject::DebugLevel(1);
            // then we can build a join..
            $d = DB_DataObject::Factory($q['ontable']);
            $ji = $d->autoJoin();
            //echo '<PRE>';print_R($ji);
            // get cols
            foreach($ji['join_names'] as $cname=>$fname) {
                 $this->selectAdd($fname . ' as ontable_id_' . $cname );
            }
            
            //$this->selectAdd($d->_query['data_select']); -- this will cause the same dataIndex...
            $this->_join .= "
                LEFT JOIN {$d->tableName()} ON {$this->tableName()}.onid = {$d->tableName()}.id
                {$d->_join}
            "; 
            $this->selectAs($d, 'core_notify_%s');
        } 
        if (!empty($q['query']['person_id_name']) ) {
            $this->whereAdd( "join_person_id_id.name LIKE '{$this->escape($q['query']['person_id_name'])}%'");
             
        }
         if (!empty($q['query']['status'])) {
            switch ($q['query']['status']) {
                
                case 'SUCCESS';
                    $this->whereAdd("msgid  != ''");
                    break;
                case 'FAILED';
                    
                    $this->whereAdd("msgid  = '' AND event_id > 0 AND act_when < NOW()");
                    
                    break;
                case 'PENDING';
                    $this->whereAdd('event_id = 0 OR (event_id  > 0 AND act_when > NOW() )');
                    break;
                
                case 'OPENED';
                    $this->whereAdd('is_open > 0');
                    break;
                
                case 'ALL':
                default:
                    break;
            }
        }
        
        if(!empty($q['_evtype_align'])){
            $this->selectAdd("
                (SELECT
                        display_name
                FROM
                        core_enum
                WHERE
                        etype = 'Core.NotifyType'
                    AND
                        name = core_notify.evtype
                    AND
                        active = 1
                ) AS evtype_align
            ");
        }
        
        if(!empty($q['from'])){
            $this->whereAdd("
                act_when >= '{$q['from']}'
            ");
        }
        
        if(!empty($q['to'])){
            $this->whereAdd("
                act_when <= '{$q['to']}'
            ");
        }
        
    }
    
    function sendManual()
    {   
        require_once 'Pman/Core/NotifySend.php';
        
        $send = new Pman_Core_NotifySend();
        $send->error_handler = 'exception';
        
        try {
            $send->get($this->id, array());
        } catch (Exception $e) {
            ob_end_clean();
            return $e;
        }
        
        ob_end_clean();
        
        return true;
    }
    
}

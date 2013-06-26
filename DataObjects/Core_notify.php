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
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function person($set = false)
    {
        if ($set !== false) {
            $this->person_id = is_object($set) ? $set->id : $set;
            return;
        }
        $c = DB_DataObject::Factory('Person');
        $c->get($this->person_id);
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
        
        if(!$c->get($this->onid)){
            return false;
        }
        
        
        //$c->autoJoin();
        
        return $c;
        
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
    function delivered()
    {
        return !empty($msgid);
    }
    
    function status() // used by commandline reporting at present..
    {
        switch($this->event_id) {
            case -1:
                return 'DELIVERED';
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
    
    function applyFilters($q, $au, $roo)
    {
        if (isset($q['ontable']) && !in_array($q['ontable'], array('Person', 'Events' . 'core_watch'))) {
            // this will only work on tables not joined to ours.
            
            //DB_DAtaObject::DebugLevel(1);
            // then we can build a join..
            $d = DB_DataObject::Factory($q['ontable']);
            $d->autoJoin();
            //$this->selectAdd($d->_query['data_select']); -- this will cause the same dataIndex...
            $this->_join .= "
                LEFT JOIN {$d->tableName()} ON {$this->tableName()}.onid = {$d->tableName()}.id
                {$d->_join}
            "; 
            $this->selectAs($d, 'core_notify_%s');
        } 
        if (isset($q['query']['person_id_name']) ) {
            $this->whereAdd( "join_person_id_id.name LIKE '{$this->escape($q['query']['person_id_name'])}%'");
             
        }
        
        
        
        
        
        
    }
    
}

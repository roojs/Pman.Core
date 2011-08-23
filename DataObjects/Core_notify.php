<?php
/**
 *
 * Table is designed to be used with a mailer to notify or issue
 * emails (or maybe others later??)
 *
 *
CREATE TABLE  core_notify  (
  `id` int(11)  NOT NULL AUTO_INCREMENT,
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
    public $act_when;                        // datetime(19)  not_null multiple_key binary
    public $onid;                            // int(11)  not_null
    public $ontable;                         // string(128)  not_null
    public $person_id;                       // int(11)  not_null
    public $msgid;                           // string(128)  not_null
    public $sent;                            // datetime(19)  not_null binary
    public $event_id;                        // int(11)  

    
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
            return;
        }
        $c = DB_DataObject::factory($this->ontable);
        $c->autoJoin();
        if ($c->get($this->onid)) {
            return $c;
        }
        return false;
        
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
}

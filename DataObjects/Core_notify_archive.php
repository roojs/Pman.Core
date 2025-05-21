<?php
/**
 *
 * Archive for core notify
 */
  
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_notify_archive extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_notify_archive';  // table name
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
    public $to_email;
 
 
    function archive()
    {
        
        
        
        
        
    }
 
 
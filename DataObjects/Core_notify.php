<?php
/**
 * Table Definition for core_notify
 */
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
}

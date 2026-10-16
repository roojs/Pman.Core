<?php
/**
 * Table Definition for core_notify_event_archive
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_notify_event_archive extends DB_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_notify_event_archive'; // table name
    public $id;                            // bigint not_null primary_key auto_increment
    public $event_when;                    // datetime
    public $action;                        // varchar(64) not_null
    public $on_id;                         // int not_null
    public $on_table;                      // varchar(64) not_null
    public $person_id;                     // int not_null
    public $person_table;                  // varchar(64) not_null
    public $person_name;                   // varchar(128) not_null
    public $ipaddr;                        // varchar(16) not_null
    public $remarks;                       // text

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

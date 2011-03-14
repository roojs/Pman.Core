<?php
/**
 * Table Definition for core_watch
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_watch extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_watch';                      // table name
    public $ontable;                         // string(128)  not_null primary_key
    public $onid;                            // int(11)  not_null primary_key
    public $person_id;                         // int(11)  not_null primary_key
    public $event;                           // string(128)  not_null primary_key
    public $medium;                          // string(128)  not_null primary_key
    public $active;                          // int(11)  not_null

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

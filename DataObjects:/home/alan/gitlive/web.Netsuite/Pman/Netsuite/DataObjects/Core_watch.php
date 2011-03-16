<?php
/**
 * Table Definition for core_watch
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Core_watch extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_watch';                      // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $ontable;                         // string(128)  not_null
    public $onid;                            // int(11)  not_null
    public $person_id;                       // int(11)  not_null
    public $event;                           // string(128)  not_null
    public $medium;                          // string(128)  not_null
    public $active;                          // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Core_watch',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

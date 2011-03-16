<?php
/**
 * Table Definition for core_locking
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Core_locking extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_locking';                    // table name
    public $int;                             // int(11)  not_null primary_key auto_increment
    public $on_table;                        // string(64)  not_null multiple_key
    public $on_id;                           // int(11)  not_null
    public $person_id;                       // int(11)  not_null
    public $created;                         // datetime(19)  not_null binary

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Core_locking',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

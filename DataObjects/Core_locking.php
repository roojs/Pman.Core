<?php
/**
 * Table Definition for core company
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_locking extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_locking';               // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $person_id;                            // string(64)  not_null
    public $on_id;                            // string(64)  not_null
    public $on_table;                            // string(64)  not_null
    public $created;                            // string(64)  not_null

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    function person() {
        $p = DB_DataObjecT::factory('Person');
        $p->get($this->person_id);
        return $p;
    }
}

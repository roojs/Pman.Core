<?php
/**
 * Table Definition for core company
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_person_window extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_person_window';               // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $person_id;
    public $window_id;                            // string(64)  not_null
    public $login_dt;
    public $force_logout;

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

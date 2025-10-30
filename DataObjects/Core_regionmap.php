<?php
/**
 * Table Definition for core_regionmap
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_regionmap extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_regionmap';    // table name
    public $id;                              // int  not_null primary_key auto_increment
    public $region_id;                       // int  not_null
    public $country;                         // string(8)

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

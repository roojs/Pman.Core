<?php
/**
 * Table Definition for core company
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_enum extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_enum';               // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $name;                            // string(64)  not_null
    public $otype;                            // string(64)  not_null
    public $seqid;                            // string(64)  not_null
    public $active;                            // string(64)  not_null

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

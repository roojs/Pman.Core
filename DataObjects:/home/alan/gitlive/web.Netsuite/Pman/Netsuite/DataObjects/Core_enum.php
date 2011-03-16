<?php
/**
 * Table Definition for core_enum
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Core_enum extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_enum';                       // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $etype;                           // string(32)  not_null
    public $name;                            // string(255)  not_null
    public $active;                          // int(2)  not_null
    public $seqid;                           // int(11)  not_null multiple_key

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Core_enum',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

<?php
/**
 * Table Definition for core_company_type
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Core_company_type extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_company_type';               // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $name;                            // string(64)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Core_company_type',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

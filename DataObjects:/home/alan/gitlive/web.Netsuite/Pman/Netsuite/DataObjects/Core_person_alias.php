<?php
/**
 * Table Definition for core_person_alias
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Core_person_alias extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_person_alias';               // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $person_id;                       // string(128)  
    public $alias;                           // string(254)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Core_person_alias',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

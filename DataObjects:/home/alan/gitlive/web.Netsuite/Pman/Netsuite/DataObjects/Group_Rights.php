<?php
/**
 * Table Definition for Group_Rights
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Group_Rights extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Group_Rights';                    // table name
    public $rightname;                       // string(64)  not_null
    public $group_id;                        // int(11)  not_null
    public $AccessMask;                      // string(10)  not_null
    public $id;                              // int(11)  not_null primary_key auto_increment

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Group_Rights',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

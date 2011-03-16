<?php
/**
 * Table Definition for Group_Members
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Group_Members extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Group_Members';                   // table name
    public $group_id;                        // int(11)  
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $user_id;                         // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Group_Members',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

<?php
/**
 * Table Definition for Office
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Office extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Office';                          // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $company_id;                      // int(11)  not_null
    public $name;                            // string(64)  not_null
    public $address;                         // blob(65535)  not_null blob
    public $phone;                           // string(32)  not_null
    public $fax;                             // string(32)  not_null
    public $email;                           // string(128)  not_null
    public $role;                            // string(32)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Office',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

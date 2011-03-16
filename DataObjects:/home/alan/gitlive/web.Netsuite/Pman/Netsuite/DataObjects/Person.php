<?php
/**
 * Table Definition for Person
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Person extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Person';                          // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $office_id;                       // int(11)  
    public $name;                            // string(128)  not_null
    public $phone;                           // string(32)  not_null
    public $fax;                             // string(32)  not_null
    public $email;                           // string(128)  not_null
    public $company_id;                      // int(11)  
    public $role;                            // string(32)  not_null
    public $active;                          // int(11)  not_null
    public $remarks;                         // blob(65535)  not_null blob
    public $passwd;                          // string(64)  not_null
    public $owner_id;                        // int(11)  not_null
    public $lang;                            // string(8)  
    public $no_reset_sent;                   // int(11)  
    public $action_type;                     // string(32)  
    public $project_id;                      // int(11)  
    public $deleted_by;                      // int(11)  not_null
    public $deleted_dt;                      // datetime(19)  binary

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Person',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

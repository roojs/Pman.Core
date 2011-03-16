<?php
/**
 * Table Definition for Companies
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Companies extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Companies';                       // table name
    public $code;                            // string(32)  not_null
    public $name;                            // string(128)  
    public $remarks;                         // blob(65535)  blob
    public $owner_id;                        // int(11)  not_null
    public $address;                         // blob(65535)  blob
    public $tel;                             // string(32)  
    public $fax;                             // string(32)  
    public $email;                           // string(128)  
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $isOwner;                         // int(11)  
    public $logo_id;                         // int(11)  not_null
    public $background_color;                // string(8)  not_null
    public $comptype;                        // string(8)  not_null
    public $url;                             // string(254)  not_null
    public $main_office_id;                  // int(11)  not_null
    public $created_by;                      // int(11)  not_null
    public $created_dt;                      // datetime(19)  not_null binary
    public $updated_by;                      // int(11)  not_null
    public $updated_dt;                      // datetime(19)  not_null binary
    public $passwd;                          // string(64)  not_null
    public $dispatch_port;                   // string(255)  not_null
    public $province;                        // string(255)  not_null
    public $country;                         // string(4)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Companies',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

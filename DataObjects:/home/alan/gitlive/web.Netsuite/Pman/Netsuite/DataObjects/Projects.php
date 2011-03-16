<?php
/**
 * Table Definition for Projects
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Projects extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Projects';                        // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $name;                            // string(254)  not_null
    public $remarks;                         // blob(65535)  not_null blob
    public $owner_id;                        // int(11)  
    public $code;                            // string(32)  not_null multiple_key
    public $active;                          // int(11)  
    public $type;                            // string(1)  not_null
    public $client_id;                       // int(11)  not_null
    public $team_id;                         // int(11)  not_null
    public $file_location;                   // string(254)  not_null
    public $open_date;                       // date(10)  binary
    public $open_by;                         // int(11)  not_null
    public $close_date;                      // date(10)  binary
    public $countries;                       // string(128)  not_null
    public $languages;                       // string(128)  not_null
    public $agency_id;                       // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Projects',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

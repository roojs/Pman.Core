<?php
/**
 * Table Definition for proftp_groups
 * 
 * -- this is needed for proftp integratin, but it's not used.. AFAIK
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Proftp_groups extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'proftp_groups';                   // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $grpid;                           // int(11)  not_null multiple_key
    public $grpname;                         // string(32)  not_null
    public $grpmembers;                      // blob(65535)  blob

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

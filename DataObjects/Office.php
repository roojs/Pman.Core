<?php
/**
 * Table Definition for Office
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Office extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Office';                          // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $company_id;                      // int(11)  not_null
    public $name;                            // string(64)  not_null
    public $address;                         // blob(65535)  not_null blob
    public $address2;                         // blob(65535)  not_null blob
    public $address3;                         // blob(65535)  not_null blob 
    public $phone;                           // string(32)  not_null
    public $fax;                             // string(32)  not_null
    public $email;                           // string(128)  not_null
    public $role;                            // string(32)  not_null

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function autoJoin()
    {
        
    }
    function toEventString() {
        return $this->name;
    }
    /**
     * check who is trying to access this. false == access denied..
     */
    function checkPerm($lvl, $au) 
    {
        return $au->hasPerm("Core.Offices", $lvl);    
    } 
}
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
    public $country;                         // string(4)
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    function applyFilters($q, $au)
    {
        //DB_DataObject::debugLevel(1);
        $tn  = $this->tableName();
        $this->selectAdd("   i18n_translate('c' , {$tn}.country, 'en') as office_id_country_name");
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
    
    function company()
    {
        $c = DB_DataObject::Factory('Companies');
        $c->get($this->company_id);
        return $c;
        
    }
    
    function beforeDelete($dependants_array, $roo)
    {
        if (count($dependants_array != 1)) {
            $roo->jerr("more than one person..");
            return true; // standard error message.
        }
         $p = DB_DAtaObject::Factory('Person');
        if (!is_a($dependants_array[0], get_class($p))) {
            $roo->jerr("dep is not a person..");
            return true;
        }
        $p = $dependants_array[0];
        $old = clone($p);
        $p->office_id = 0;
        $p->update($old);
        $roo->jerr("updated person?..");
        return true;
    }
    
    
}
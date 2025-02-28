<?php
/**
 * Table Definition for Office
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_office extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_office';                          // table name
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
        $c = DB_DataObject::Factory('core_company');
        $c->get($this->company_id);
        return $c;
        
    }
    
    function beforeDelete($dependants_array, $roo)
    {
        if (count($dependants_array) != 1) {
            //$roo->jerr("more than one dependant type....");
            return true; // standard error message.
        }
        
        $p = DB_DAtaObject::Factory('core_person');
        
        if (!is_a($dependants_array[0], get_class($p))) {
            $roo->jerr("dependancy is not a person..- ". get_class($dependants_array[0]));
            return true;
        }
        
        $p->office_id = $this->id;
        if ($p->count() > 1) {
            return true; // default err..
        }
        $p->find(true);
        $pp = clone($p);
        $p->office_id = 0;
        $p->update($pp);
        return true;
    }
    
    
}
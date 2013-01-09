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
        
        if(!empty($q['_join_country_with_person'])){
            $tn = $this->tableName();
            $this->selectAdd();
            $this->selectAdd(" i18n_translate('c' , Office.country, 'en') as office_id_country_name");
            $this->_extra_col = array('office_country_name');
            $this->query('_distinct=office_id_country&cols=office_id_country,office_id_country_name');
            //then the query should be _distinct=office_id_country&cols=office_id_country,office_id_country_name
//            $p = DB_DataObject::factory('Person');
//            $p->whereAdd("office_id > 0");
//            $p->selectAdd();
//            $p->selectAdd("DISTINCT(office_id) as office_id");
//            $officeIds = $p->fetchAll('office_id');
//            $this->whereAddIn($tn.'.id', $officeIds, 'INT');
//            $this->selectAdd();
//            $this->selectAdd("DISTINCT({$tn}.country) as country");
//            $this->joinAddCountryWithPerson();
        }
    }
    
    function joinAddCountryWithPerson()
    {
        $this->_join .= "
            LEFT JOIN
                i18n AS join_country
            ON
                (join_country.lkey = Office.country AND join_country.inlang = 'en')
        ";
        $item = DB_DataObject::Factory('I18n');
        $this->selectAs($item, 'country_id_%s', 'join_country');
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
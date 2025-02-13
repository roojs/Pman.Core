<?php

require_once 'Pman/Core/Auth.php';

/***
* 
* Authentication - Has Company Owner
*
* (was ?check_owner_company)
*
* Check if there is an owner company (for new systems)
*
* GET only 
* 
*/



class Pman_Core_Auth_HasCompanyOwner extends Pman_Core_Auth
{ 
    function post($v, $opts=array())
    {
        
        $core_company = DB_DataObject::factory('core_company');
        $core_company->comptype = 'OWNER';
        $this->jok($core_company->count());
    }
     
} 
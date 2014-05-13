<?php
/**
 * Table Definition for core_geoip_city
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_geoip_city extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_geoip_city';                       // table name
    public $id;
    public $name;
    public $country_id;
    public $division_id;
    public $postal_code;
    public $metro_code;
    public $time_zone;
    
        
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    
}

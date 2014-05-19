<?php
/**
 * Table Definition for core_geoip_country
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_geoip_country extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_geoip_country';                       // table name
    public $id;
    public $continent_id;
    public $code;
    public $name;

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function applyFilters($q, $au, $roo)
    {
        
    }
    
}

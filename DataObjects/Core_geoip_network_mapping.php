<?php
/**
 * Table Definition for core_geoip_network_mapping
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_geoip_network_mapping extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_geoip_network_mapping';                       // table name
    public $id;
    public $start_ip;
    public $mask_length;
    public $city_id;
    
        
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    
}

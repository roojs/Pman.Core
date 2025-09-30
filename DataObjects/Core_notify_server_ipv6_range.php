<?php
/**
 * Table Definition for core_notify_server_ipv6_range
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_notify_server_ipv6_range extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_notify_server_ipv6_range';    // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $server_id;                       // int(11)  not_null
    public $ipv6_range_from;                 // varchar(255)  not_null
    public $ipv6_range_to;                   // varchar(255)  not_null
    public $ipv6_ptr;                        // varchar(255)  not_null
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    function findRange($ipv6_addr) 
    {
        if (empty($ipv6_addr)) {
            return false;
        }
        $ipv6_addr = $this->escape($ipv6_addr);
        $cnsir = DB_DataObject::factory('core_notify_server_ipv6_range');
        $cnsir->whereAdd("INET6_ATON('{$ipv6_addr}') >= INET6_ATON(ipv6_range_from)");
        $cnsir->whereAdd("INET6_ATON('{$ipv6_addr}') <= INET6_ATON(ipv6_range_to)");
        if($cnsir->find(true)) {
            return $cnsir;
        }
        return $false;
    }
}

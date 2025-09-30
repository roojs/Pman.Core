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
        
        // Convert IPv6 address to binary representation for comparison
        $target_addr = inet_pton($ipv6_addr);
        if ($target_addr === false) {
            return false; // Invalid IPv6 address
        }
        
        // Get all IPv6 ranges
        $ranges = DB_DataObject::factory('core_notify_server_ipv6_range');
        $ranges->find();
        
        while ($ranges->fetch()) {
            // Convert range boundaries to binary
            $range_from = inet_pton($ranges->ipv6_range_from);
            $range_to = inet_pton($ranges->ipv6_range_to);
            
            if ($range_from === false || $range_to === false) {
                continue; // Skip invalid ranges
            }
            
            // Check if the target address is within the range
            if ($target_addr >= $range_from && $target_addr <= $range_to) {
                // Found matching range - copy the range data to this object
                $this->id = $ranges->id;
                $this->server_id = $ranges->server_id;
                $this->ipv6_range_from = $ranges->ipv6_range_from;
                $this->ipv6_range_to = $ranges->ipv6_range_to;
                $this->ipv6_ptr = $ranges->ipv6_ptr;
                return true;
            }
        }
        
        return false; // No matching range found
    }
}

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
        
        // Use SQL INET6_ATON to find the matching range
        $db = $this->getDatabaseConnection();
        $ipv6_addr_escaped = $db->escape($ipv6_addr);
        
        $sql = "
            SELECT id, server_id, ipv6_range_from, ipv6_range_to, ipv6_ptr
            FROM core_notify_server_ipv6_range
            WHERE INET6_ATON('$ipv6_addr_escaped') >= INET6_ATON(ipv6_range_from)
            AND INET6_ATON('$ipv6_addr_escaped') <= INET6_ATON(ipv6_range_to)
            LIMIT 1
        ";
        
        $result = $db->query($sql);
        if ($result && $row = $result->fetchRow()) {
            // Found matching range - populate this object
            $this->id = $row['id'];
            $this->server_id = $row['server_id'];
            $this->ipv6_range_from = $row['ipv6_range_from'];
            $this->ipv6_range_to = $row['ipv6_range_to'];
            $this->ipv6_ptr = $row['ipv6_ptr'];
            return true;
        }
        
        return false; // No matching range found
    }
}

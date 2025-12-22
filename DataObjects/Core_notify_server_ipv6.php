<?php
/**
 * Table Definition for core_notify_server_ipv6
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_notify_server_ipv6 extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_notify_server_ipv6';    // table name
    public $id;                              // int  not_null primary_key auto_increment
    public $ipv6_addr;                       // varchar(255)  not_null
    public $domain_id;                       // int  not_null
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    function applyFilters($q, $au, $roo)
    {
        if(!empty($q['search']['domain'])){
            $s = $this->escape($q['search']['domain']);
            $this->whereAdd("join_domain_id_id.domain like '%$s%'");
        }
    }
    
    /**
     * Find the server whose IPv6 range contains this record's ipv6_addr
     * 
     * @param string $poolname
     * @return Pman_Core_DataObjects_Core_notify_server|false
     */
    function findServerFromIpv6($poolname)
    {
        if (empty($this->ipv6_addr)) {
            return false;
        }
        
        // Get all active servers with IPv6 ranges defined
        $server = DB_DataObject::factory('core_notify_server');
        $server->whereAdd("
            ipv6_range_from != ''
            AND
            ipv6_range_to != ''
        ");
        $server->is_active = 1;
        $servers = $server->fetchAll();
        
        if (empty($servers)) {
            return false;
        }
        
        // Convert this record's IPv6 address to decimal for comparison
        $addrDecimal = $this->ipv6ToDecimal($this->ipv6_addr);
        if ($addrDecimal === false) {
            return false;
        }

        
        // Check each server's range
        foreach ($servers as $s) {
            $rangeFrom = $this->ipv6ToDecimal($s->ipv6_range_from);
            $rangeTo = $this->ipv6ToDecimal($s->ipv6_range_to);
            
            if ($rangeFrom === false || $rangeTo === false) {
                continue;
            }
            
            // Check if address is within range: rangeFrom <= addr <= rangeTo
            if (bccomp($addrDecimal, $rangeFrom) >= 0 && bccomp($addrDecimal, $rangeTo) <= 0) {
                // fitting poolname
                if($s->poolname == $poolname) {
                    return $s;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Convert ipv6 to decimal
     * If invalid ipv6 address, return false
     * 
     * @param string $ip
     * @return string|false
     */
    function ipv6ToDecimal($ip)
    {
        $binary = inet_pton($ip);
        if ($binary === false) {
            return false;
        }
        
        // Convert to hex string
        $hex = bin2hex($binary);
        
        // Convert hex to decimal using bcmath
        $decimal = '0';
        for ($i = 0; $i < strlen($hex); $i++) {
            $decimal = bcmul($decimal, '16');
            $decimal = bcadd($decimal, hexdec($hex[$i]));
        }
        
        return $decimal;
    }
}

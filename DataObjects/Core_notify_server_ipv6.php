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
    
    function beforeInsert($q, $roo)
    {
        // Validate required fields
        if (empty($this->ipv6_addr)) {
            $roo->jerr("IPv6 address is required");
        }
        
        if (empty($this->domain_id)) {
            $roo->jerr("Domain is required");
        }
        
        if (empty($this->allocation_reason)) {
            $roo->jerr("Allocate reason is required");
        }
        
        // Validate IPv6 address format
        if (filter_var($this->ipv6_addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
            $roo->jerr("Invalid IPv6 address format: {$this->ipv6_addr}");
        }
        
        // Check if IPv6 address is within any notify server's IPv6 range
        if (!$this->isInAnyServerRange()) {
            $roo->jerr("IPv6 address {$this->ipv6_addr} is not within any configured server IPv6 range");
        }
        
        // Check for duplicate ipv6_addr + domain_id combination
        $check = DB_DataObject::factory($this->tableName());
        $check->ipv6_addr = $this->ipv6_addr;
        $check->domain_id = $this->domain_id;
        
        if ($check->find(true)) {
            $roo->jerr("A record with this IPv6 address and domain already exists");
        }

        $this->allocation_reason = "Manual allocation: " . $this->allocation_reason;
        
        // Set seq before insert if domain_id or ipv6_addr already exists
        if ($this->needsUniqueSeq()) {
            $this->seq = $this->getNextSeq();
        }
    }
    
    /**
     * Check if domain_id or ipv6_addr already exists in the table
     * 
     * @return bool True if a unique seq is needed
     */
    function needsUniqueSeq()
    {
        $check_domain = DB_DataObject::factory($this->tableName());
        $check_domain->domain_id = $this->domain_id;
        if ($check_domain->count() > 0) {
            return true;
        }
        
        $check_ipv6 = DB_DataObject::factory($this->tableName());
        $check_ipv6->ipv6_addr = $this->ipv6_addr;
        if ($check_ipv6->count() > 0) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get the next seq value based on max(seq) + 1
     * 
     * @return int The next seq value
     */
    function getNextSeq()
    {
        $q = DB_DataObject::factory($this->tableName());
        $q->selectAdd();
        $q->selectAdd('MAX(seq) as max_seq');
        $q->find(true);
        
        return ($q->max_seq ?: 0) + 1;
    }
    
    /**
     * Check if the IPv6 address is within any notify server's IPv6 range
     * 
     * @return bool True if the address is within at least one server's range
     */
    function isInAnyServerRange()
    {
        if (empty($this->ipv6_addr)) {
            return false;
        }
        
        // Get all servers with IPv6 ranges defined
        $server = DB_DataObject::factory('core_notify_server');
        $server->whereAdd("
            ipv6_range_from != ''
            AND
            ipv6_range_to != ''
        ");
        $servers = $server->fetchAll();
        
        if (empty($servers)) {
            return false;
        }
        
        // Convert this record's IPv6 address to decimal for comparison
        $addrDecimal = $this->ipv6ToDecimal($this->ipv6_addr);
        if ($addrDecimal === false) {
            return false;
        }
        
        // Check if address is within any server's range
        foreach ($servers as $s) {
            $rangeFrom = $this->ipv6ToDecimal($s->ipv6_range_from);
            $rangeTo = $this->ipv6ToDecimal($s->ipv6_range_to);
            
            if ($rangeFrom === false || $rangeTo === false) {
                continue;
            }
            
            // Check if address is within range: rangeFrom <= addr <= rangeTo
            if (bccomp($addrDecimal, $rangeFrom) >= 0 && bccomp($addrDecimal, $rangeTo) <= 0) {
                return true;
            }
        }
        
        return false;
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

    /**
     * Get the list of IPv6 addresses configured for domains matching the MX record
     * 
     * Finds IPv6 addresses where the domain is a suffix of the MX record.
     * E.g., for MX 'roojs.mail.protection.outlook.com', matches domains like:
     * - 'roojs.mail.protection.outlook.com'
     * - 'mail.protection.outlook.com'
     * - 'protection.outlook.com'
     * - 'outlook.com'
     * 
     * Results are cached per MX to avoid repeated database queries.
     * 
     * @param string $mx The MX hostname
     * @return array Array of unique IPv6 addresses configured for matching domains
     */
    function getIpv6ForMx($mx)
    {
        static $cache = array();
        
        if (isset($cache[$mx])) {
            return $cache[$mx];
        }
        
        $ipv6_lookup = DB_DataObject::factory('core_notify_server_ipv6');
        $ipv6_lookup->autoJoin();
        $escaped_mx = $ipv6_lookup->escape($mx);
        $ipv6_lookup->whereAdd("'$escaped_mx' LIKE CONCAT('%', join_domain_id_id.domain)");
        $ipv6_lookup->has_reverse_ptr = 1;
        
        $outlook_ipv6_records = $ipv6_lookup->fetchAll();
        
        // Extract unique IPv6 addresses
        $cache[$mx] = array();
        foreach ($outlook_ipv6_records as $record) {
            if (!in_array($record->ipv6_addr, $cache[$mx])) {
                $cache[$mx][] = $record->ipv6_addr;
            }
        }
        
        return $cache[$mx];
    }

    /**
     * Check if an IPv6 address is configured for domains matching the MX record
     * 
     * @param string $ipv6_addr The IPv6 address to check
     * @param string $mx The MX hostname
     * @return bool True if this IPv6 is used by a matching domain
     */
    function isIpv6ForMx($ipv6_addr, $mx)
    {
        if (empty($ipv6_addr)) {
            return false;
        }
        
        $ipv6_list = $this->getIpv6ForMx($mx);
        
        return in_array($ipv6_addr, $ipv6_list);
    }
}

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

    /**
     * Convert IPv6 string to binary format for database storage
     * 
     * @param string $ipv6_str IPv6 address as string (e.g., "2001:db8::1")
     * @return string Binary representation (16 bytes)
     */
    static function ipv6ToBinary($ipv6_str)
    {
        return inet_pton($ipv6_str);
    }
    
    /**
     * Convert binary IPv6 to string format for display
     * 
     * @param string $ipv6_bin Binary IPv6 address (16 bytes)
     * @return string|false IPv6 address as string, or false on failure
     */
    static function binaryToIpv6($ipv6_bin)
    {
        if (empty($ipv6_bin) || $ipv6_bin === "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00") {
            return false;
        }
        return inet_ntop($ipv6_bin);
    }
    
    /**
     * Get the IPv6 address as a string (converts from binary storage)
     * 
     * @return string|false IPv6 address as string
     */
    function getIpv6Addr()
    {
        return self::binaryToIpv6($this->ipv6_addr);
    }
    
    /**
     * Set the IPv6 address from a string (converts to binary for storage)
     * 
     * @param string $ipv6_str IPv6 address as string
     * @return bool True on success, false on invalid IPv6
     */
    function setIpv6Addr($ipv6_str)
    {
        if (filter_var($ipv6_str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
            return false;
        }
        $this->ipv6_addr = self::ipv6ToBinary($ipv6_str);
        return true;
    }

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
        
        // Get IPv6 as string for validation (it may already be binary or still string)
        $ipv6_str = $this->getIpv6AddrForValidation();
        
        // Validate IPv6 address format
        if (filter_var($ipv6_str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
            $roo->jerr("Invalid IPv6 address format: {$ipv6_str}");
        }
        
        // Check if IPv6 address is within any notify server's IPv6 range
        if (!$this->isInAnyServerRange($ipv6_str)) {
            $roo->jerr("IPv6 address {$ipv6_str} is not within any configured server IPv6 range");
        }
        
        // Convert to binary for storage and duplicate check
        $ipv6_bin = self::ipv6ToBinary($ipv6_str);
        
        // Check for duplicate ipv6_addr + domain_id combination
        $check = DB_DataObject::factory($this->tableName());
        $check->ipv6_addr = $ipv6_bin;
        $check->domain_id = $this->domain_id;
        
        if ($check->find(true)) {
            $roo->jerr("A record with this IPv6 address and domain already exists");
        }

        $this->allocation_reason = "Manual allocation: " . $this->allocation_reason;
        
        // Convert to binary for storage
        $this->ipv6_addr = $ipv6_bin;
        
        // Set seq before insert if domain_id or ipv6_addr already exists
        if ($this->needsUniqueSeq()) {
            $this->seq = $this->getNextSeq();
        }
    }
    
    /**
     * Get IPv6 address as string for validation
     * Handles both string input (from form) and binary (from DB)
     * 
     * @return string IPv6 address as string
     */
    function getIpv6AddrForValidation()
    {
        // If it's already a valid IPv6 string, return as-is
        if (filter_var($this->ipv6_addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            return $this->ipv6_addr;
        }
        // Try to convert from binary
        $str = self::binaryToIpv6($this->ipv6_addr);
        return $str ?: $this->ipv6_addr;
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
        
        // ipv6_addr should already be in binary format at this point
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
     * @param string $ipv6_str IPv6 address as string (optional, uses $this->ipv6_addr if not provided)
     * @return bool True if the address is within at least one server's range
     */
    function isInAnyServerRange($ipv6_str = null)
    {
        if ($ipv6_str === null) {
            $ipv6_str = $this->getIpv6Addr();
        }
        
        if (empty($ipv6_str)) {
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
        $addrDecimal = $this->ipv6ToDecimal($ipv6_str);
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
        $ipv6_str = $this->getIpv6Addr();
        
        if (empty($ipv6_str)) {
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
        $addrDecimal = $this->ipv6ToDecimal($ipv6_str);
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

        // Extract unique IPv6 addresses (convert binary to string)
        $cache[$mx] = array();
        foreach ($ipv6_lookup->fetchAll() as $record) {
            $ipv6_str = $record->getIpv6Addr();
            if ($ipv6_str && !in_array($ipv6_str, $cache[$mx])) {
                $cache[$mx][] = $ipv6_str;
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

    /**
     * Find the least-used IPv6 address configured for domains matching the MX record
     * 
     * Looks for IPv6 addresses mapped to domains that are suffixes of the MX record
     * and returns the one with the fewest domain mappings.
     * 
     * @param string $mx The MX hostname
     * @return string|false The IPv6 address with least mappings, or false if none found
     */
    function getLeastUsedIpv6ForMx($mx)
    {
        $ipv6_list = $this->getIpv6ForMx($mx);
        
        if (empty($ipv6_list)) {
            return false;
        }
        
        // Convert string IPv6 addresses to binary for SQL comparison
        $binary_list = array();
        foreach ($ipv6_list as $ipv6_str) {
            $binary_list[] = self::ipv6ToBinary($ipv6_str);
        }
        
        // Single query to find the IPv6 with least domain mappings
        $q = DB_DataObject::factory('core_notify_server_ipv6');
        
        // Build IN clause with hex values for binary comparison
        $hex_values = array();
        foreach ($binary_list as $bin) {
            $hex_values[] = "0x" . bin2hex($bin);
        }
        $in_clause = implode(",", $hex_values);
        
        $q->selectAdd();
        $q->selectAdd('ipv6_addr, COUNT(*) as domain_count');
        $q->whereAdd("ipv6_addr IN ($in_clause)");
        $q->groupBy('ipv6_addr');
        $q->orderBy('domain_count ASC');
        $q->limit(1);
        
        if ($q->find(true)) {
            // Return as string
            return $q->getIpv6Addr();
        }
        
        return false;
    }

    /**
     * Find or create an IPv6 address mapping for a domain based on a MX record
     * 
     * Looks for pre-configured IPv6 addresses for domains matching the MX record,
     * finds the one with the least domains mapped, and creates a mapping
     * for the current domain.
     * 
     * @param string $mx The MX hostname
     * @param object $core_domain The recipient's domain object
     * @return object|false The IPv6 record to use, or false if none available
     */
    function findOrCreateIpv6ForMx($mx, $core_domain)
    {
        if (empty($core_domain) || empty($core_domain->id)) {
            return false;
        }
        
        // Find the least-used IPv6 address for domains matching this MX (returns string)
        $least_used_ipv6_str = $this->getLeastUsedIpv6ForMx($mx);
        
        if (empty($least_used_ipv6_str)) {
            return false;
        }
        
        // Check if this domain already has an IPv6 mapping
        $existing = DB_DataObject::factory('core_notify_server_ipv6');
        $existing->domain_id = $core_domain->id;
        if ($existing->find(true)) {
            // Check if existing IPv6 is one of the matching IPv6 addresses for this MX
            $existing_ipv6_str = $existing->getIpv6Addr();
            if ($this->isIpv6ForMx($existing_ipv6_str, $mx)) {
                echo "IPv6: Using existing IPv6 mapping - domain: {$core_domain->domain}, ipv6: {$existing_ipv6_str}\n";
                return $existing;
            }
            
            // Existing IPv6 is not one of the matching IPv6 addresses for this MX, update to the least-used IPv6
            $old = clone($existing);
            $existing->ipv6_addr = self::ipv6ToBinary($least_used_ipv6_str);
            if($existing->needsUniqueSeq()) {
                $existing->seq = $existing->getNextSeq();
            }
            $existing->update($old);
            
            echo "IPv6: Updated to IPv6 mapping - domain: {$core_domain->domain}, ipv6: $least_used_ipv6_str\n";
            return $existing;
        }
        
        // Create a new mapping for this domain with the least-used IPv6
        $new_mapping = DB_DataObject::factory('core_notify_server_ipv6');
        $new_mapping->ipv6_addr = self::ipv6ToBinary($least_used_ipv6_str);
        $new_mapping->domain_id = $core_domain->id;
        $new_mapping->allocation_reason = "Auto-allocated for MX: $mx";
        
        // Set seq before insert if domain_id or ipv6_addr already exists
        if ($new_mapping->needsUniqueSeq()) {
            $new_mapping->seq = $new_mapping->getNextSeq();
        }
        
        $new_mapping->insert();
        
        echo "IPv6: Created new IPv6 mapping - domain: {$core_domain->domain}, ipv6: $least_used_ipv6_str\n";
        
        return $new_mapping;
    }
}

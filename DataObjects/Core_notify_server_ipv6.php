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
     * Convert ipv6 to decimal
     * If invalid ipv6 address, return false
     * 
     * @param string $ip
     * @return string|false
     */
    static function ipv6ToDecimal($ip)
    {
        if (empty($ip)) {
            return false;
        }
        
        $binary = @inet_pton($ip);
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
        // Add string version of binary IPv6 field for the interface
        $this->selectAdd("INET6_NTOA(ipv6_addr) as ipv6_addr_str");
        
        if(!empty($q['search']['domain'])){
            $s = $this->escape($q['search']['domain']);
            $this->whereAdd("join_domain_id_id.domain like '%$s%'");
        }
    }
    
    function beforeInsert($q, $roo)
    {   
        // Validate required fields
        if (empty($q['ipv6_addr_str'])) {
            $roo->jerr("IPv6 address is required");
        }
        
        if (empty($this->domain_id)) {
            $roo->jerr("Domain is required");
        }
        
        if (empty($this->allocation_reason)) {
            $roo->jerr("Allocate reason is required");
        }
        
        // Validate IPv6 address format
        if (filter_var($q['ipv6_addr_str'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
            $roo->jerr("Invalid IPv6 address format: {$q['ipv6_addr_str']}");
        }
        
        // Convert to binary for storage and duplicate check
        $ipv6_bin = self::ipv6ToBinary($q['ipv6_addr_str']);
        
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

        // Check if IPv6 address is within any notify server's IPv6 range
        if (!$this->isInAnyServerRange()) {
            $roo->jerr("IPv6 address {$q['ipv6_addr_str']} is not within any configured server IPv6 range");
        }
        
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
     * @return bool True if the address is within at least one server's range
     */
    function isInAnyServerRange()
    {
        $ipv6_bin = $this->ipv6_addr;
        
        // Validate it can be converted to a valid IPv6 string
        $ipv6_str = @inet_ntop($ipv6_bin);
        if ($ipv6_str === false) {
            return false;
        }
        
        // Use MySQL BETWEEN to check if address is in any server's range
        $ipv6_hex = bin2hex($ipv6_bin);
        $server = DB_DataObject::factory('core_notify_server');
        $server->selectAdd();
        $server->selectAdd("id");
        $server->whereAdd("
            ipv6_range_from != 0x0
            AND
            ipv6_range_to != 0x0
            AND
            0x{$ipv6_hex} BETWEEN ipv6_range_from AND ipv6_range_to
        ");
        $server->limit(1);
        
        return $server->find(true) ? true : false;
    }
    
    /**
     * Find the server whose IPv6 range contains this record's ipv6_addr
     * 
     * @param string $poolname
     * @return Pman_Core_DataObjects_Core_notify_server|false
     */
    function findServerFromIpv6($poolname)
    {
        $ipv6_bin = $this->ipv6_addr;
        
        // Validate it can be converted to a valid IPv6 string
        $ipv6_str = @inet_ntop($ipv6_bin);
        if ($ipv6_str === false) {
            return false;
        }
        
        // Use MySQL BETWEEN to find server with matching range and poolname
        $ipv6_hex = bin2hex($ipv6_bin);
        $server = DB_DataObject::factory('core_notify_server');
        $poolname_escaped = $server->escape($poolname);
        $server->whereAdd("
            ipv6_range_from != 0x0
            AND
            ipv6_range_to != 0x0
            AND
            0x{$ipv6_hex} BETWEEN ipv6_range_from AND ipv6_range_to
            AND
            poolname = '{$poolname_escaped}'
        ");
        $server->is_active = 1;
        $server->limit(1);
        
        if ($server->find(true)) {
            return $server;
        }
        
        return false;
    }

    /**
     * Get the list of IPv6 addresses configured for domains matching the MX record and with reverse pointer
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
     * Check if an IPv6 address is configured for domains matching the MX record and has reverse pointer
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
     * Find the least-used IPv6 address configured for domains matching the MX record and has reverse pointer
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
     * Create a new IPv6 mapping using the least-used IPv6 with reverse pointer for the given MX
     * 
     * @param string $mx The MX hostname
     * @param int $domain_id The domain ID to create the mapping for
     * @return object|false Returns the new record, or false if no IPv6 available for MX
     */
    function createIpv6ForMx($mx, $domain_id)
    {
        // Find the least-used IPv6 for this MX
        $least_used_ipv6_str = $this->getLeastUsedIpv6ForMx($mx);
        
        if (empty($least_used_ipv6_str)) {
            return false;
        }
        
        // Create a new mapping
        $this->ipv6_addr = self::ipv6ToBinary($least_used_ipv6_str);
        $this->domain_id = $domain_id;
        $this->allocation_reason = "Auto-allocated for MX: $mx";
        
        if ($this->needsUniqueSeq()) {
            $this->seq = $this->getNextSeq();
        }
        
        $this->insert();
        
        return $this;
    }
}

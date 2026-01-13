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
        
        // Check for duplicate ipv6_addr + domain_id combination
        $check = DB_DataObject::factory($this->tableName());
        $check->whereAdd("ipv6_addr = INET6_ATON('" . $check->escape($q['ipv6_addr_str']) . "')");
        $check->domain_id = $this->domain_id;
        
        if ($check->find(true)) {
            $roo->jerr("A record with this IPv6 address and domain already exists");
        }

        $this->allocation_reason = "Manual allocation: " . $this->allocation_reason;

        // Check if IPv6 address is within any notify server's IPv6 range
        if (!$this->isInAnyServerRange($q['ipv6_addr_str'])) {
            $roo->jerr("IPv6 address {$q['ipv6_addr_str']} is not within any configured server IPv6 range");
        }
        
        // Set seq before insert if domain_id or ipv6_addr already exists
        if ($this->needsUniqueSeq($q['ipv6_addr_str'])) {
            $this->seq = $this->getNextSeq();
        }

        // Convert to binary for storage using MySQL
        $this->ipv6_addr = $this->sqlValue("INET6_ATON('" . $this->escape($q['ipv6_addr_str']) . "')");
    }
    
    /**
     * Check if domain_id or ipv6_addr already exists in the table
     * 
     * @param string $ipv6_addr_str The IPv6 address to check
     * @return bool True if a unique seq is needed
     */
    function needsUniqueSeq($ipv6_addr_str)
    {
        $check_domain = DB_DataObject::factory($this->tableName());
        $check_domain->domain_id = $this->domain_id;
        if ($check_domain->count() > 0) {
            return true;
        }
        
        $check_ipv6 = DB_DataObject::factory($this->tableName());
        $check_ipv6->whereAdd("ipv6_addr = INET6_ATON('" . $this->escape($ipv6_addr_str) . "')");
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
     * @param string $ipv6_addr_str The IPv6 address to check
     * @return bool True if the address is within at least one server's range
     */
    function isInAnyServerRange($ipv6_addr_str)
    {
        $server = DB_DataObject::factory('core_notify_server');
        $server->selectAdd();
        $server->selectAdd("id");
        $server->whereAdd("
            ipv6_range_from != 0x0
            AND
            ipv6_range_to != 0x0
            AND
            INET6_ATON('" . $this->escape($ipv6_addr_str) . "') BETWEEN ipv6_range_from AND ipv6_range_to
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
        // if ipv6_addr_str is not available, return false
        if(empty($this->ipv6_addr_str)) {
            return false;
        }

        $server = DB_DataObject::factory('core_notify_server');
        $poolname_escaped = $server->escape($poolname);

        $server->whereAdd("
            ipv6_range_from != 0x0
            AND
            ipv6_range_to != 0x0
            AND
            INET6_ATON('" . $this->escape($this->ipv6_addr_str) . "') BETWEEN ipv6_range_from AND ipv6_range_to
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
        $ipv6_lookup->selectAdd("INET6_NTOA(ipv6_addr) as ipv6_addr_str");
        $escaped_mx = $ipv6_lookup->escape($mx);
        $ipv6_lookup->whereAdd("'$escaped_mx' LIKE CONCAT('%', join_domain_id_id.domain)");
        $ipv6_lookup->has_reverse_ptr = 1;

        // Extract unique IPv6 addresses
        $cache[$mx] = array();
        foreach ($ipv6_lookup->fetchAll() as $record) {
            if ($record->ipv6_addr_str && !in_array($record->ipv6_addr_str, $cache[$mx])) {
                $cache[$mx][] = $record->ipv6_addr_str;
            }
        }
        
        return $cache[$mx];
    }

    /**
     * Find the least-used IPv6 address configured for domains matching the MX record and with reverse pointer
     * 
     * Looks for IPv6 addresses mapped to domains that are suffixes of the MX record
     * and returns the one with the fewest domain mappings.
     * 
     * @param string $mx The MX hostname
     * @return int|false The IPv6 address with least mappings, or false if none found
     */
    function getLeastUsedIpv6ForMx($mx)
    {
        $ipv6_list = $this->getIpv6ForMx($mx);
        
        if (empty($ipv6_list)) {
            return false;
        }
        
        // Single query to find the IPv6 with least domain mappings
        $q = DB_DataObject::factory('core_notify_server_ipv6');
        
        // Build IN clause using INET6_ATON for each IPv6 string
        $in_values = array();
        foreach ($ipv6_list as $ipv6_str) {
            $in_values[] = "INET6_ATON('" . $q->escape($ipv6_str) . "')";
        }
        $in_clause = implode(",", $in_values);
        
        $q->selectAdd();
        $q->selectAdd('INET6_NTOA(ipv6_addr) as ipv6_addr_str, COUNT(*) as domain_count');
        $q->whereAdd("ipv6_addr IN ($in_clause)");
        $q->groupBy('ipv6_addr');
        $q->orderBy('domain_count ASC');
        $q->limit(1);
        
        if ($q->find(true)) {
            return $q->ipv6_addr_str;
        }
        
        return false;
    }

    /**
     * Find existing or create new IPv6 mapping using the least-used IPv6 with reverse pointer for the given MX
     * 
     * @param string $mx The MX hostname
     * @param int $domain_id The domain ID to find/create the mapping for
     * @param string $allocation_reason The reason why the IPv6 was allocated (used only for new records)
     * @return object|false Returns the existing or new record, or false if no IPv6 available for MX
     */
    function findOrCreateIpv6ForMx($mx, $domain_id, $allocation_reason)
    {
        // Check if there's an existing IPv6 mapping for this domain
        $existing = DB_DataObject::factory('core_notify_server_ipv6');
        $existing->selectAdd();
        $existing->selectAdd('*, INET6_NTOA(ipv6_addr) as ipv6_addr_str');
        $existing->domain_id = $domain_id;
        if ($existing->find(true)) {
            return $existing;
        }
        
        // Find the least-used IPv6 for this MX
        $least_used_ipv6_str = $this->getLeastUsedIpv6ForMx($mx);
        
        if (empty($least_used_ipv6_str)) {
            return false;
        }
        
        // Create a new mapping
        $cnsi = DB_DataObject::factory('core_notify_server_ipv6');
        $cnsi->ipv6_addr = $cnsi->sqlValue("INET6_ATON('" . $cnsi->escape($least_used_ipv6_str) . "')");
        $cnsi->domain_id = $domain_id;
        $cnsi->allocation_reason = $allocation_reason;
        
        if ($cnsi->needsUniqueSeq($least_used_ipv6_str)) {
            $cnsi->seq = $this->getNextSeq();
        }

        $cnsi->insert();

        // make sure the ipv6_addr_str is available
        $cnsi2 = DB_DataObject::factory('core_notify_server_ipv6');
        $cnsi2->selectAdd("INET6_NTOA(ipv6_addr) as ipv6_addr_str");
        $cnsi2->get($cnsi->id);
        
        return $cnsi2;
    }   
}

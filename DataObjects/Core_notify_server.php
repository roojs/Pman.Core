<?php
/**
 * Table Definition for core_notify_server
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_notify_server extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_notify_server';    // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $hostname;
    public $helo;
    public $poolname;
    public $is_active;
    public $last_send;
    
    function  applyFilters($q, $au, $roo)
    {
        // Add string versions of binary IPv6 fields for the interface
        $this->selectAdd("INET6_NTOA(ipv6_range_from) as ipv6_range_from_str");
        $this->selectAdd("INET6_NTOA(ipv6_range_to) as ipv6_range_to_str");
        
        if (isset($q['_with_queue_size'])) {
            $this->addQueueSize();
        }
    }

    function beforeUpdate($old, $q, $roo)
    {
        // if any of the ipv6 fields is set, make sure all of them are set
        if(
            !empty($q['ipv6_range_from_str'])
            ||
            !empty($q['ipv6_range_to_str'])
            ||
            !empty($q['ipv6_ptr'])
            ||
            !empty($q['ipv6_sender_id'])
        ) {
            if(empty($q['ipv6_range_from_str'])) {
                $roo->jerr("IPv6 range from is required");
            }
            if(filter_var($q['ipv6_range_from_str'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
                $roo->jerr("IPv6 range from is not a valid IPv6 address");
            }
            $ipv6_range_from = DB_DataObject::factory('core_notify_server_ipv6')->ipv6ToBinary($q['ipv6_range_from_str']);
            if($ipv6_range_from === false) {
                $roo->jerr("IPv6 range from is not a valid IPv6 address");
            }
            if(empty($q['ipv6_range_to_str'])) {
                $roo->jerr("IPv6 range to is required");
            }
            if(filter_var($q['ipv6_range_to_str'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
                $roo->jerr("IPv6 range to is not a valid IPv6 address");
            }
            $ipv6_range_to = DB_DataObject::factory('core_notify_server_ipv6')->ipv6ToBinary($q['ipv6_range_to_str']);
            if($ipv6_range_to === false) {
                $roo->jerr("IPv6 range to is not a valid IPv6 address");
            }
            if(empty($q['ipv6_ptr'])) {
                $roo->jerr("IPv6 ptr is required");
            }
            if(empty($q['ipv6_sender_id'])) {
                $roo->jerr("IPv6 sender is required");
            }
            
            // Validate that range_to is not smaller than range_from
            $cnsi = DB_DataObject::factory('core_notify_server_ipv6');
            $fromDecimal = $cnsi->ipv6ToDecimal($q['ipv6_range_from_str']);
            $toDecimal = $cnsi->ipv6ToDecimal($q['ipv6_range_to_str']);
            if (bccomp($toDecimal, $fromDecimal) < 0) {
                $roo->jerr("IPv6 range to must be greater than or equal to range from");
            }
            
            // Convert string to binary for storage
            $this->ipv6_range_from = $ipv6_range_from;
            $this->ipv6_range_to = $ipv6_range_to;
        }
    }
    
    
    function addQueueSize()
    {
        // look for database links for server_id (which should find core_notify + others..)
        $cn = get_class(DB_DataObject::factory('core_notify'));
        $tables = array();
        foreach($this->databaseLinks() as $tbl => $kv) {
            foreach($kv as $k=>$v) {
                if ($v != 'core_notify_server:id') {
                    continue;
                }
                
                $test = DB_DAtaObject::factory($tbl);
                if (!is_a($test, $cn)) {
                    break;
                }
                $tables[] = $tbl;
            }
        }
        if (empty($tables)) {
            die("OOPS - no tables for notify_server references");
        }
        $totals = array();
        foreach($tables as $t) {
            $totals[] = "
                COALESCE((SELECT
                    count(id)
                FROM
                    $t
                WHERE
                    server_id = core_notify_server.id
                AND
                    sent < '1970-01-01' OR sent IS NULL
                 and
                        event_id = 0
                ),0)
            ";
        }
        $this->selectAdd("(" . implode(" + ", $totals) . ") as in_queue ");
        
        
        
        
        
    }
    
    
    // most services should call this first..
    
    /**
     * Get current notify server
     * 
     * @param object $roo Normally a notify object, but could also be a roo object
     * @param bool $force Force fallback to any server if hostname not found
     * @param string|false $poolname Pool name to use. Required when $roo is a roo object (which doesn't have poolname property)
     * @return object Core_notify_server instance
     */
    function getCurrent($roo, $force = false, $poolname = false)
    {
        static $current = false;;
        
        if ($current !== false) {
            return $current;
        }
        
        $ns = DB_DataObject::factory('core_notify_server');
        if (!$ns->count()) {
            $ns->id = 0;
            return $ns;
        }
        
        
        $ns->poolname = ($poolname !== false) ? $poolname : $roo->poolname;
        $ns->is_active = 1;
        $ns->hostname = gethostbyaddr("127.0.1.1");
        $ns->limit(1);
        if (strlen($ns->hostname) && $ns->find(true)) {
            $current = $ns;
            return $ns;
        }
        if (!$force) {
            $roo->jerr("Server not found for this server hostname 127.0.1.1 - {$ns->hostname} in core_notify_server" );
        }
        // fallback to any server - if we are using force. (this is so helo will work...)
        
        $ns = DB_DataObject::factory('core_notify_server');
        //$ns->is_active = 1; // we allow non active servers if force is used
        $ns->hostname = gethostbyaddr("127.0.1.1");
        if (!strlen($ns->hostname) ||  !$ns->find(true)) {
            $roo->jerr("Server not found for this server hostname 127.0.1.1 - {$ns->hostname} in core_notify_server" );
        }
        
        $current = $ns;
        return $ns;
    }
    
    
    function isFirstServer()
    {
        if (!$this->id) {
            return true;
        }
        
        $servers = $this->availableServers();
        if (empty($servers)) {
            return false;
        }
        // only run this on the first server...
        return $this->id == $servers[0]->id;
    }
    
    
    // called on current server.
    function assignQueues($notify)
    {
        if (!$this->id) {
            return true;
        }
        
        $servers = $this->availableServers();
        $ids = array();
        $up = array();
        foreach($servers as $s) {
            $ids[] = $s->id;
        }
        
        
        if (empty($ids)) {
            $notify->jerr("no configured servers in core_notify_server for poolname = {$notify->poolname}");
            
        }
         
        // only run this on the first server...
        if ($this->id != $ids[0]) {
            // return; 
        }
        
        // First, assign servers based on IPv6 domain assignments
        $assignedIPv6Ids = $this->assignQueuesByIPv6Domain($notify);
        die('test2');
        foreach($ids as $rn) {
            $up[$rn]  = array();
        }
        
        $num_servers = count($ids);
        
        if ($num_servers == 1) {
            $p = DB_DataObject::factory($notify->table);
            $p->query("
                UPDATE
                    {$notify->table}
                SET
                    server_id = {$ids[0]}
                WHERE
                    sent < '2000-01-01'
                    and
                    event_id = 0
                    and
                    act_start < NOW() +  INTERVAL 3 HOUR 
                    and
                    server_id != {$ids[0]}
                    " . (!empty($assignedIPv6Ids) ? "and id NOT IN (" . implode(",", $assignedIPv6Ids) . ")" : "") . "
            ");
            return;
        }
        
        
        
        $p = DB_DataObject::factory($notify->table);
        $p->whereAdd("
                sent < '2000-01-01'
                and
                event_id = 0
                and
                act_start < NOW() +  INTERVAL 3 HOUR 
                and
                server_id NOT IN (" . implode(",", $ids) . ")
                " . (!empty($assignedIPv6Ids) ? "and id NOT IN (" . implode(",", $assignedIPv6Ids) . ")" : "") . "
        ");
        $p->orderBy('act_when asc'); //?
        $total_add = $p->count();
        if ($total_add < 1) {
            return;
        }
        
        $to_add = $p->fetchAll('id');
        
        $p = DB_DataObject::factory($notify->table);
        $p->whereAdd("
                sent < '2000-01-01'
                and
                event_id = 0
        
                and
                server_id IN (" . implode(",", $ids) . ")
        ");
        $p->selectAdd();
        $p->selectAdd('server_id, count(id) as  n');
        $p->groupBy('server_id');
        $in_q = $p->fetchAll('server_id', 'n');
        
        // if queue is empty it will not get allocated anything.
        foreach($ids as $sid) {
            if (!isset($in_q[$sid])) {
                $in_q[$sid] = 0;
            }
        }

        $totalq = 0;
        foreach($in_q as $sid => $n) {
            $totalq += $n;
        }
        
        
        // new average queue
        $target_len = floor(  ($totalq + $total_add) / $num_servers );
        
        foreach($in_q as $sid => $cq) {
            if ( $cq > $target_len) {
                continue;
            }
            $up[ $sid ] = array_splice($to_add, 0, $target_len - $cq);
        }

        
        // add the reminder evently
        foreach($to_add as $n=>$i) {
            
            $up[  $ids[$n % $num_servers] ][] = $i;
        }
        
        // distribution needs to go to ones that have the shortest queues. - so to balance out the queues
        
         
        
        foreach($up as $sid => $nids) {
            if (empty($nids)) {
                continue;
            }
            $p = DB_DataObject::factory($notify->table);
            $p->query("
                UPDATE
                    {$notify->table}
                SET
                    server_id = $sid
                WHERE
                    id IN (". implode(',', $nids). ')'
            );
        }
         
        DB_DataObject::factory("core_notify_blacklist")->prune();
        
    }
    
    /**
     * Assign servers based on IPv6 domain assignments
     * If domain_id exists in server_ipv6, set the server_id to the same server
     * If no domain_id match, leave for normal assignment
     */
    function assignQueuesByIPv6Domain($notify)
    {
        $assignedIds = array();
        // Get all pending notifications that have domain_id
        $p = DB_DataObject::factory($notify->table);
        $p->whereAdd("
            sent < '2000-01-01'
            and
            event_id = 0
            and
            act_start < NOW() + INTERVAL 3 HOUR
            and
            domain_id > 0
        ");
        
        $pending_notifications = $p->fetchAll();
        foreach ($pending_notifications as $notification) {
            var_dump($notification->id);
            /*
            // Check if this domain_id has an IPv6 server assignment
            $ipv6 = DB_DataObject::factory('core_notify_server_ipv6');
            $ipv6->domain_id = $notification->domain_id;
            
            if ($ipv6->find(true)) {
                // Find the server whose range contains this IPv6 address
                $serverFromIpv6 = $ipv6->findServerFromIpv6($this->poolname);
                if ($serverFromIpv6) {
                    // Assign the IPv6 server regardless of availability status
                    $update_notification = DB_DataObject::factory($notify->table);
                    $update_notification->get($notification->id);
                    $update_notification->server_id = $serverFromIpv6->id;
                    $update_notification->update();
                    $assignedIds[] = $notification->id;
                }
            }
            */
        }
        die('test3');
        return $assignedIds;
    }
        // called on current server.

    function availableServers()
    {
        $ns = DB_DataObject::factory('core_notify_server');
        $ns->poolname = $this->poolname;
        $ns->is_active = 1;
        $ns->orderBy('id ASC');
        return  $ns->fetchAll();
        
    }
    
    function updateNotifyToNextServer( $cn , $when = false, $allow_same = false, $server_ipv6 = null)
    {
        if (!$this->id) {
            return;
        }
        
        // fixme - this should take into account blacklisted - and return false if no more servers are available
        $email = empty($cn->to_email) ? ($cn->person() ? $cn->person()->email : $cn->to_email) : $cn->to_email;

        $w = DB_DataObject::factory($cn->tableName());
        $w->get($cn->id);

        // set to ipv6 server if available
        // update act_when
        if($server_ipv6 != null) {
            $pp = clone($w);

            $serverFromIpv6 = $server_ipv6->findServerFromIpv6($this->poolname);
            if($serverFromIpv6 != false) {
                $w->server_id = $serverFromIpv6->id;
            }
            $w->act_when = $when === false ? $w->sqlValue('NOW() + INTERVAL 1 MINUTE') : $when;
            $w->update($pp);
            return true;
        }
        
        $servers = $this->availableServers();
        $start = 0;
        foreach($servers as $i => $s) {
            if ($s->id == $this->id) {
                $start = $i;
            }
        }
        
        $offset = ($start + 1)  % count($servers);
        $good = false;
        while ($offset  != $start) {
            $s = $servers[$offset];
            if (!$s->isBlacklisted($email)) {
                $good = $s;
                break;
            }
            $offset = ($offset + 1)  % count($servers);
            //var_dump($offset);
        }
        if ($good == false && $allow_same) {
            $good = $this;
        }
        
        if ($good == false) {
            return false;
        }
        
        
        // next server..
        $pp = clone($w);
        $w->server_id = $good->id;   
        $w->act_when = $when === false ? $w->sqlValue('NOW() + INTERVAL 5 MINUTE') : $when;
        $w->update($pp);
        return true;
    }
    
    
    function isBlacklisted($email)
    {
        if (!$this->id) {
            return false;
        }
        
        // return current server id..
        static $cache = array();
         // get the domain..
        $ea = explode('@',$email);
        $dom = strtolower(array_pop($ea));
        if (isset( $cache[$this->id . '-'. $dom])) {
            return  $cache[$this->id . '-'. $dom];
        }
        
        $cd = DB_DataObject::factory('core_domain')->loadOrCreate($dom);
        
        $bl = DB_DataObject::factory('core_notify_blacklist');
        $bl->server_id = $this->id;
        $bl->domain_id = $cd->id;
        if ($bl->count()) {
            $cache[$this->id . '-'. $dom] = true;
            return true;
        }
        
        return false; 
    }
    function initHelo($server_ipv6 = null)
    {
        if (!$this->id) {
            return;
        }
        $ff = HTML_FlexyFramework::get();
        
        if (!empty($server_ipv6)) {
            $serverFromIpv6 = $server_ipv6->findServerFromIpv6($this->poolname);
            if ($serverFromIpv6 && !empty($serverFromIpv6->ipv6_ptr)) {
                $ff->Mail['helo'] = $serverFromIpv6->ipv6_ptr;
                return;
            }
        }
        $ff->Mail['helo'] = $this->helo;
    }
    function checkSmtpResponse($errmsg, $core_domain)
    {
        if (!$this->id) {
            return false;
        }
        $bl = DB_DataObject::factory('core_notify_blacklist');
        $bl->server_id = $this->id;
        $bl->domain_id = $core_domain->id;
        if ($bl->count()) {
            return true;
        }
        // is it a blacklist message
        if (!$bl->messageIsBlacklisted($errmsg)) {
            return false;
        }
        $bl->error_str = $errmsg;
        $bl->added_dt = $bl->sqlValue("NOW()");
        $bl->insert();
        return true;
        
    }

    /**
     * Find a server with ipv6 range and ptr
     * If current server has ipv6 range and ptr, return it
     * If no current server has ipv6 range and ptr, return the first server with ipv6 range and ptr
     * If no server has ipv6 range and ptr, return false
     * 
     * @return core_notify_server
     */
    function findServerWithIpv6()
    {
        $server = DB_DataObject::factory('core_notify_server');
        $server->whereAdd("
            ipv6_range_from != 0x0
            and
            ipv6_range_to != 0x0
        ");
        $server->is_active = 1;
        $server->limit(1);

        $current = clone($server);
        $current->hostname = gethostbyaddr("127.0.1.1");

        // if current server has ipv6 range and ptr, return it
        if($current->find(true)) {
            return $current;
        }

        // if no current server has ipv6 range and ptr, return the first server with ipv6 range and ptr
        if($server->find(true)) {
            return $server;
        }
        return false;
    }

    /**
     * Find the smallest unused ipv6 address in the range
     * If no unused ipv6 address is found, return false
     * 
     * @return string|false
     */
    function findSmallestUnusedIpv6()
    {
        $cns = DB_DataObject::factory('core_notify_server');
        $cns->selectAdd();
        $cns->selectAdd("find_smallest_unused_ipv6(core_notify_server.id) as smallest_unused_ipv6");
        $cns->id = $this->id;
        if($cns->find(true) && !empty($cns->smallest_unused_ipv6)) {
            return $cns->smallest_unused_ipv6;
        }
        return false;
    }


    
    function resetQueueForTable($table)
    {
        if (!$this->id) {
            return;
        }
        
        $p = DB_DataObject::factory($table);
        $p->query("
            UPDATE
                {$table}
            SET
                server_id = 0
            WHERE
                server_id = {$this->id}
            AND
                (sent < '2000-01-01' OR sent IS NULL)
            AND
                event_id = 0
        ");
    }
    
}
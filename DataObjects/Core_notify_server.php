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
        if (isset($q['_with_queue_size'])) {
            $this->addQueueSize();
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
    
    function getCurrent($notify, $force = false)
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
        
        
        $ns->poolname = $notify->poolname;
        $ns->is_active = 1;
        $ns->hostname = gethostbyaddr("127.0.1.1");
        $ns->limit(1);
        if (strlen($ns->hostname) && $ns->find(true)) {
            $current = $ns;
            return $ns;
        }
        if (!$force) {
            $notify->jerr("Server not found for this server hostname 127.0.1.1 - {$ns->hostname} in core_notify_server" );
        }
        // fallback to any server - if we are using force. (this is so helo will work...)
        
        $ns = DB_DataObject::factory('core_notify_server');
        //$ns->is_active = 1; // we allow non active servers if force is used
        $ns->hostname = gethostbyaddr("127.0.1.1");
        if (!strlen($ns->hostname) ||  !$ns->find(true)) {
            $notify->jerr("Server not found for this server hostname 127.0.1.1 - {$ns->hostname} in core_notify_server" );
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
            return; 
        }
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
            $up[ $sid ] = array_slice($to_add, 0, $target_len - $cq);
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
        // called on current server.

    function availableServers()
    {
        $ns = DB_DataObject::factory('core_notify_server');
        $ns->poolname = $this->poolname;
        $ns->is_active = 1;
        $ns->orderBy('id ASC');
        return  $ns->fetchAll();
        
    }
    
    function updateNotifyToNextServer( $cn , $when = false, $allow_same = false)
    {
        if (!$this->id) {
            return;
        }
        
        // fixme - this should take into account blacklisted - and return false if no more servers are available
        $email = empty($cn->to_email) ? ($cn->person() ? $cn->person()->email : $cn->to_email) : $cn->to_email;

        $w = DB_DataObject::factory($cn->tableName());
        $w->get($cn->id);
        
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
    function initHelo()
    {
        if (!$this->id) {
            return;
        }
        $ff = HTML_FlexyFramework::get();
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
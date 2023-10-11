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
    
    // most services should call this first..
    
    function getCurrent($notify)
    {
        static $current = false;;
        
        if ($current !== false) {
            return $current;
        }
        
        $ns = DB_DataObject::factory('core_notify_server');
        $ns->poolname = $notify->poolname;
        $ns->is_active = 1;
        $ns->hostname = gethostname();
        if (!$ns->count()) {
            $notify->jerr("Server not found for this server " .  gethostname() . " in core_notify_server" );
        }
        $ns->find(true);
        $current = $ns;
        return $ns;
    }
    
    
    function isFirstServer()
    {
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
         
        
        $servers = $this->availableServers();
        $ids = array();
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
        
        // ((@row_number := CASE WHEN @row_number IS NULL THEN 0 ELSE @row_number END  +1) % {$num_servers})
        
        
        
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
        if ($p->count() < 1) {
            return;
        }
        
        $p->selectAdd();
        $p->selectAdd("id, ((@row_number := CASE WHEN @row_number IS NULL THEN 0 ELSE @row_number END  +1) % {$num_servers})  as rn");
        $kv = $p->fetchAll('id,rn');
        foreach($kv as $id => $r) {
            $up[ $ids[$r] ][] = $id;
        }
        foreach($up as $sid => $nids) {
            $p = DB_DataObject::factory($notify->table);
            $p->query("
                UPDATE
                    {$this->table}
                SET
                    server_id = $sid
                WHERE
                    id IN (". implode(",', $nids"). ')'
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
        
        $offset = ($start + 1)  % count($ids);
        $good = false;
        while ($offset  != $start) {
            $s = $servers[$offset];
            if (!$s->isBlacklisted($email)) {
                $good = $s;
                break;
            }
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
        $w->act_when = $when === false ? $w->sqlValue('NOW() + INTERVAL 1 MINUTE') : $when;
        $w->update($pp);
        return true;
    }
    
    
    function isBlacklisted($email)
    {
        // return current server id..
        static $cache = array();
        
        // get the domain..
        $ea = explode('@',$email);
        $dom = strtolower(array_pop($ea));
        if (isset($cache[$dom])) {
            return $cache[$dom];
        }
        
        $cd = DB_DataObject::factory('core_domain')->loadOrCreate($dom);
        
        $bl = DB_DataObject::factory('core_notify_blacklist');
        $bl->server_id = $this->id;
        $bl->domain_id = $cd->id;
        if ($bl->count()) {
            $cache[$dom] = true;
            return true;
        }
        
        return false; 
    }
    function initHelo()
    {
        $ff = HTML_FlexyFramework::get();
        $ff->Mail['helo'] = $this->helo;
        
    }
    function checkSmtpResponse($errmsg, $core_domain)
    {
        $bl = DB_DataObject::factory('core_notify_blacklist');
        $bl->server_id = $this->id;
        $bl->domain_id = $core_domain->id;
        if ($bl->count()) {
            return;
        }
        // is it a blacklist message
        if (!$bl->messageIsBlacklisted($errmsg)) {
            return;
        }
        $bl->added_dt = $bl->sqlValue("NOW()");
        $bl->insert();
        
        
    }
    
}
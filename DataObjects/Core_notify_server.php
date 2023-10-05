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
    
    function assignQueues($notify)
    {
         
        $ns = DB_DataObject::factory('core_notify_server');
        $ns->poolname = $notify->poolname;
        $ns->is_active = 1;
        $ns->orderBy('id ASC');
        $map = $ns->fetchAll('id' ,'hostname' );
        $ids = array_keys($map);
        
        if (empty($ids)) {
            $notify->jerr("no configured servers in core_notify_server for poolname = {$notify->poolname}");
            
        }
        
        // only run this on the first server...
        if ($map[gethostname()] != $ids[0]) {
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
                    {$this->table}
                SET
                    server_id = {$ids[0]}
                WHERE
                    sent < '2000-01-01'
                    and
                    event_id = 0
                    and
                    act_start < NOW() +  INTERVAL 3 HOUR 
                    and
                    server_id < 0
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
                server_id < 0"
            
        );
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
        
        
          
        
    }
    function getCurrent($notify)
    {
        $ns = DB_DataObject::factory('core_notify_server');
        $ns->poolname = $notify->poolname;
        $ns->is_active = 1;
        $ns->hostname = gethostname();
        if (!$ns->count()) {
            $notify->jerr("Server not found for this server " .  gethostname() . " in core_notify_server" );
        }
        $ns->find(true);
        return $ns;
      
}
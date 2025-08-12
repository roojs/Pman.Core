<?php
/**
 * Table Definition for core_domain
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_domain extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */
  
    var $__table = 'core_domain';
    var $id;
    var $domain;
    var $mx_updated;
    var $has_mx;
    var $server_id; // mail_imap_server

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    function loadOrCreate($dom)
    {
        // should we validate domain?
        $dom = preg_replace('/^www./i', '', $dom);
        
        
        static $cache = array();
        if (isset($cache[$dom])) {
            return $cache[$dom];
        }
        
        $cd = DB_DataObject::Factory($this->tableName());
        if ($cd->get('domain', $dom)) {
            $cache[$dom] = $cd;
            return $cd;
        }
        $cd->domain = $dom;
        $cd->insert();
        $cache[$dom] = $cd;
        return $cd;
    }
    function server()
    {
        static $cache = array();
        if (!isset($cache[$this->server_id])) {
            
            $server = DB_DataObject::factory('mail_imap_server');
            if(!$this->server_id || !$server->get($this->server_id)) {
                return false;
            }
            $cache[$this->server_id] = $server;
        }
        return  $cache[$this->server_id];
    }

    function beforeUpdate($old, $q, $roo)
    {
        if(!empty($q['_update_mx'])) {
            $this->updateMx();
            $roo->jok('DONE');
        }
    }

    function updateMx()
    {
        $old = clone($this);

        $this->has_mx = checkdnsrr($this->domain, 'MX');
        $this->mx_updated = date('Y-m-d H:i:s');
        $this->no_mx_dt = '1000-01-01 00:00:00';
        // expired
        if(!$this->has_mx) {
            $this->no_mx_dt = date('Y-m-d H:i:s');
        }
        $this->update($old);
    }

    function toRooSingleArray($authUser, $request)
    {
        $ret = $this->toArray();

        $ret['is_mx_valid'] = $ret['no_mx_dt'] == '1000-01-01 00:00:00' ? 1 : 0;
        
        return $ret;
    }
    
    
}

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

    function beforeInsert($q,$roo)
    {
        if (!empty($q['_delete_ids'])) {
            $this->batchDelete(explode(',', $q['_delete_ids']), $roo);
            $roo->jok('DONE');
        }
    }

    function batchDelete($ids, $roo)
    {
        $cd = DB_DataObject::factory('core_domain');
        $cd->whereAddIn('id', $ids, 'int');
        $domains = $cd->fetchAll();

        if(method_exists($this, 'beforeDelete')) {
            foreach($domains as $domain) {
                $domain->beforeDelete(array(), $roo);
            }
        }

        // delete domains
        $cd = DB_DataObject::factory('core_domain');
        $sql = "
            DELETE
                FROM core_domain
            WHERE
            id IN (" . implode(',', $ids). ")
        ";
        $cd->query($sql);

        if(method_exists($this, 'onDelete')) {
            foreach($domains as $domain) {
                $domain->onDelete(array(), $roo);
            }
        }
    }

    function beforeUpdate($old, $q, $roo)
    {
        if(!empty($q['_update_mx'])) {
            $this->updateMx();
            $roo->jok('DONE');
        }

        if(isset($q['is_mx_valid'])) {
            $isMxValid = $this->no_mx_dt == '1000-01-01 00:00:00' ? 1 : 0;

            // update mx manually
            if($q['is_mx_valid'] != $isMxValid) {
                $this->mx_updated = date('Y-m-d H:i:s');
                // invalid to valid
                if($q['is_mx_valid']) {
                    $this->has_ns = 1;
                    $this->no_mx_dt = '1000-01-01 00:00:00';
                }
                // valid to invalid
                else {
                    $this->has_ns = 0;
                    $this->no_mx_dt = date('Y-m-d H:i:s');
                }
            }
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

        $ret['is_mx_valid'] = $ret['has_mx'] == 0 && $ret['mx_updated'] != '1000-01-01 00:00:00' ? 0 : 1;
        
        return $ret;
    }
    
    function applyFilters($q, $au, $roo)
    {
        if (!empty($q['query']['domain'])) {
            $this->whereAdd("core_domain.domain like '%{$this->escape($q['query']['domain'])}%'");
        }

        if(!empty($q['_status'])) {
            $badCond = "
                (
                    core_domain.has_mx = 0 
                AND 
                    core_domain.mx_updated != '1000-01-01 00:00:00'
                )
            ";

            switch($q['_status']) {
                case 'invalid_mx':
                    $this->whereAdd("{$badCond}");
                    break;
                case 'valid_mx':
                    $this->whereAdd("NOT({$badCond})");
                    break;
            }
        }

        if(!empty($q['_with_reference_count'])) {    
            $this->selectAddReferenceCount();

            /*

            $this->selectAddPersonReferenceCount();
            if(!empty($q['sort']) && $q['sort'] == 'person_reference_count' && !empty($q['dir'])) {
                $dir = $q['dir'] == 'DESC' ? 'DESC' : 'ASC';
                $this->orderBy("{$q['sort']} $dir");
            }
    
            if(!empty($q['_reference_status'])) {
                switch($q['_reference_status']) {
                    case 'with_references':
                        $this->whereAddWithPersonRefernceCount();
                        break;
                    case 'without_reference':
                        $this->whereAddWithoutPersonRefenceCount();
                        break;
                }
            }
                */
        }

        if(!empty($q['_get_references'])) {
            $cd = DB_DataObject::factory('core_domain');
            if(!$cd->get($q['_get_references'])) {
                $roo->jerr('Invalid domain ID');
            }
            $references = $this->getPersonReferences($cd->id, $roo);
            $roo->jdata($references);
        }
    }

    function selectAddReferenceCount()
    {
        $affects  = array();

        $all_links = $this->databaseLinks();
        
        foreach($all_links as $tbl => $links) {
            foreach($links as $col => $totbl_col) {
                $to = explode(':', $totbl_col);
                if ($to[0] != $this->tableName()) {
                    continue;
                }
                
                $affects[$tbl .'.' . $col] = true;
            }
        }

        $sql = array();

        foreach($affects as $k => $true) {
            $arr = explode('.', $k);
            $tbl = $arr[0];
            $col = $arr[1];
            if($tbl == 'pressrelease_notify_archive') {
                continue;
            }
            $sql[] = "SELECT {$tbl}.{$col} AS domain_id, COUNT(*) AS count FROM {$tbl} WHERE {$tbl}.{$col} > 0 GROUP BY {$tbl}.{$col}";
        }
        $this->_join .= "
            LEFT JOIN (
                SELECT domain_id, COUNT(*) AS count
                FROM (
                    " . implode("\n UNION ALL \n", $sql) . "
                ) AS combined
                GROUP BY domain_id
            ) domain_reference_count ON domain_reference_count.domain_id = core_domain.id
        ";
        $this->selectAdd("COALESCE(domain_reference_count.count, 0) AS reference_count");
    }

    function whereAddWithPersonRefernceCount()
    {
        // all domains have no person reference count
        $this->whereAdd("1 = 0");
    }

    function whereAddWithoutPersonRefenceCount()
    {
        // all domains have no person reference count
    }

    function getPersonReferences($domainId, $roo)
    {
        return array();
    }
}

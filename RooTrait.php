<?php

trait Pman_Core_RooTrait {
    
    var $validTables = false; 
    
    var $key;
    
    var $transObj = false;
    
    var $debugEnabled = true;
    
    var $appName;
    var $appNameShort;
    var $appModules;
    var $isDev;
    var $appDisable;
    var $appDisabled;
    var $version ;
    var $uiConfig ;
      
    var $cols = array();
    var $countWhat;
    var $colsJname;    
    var $_hasInit;
    
    function init() 
    {
        if (!empty($this->_hasInit)) {
            return;
        }
        
        $this->_hasInit = true;
        
        $boot = HTML_FlexyFramework::get();
        
        $this->appName= $boot->appName;
        $this->appNameShort= $boot->appNameShort;
        $this->appModules= $boot->enable;
        $this->isDev = empty($boot->Pman['isDev']) ? false : $boot->Pman['isDev'];
        $this->appDisable = $boot->disable;
        $this->appDisabled = explode(',', $boot->disable);
        $this->version = $boot->version; 
        $this->uiConfig = empty($boot->Pman['uiConfig']) ? false : $boot->Pman['uiConfig']; 
        
        if (!empty($ff->Pman['local_autoauth']) && 
            ($_SERVER['SERVER_ADDR'] == '127.0.0.1') &&
            ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') 
        ) {
            $this->isDev = true;
        }
        
    }
    
    function checkDebug($req = false)
    {
        $req =  $req === false  ? $_REQUEST : $req;
        if (isset($req['_debug']) 
                && 
                $this->authUser
                &&
                (
                    (
                        method_exists($this->authUser,'canDebug')
                        &&
                        $this->authUser->canDebug()
                    )
                ||
                    (  
                    
                        method_exists($this->authUser,'groups') 
                        &&
                        is_a($this->authUser, 'Pman_Core_DataObjects_Person')
                        &&
                        in_array('Administrators', $this->authUser->groups('name'))
                    )
                )
                
            ){
            DB_DAtaObject::debuglevel((int)$req['_debug']);
        }
        
    }
    
    function dataObject($tab)
    {
        if (is_array($this->validTables) &&  !in_array($tab, $this->validTables)) {
            $this->jerr("Invalid url - not listed in validTables");
        }
        
        $tab = str_replace('/', '',$tab); // basic protection??
        
        $x = DB_DataObject::factory($tab);
        
        if (!is_a($x, 'DB_DataObject')) {
            $this->jerr('invalid url - no dataobject');
        }
    
        return $x;
    }
    
    function selectSingle($x, $id, $req=false)
    {
        $_columns = !empty($req['_columns']) ? explode(',', $req['_columns']) : false;

        if (!is_array($id) && empty($id)) {
            
            if (method_exists($x, 'toRooSingleArray')) {
                $this->jok($x->toRooSingleArray($this->authUser, $req));
            }
            
            if (method_exists($x, 'toRooArray')) {
                $this->jok($x->toRooArray($req));
            }
            
            $this->jok($x->toArray());
        }
        
        $this->loadMap($x, array(
            'columns' => $_columns,
        ));
        
        if ($req !== false) { 
            $this->setFilters($x, $req);
        }
        
        if (is_array($id)) {
            // lookup...
            $x->setFrom($req['lookup'] );
            $x->limit(1);
            if (!$x->find(true)) {
                if (!empty($id['_id'])) {
                    // standardize this?
                    $this->jok($x->toArray());
                }
                $this->jok(false);
            }
            
        } else if (!$x->get($id)) {
            $this->jerr("selectSingle: no such record ($id)");
        }
        
        // ignore perms if comming from update/insert - as it's already done...
        if ($req !== false && !$this->checkPerm($x,'S'))  {
            $this->jerr("PERMISSION DENIED - si");
        }
        // different symantics on all these calls??
        if (method_exists($x, 'toRooSingleArray')) {
            $this->jok($x->toRooSingleArray($this->authUser, $req));
        }
        if (method_exists($x, 'toRooArray')) {
            $this->jok($x->toRooArray($req));
        }
        
        $this->jok($x->toArray());
        
        
    }
    
    
    
    function loadMap($do, $cfg =array())
    {
        $onlycolumns    = !empty($cfg['columns']) ? $cfg['columns'] : false;
        $distinct       = !empty($cfg['distinct']) ? $cfg['distinct'] : false;
        $excludecolumns = !empty($cfg['exclude']) ? $cfg['exclude'] : array();
          
        $excludecolumns[] = 'passwd'; // we never expose passwords
        
        $ret = $do->autoJoin(array(
            'include' => $onlycolumns,
            'exclude' => $excludecolumns,
            'distinct' => $distinct
        ));
        
        $this->countWhat = $ret['count'];
        $this->cols = $ret['cols'];
        $this->colsJname = $ret['join_names'];
        
        return;
        
    }
    
    function setFilters($x, $q)
    {
        if (method_exists($x, 'applyFilters')) {
           // DB_DataObject::debugLevel(1);
            if (false === $x->applyFilters($q, $this->authUser, $this)) {
                return; 
            } 
        }
        $q_filtered = array();
        
        $keys = $x->keys();
        // var_dump($keys);exit;
        foreach($q as $key=>$val) {
            
            if (in_array($key,$keys) && !is_array($val)) {
               
                $x->$key  = $val;
            }
            
             // handles name[]=fred&name[]=brian => name in ('fred', 'brian').
            // value is an array..
            if (is_array($val) ) {
                
                $pref = '';
                
                if ($key[0] == '!') {
                    $pref = '!';
                    $key = substr($key,1);
                }
                
                if (!in_array( $key,  array_keys($this->cols))) {
                    continue;
                }
                
                // support a[0] a[1] ..... => whereAddIn(
                $ar = array();
                $quote = false;
                foreach($val as $k=>$v) {
                    if (!is_numeric($k)) {
                        $ar = array();
                        break;
                    }
                    // FIXME: note this is not typesafe for anything other than mysql..
                    
                    if (!is_numeric($v) || !is_long($v)) {
                        $quote = true;
                    }
                    $ar[] = $v;
                    
                }
                if (count($ar)) {
                    
                    
                    $x->whereAddIn($pref . (
                        isset($this->colsJname[$key]) ? 
                            $this->colsJname[$key] :
                            ($x->tableName(). '.'.$key)),
                        $ar, $quote ? 'string' : 'int');
                }
                
                continue;
            }
            
            
            // handles !name=fred => name not equal fred.
            if ($key[0] == '!' && in_array(substr($key, 1), array_keys($this->cols))) {
                
                $key  = substr($key, 1) ;
                
                $x->whereAdd(   (
                        isset($this->colsJname[$key]) ? 
                            $this->colsJname[$key] :
                            $x->tableName(). '.'.$key ) . ' != ' .
                    (is_numeric($val) ? $val : "'".  $x->escape($val) . "'")
                );
                continue;
                
            }

            switch($key) {
                    
                // Events and remarks -- fixme - move to events/remarsk...
                case 'on_id':  // where TF is this used...
                    if (!empty($q['query']['original'])) {
                      //  DB_DataObject::debugLevel(1);
                        $o = (int) $q['query']['original'];
                        $oid = (int) $val;
                        $x->whereAdd("(on_id = $oid  OR 
                                on_id IN ( SELECT distinct(id) FROM Documents WHERE original = $o ) 
                            )");
                        continue 2;
                                
                    }
                    $x->on_id = $val;
                
                
                default:
                    if (strlen($val) && $key[0] != '_') {
                        $q_filtered[$key] = $val;
                    }
                    
                    // subjoined columns = check the values.
                    // note this is not typesafe for anything other than mysql..
                    
                    if (isset($this->colsJname[$key])) {
                        $quote = false;
                        if (!is_numeric($val) || !is_long($val)) {
                            $quote = true;
                        }
                        $x->whereAdd( "{$this->colsJname[$key]} = " . ($quote ? "'". $x->escape($val) ."'" : $val));
                        
                    }
                    
                    
                    continue 2;
            }
        }
        if (!empty($q_filtered)) {
            $x->setFrom($q_filtered);
        }
        
        if (!empty($q['query']['name'])) {
            if (in_array( 'name',  array_keys($x->table()))) {
                $x->whereAdd($x->tableName().".name LIKE '". $x->escape($q['query']['name']) . "%'");
            }
        }
        
    }
    
    
    /*
     * From Pman.php
     */
    
    static $permitError = false;
    
    function onPearError($err)
    {
        static $reported = false;
        if ($reported) {
            return;
        }
        
        if (self::$permitError) {
             
            return;
            
        }
        
        $reported = true;
        $out = $err->toString();
        
        $ret = array();
        $n = 0;
        
        foreach($err->backtrace as $b) {
            $ret[] = @$b['file'] . '(' . @$b['line'] . ')@' .   @$b['class'] . '::' . @$b['function'];
            if ($n > 20) {
                break;
            }
            $n++;
        }
        //convert the huge backtrace into something that is readable..
        $out .= "\n" . implode("\n",  $ret);
     
        print_R($out);exit;
        
        $this->jerr($out);
        
    }
    
    function addEvent($act, $obj = false, $remarks = '') 
    {
        if (!empty(HTML_FlexyFramework::get()->Pman['disable_events'])) {
            return;
        }
        
        $e = DB_DataObject::factory('Events');
        $e->init($act,$obj,$remarks); 
         
        $e->event_when = date('Y-m-d H:i:s');
        
        $eid = $e->insert();
        
        // fixme - this should be in onInsert..
        $wa = DB_DataObject::factory('core_watch');
        if (method_exists($wa,'notifyEvent')) {
            $wa->notifyEvent($e); // trigger any actions..
        }
        
        $e->onInsert(isset($_REQUEST) ? $_REQUEST : array() , $this);
        
        return $e;
        
    }
    
    function checkPerm($obj, $lvl, $req= null)
    {
        if (!method_exists($obj, 'checkPerm')) {
            return true;
        }
        if ($obj->checkPerm($lvl, $this->authUser, $req))  {
            return true;
        }
        
        return false;
    }
    
    function hasPerm($name, $lvl)  // do we have a permission
    {
        static $pcache = array();
        $au = $this->getAuthUser();
        return $au && $au->hasPerm($name, $lvl);
        
    }
    
    function getAuthUser()
    {
        die('Get auth user is not implement.');
    }
    
}

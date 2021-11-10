<?php
/**
 * Table Definition for Events
 *
 * objects can implement relatedWhere(), which should return
 *    'tablename' => array of ids
 *
 * 
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Events extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Events';                          // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $person_name;                     // string(128)  
    public $event_when;                      // datetime(19)  binary
    public $action;                          // string(32)  
    public $ipaddr;                          // string(16)  
    public $on_id;                           // int(11)  
    public $on_table;                        // string(64)  
    public $person_id;                       // int(11)  
    public $remarks;                         // blob(65535)  blob
    public $person_table;                    // string(64)
    public $dupe_id;                    // int(11)

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    
    
    
    //  ------------ROO HOOKS------------------------------------
    function applyFilters($q, $au ,$roo)
    {
        $tn = $this->tableName();
        // if not empty on_table
        
        if(!empty($q['person_table'])){
            $jt = DB_DataObject::factory($q['person_table']);

            
            
            if(!array_key_exists("{$jt->tableName()}_id", $this->tableColumns())){ // coz we have triiger on mysql...
                
                $keys = $jt->keys();
            
                $this->_join = "LEFT JOIN {$jt->tableName()} AS join_person_id_id ON (join_person_id_id.{$keys[0]}=Events.person_id)";
                
                $this->selectAdd();
                $this->selectAs();

                $this->selectAs($jt, 'person_id_%s', 'join_person_id_id');
                if (method_exists($jt,'nameColumn')) {
                    $this->selectAdd("join_person_id_id.{$jt->nameColumn()} as person_id_name");
                }

                if (method_exists($jt,'emailColumn')) {
                    $this->selectAdd("join_person_id_id.{$jt->emailColumn()} as person_id_email");
                }
                
            }
        
        } else {
            
            $person = $au->tableName(); //'Person';  -- projects may not use person as the auth table...
            $cfg = HTML_FlexyFramework::get()->Pman;
            if (!empty($cfg['authTable'])) {
                $person =$cfg['authTable'];
            }
            
            $jt = DB_DataObject::factory($person);
            //$this->whereAdd("
            //        person_table  = '{$jt->tableName()}'
            //        OR
            //        person_table = ''
            //        OR person_table IS NULL"
            //); // default to  our standard.. - unless otherwise requested..
        }
        
        
        if (!empty($q['query']['from'])) {
            $dt = date('Y-m-d' , strtotime($q['query']['from']));
            $this->whereAdd(" {$tn}.event_when >=  '$dt' ");
        }
        if (!empty($q['query']['to'])) {
            $dt = date('Y-m-d' , strtotime($q['query']['to']));
            $this->whereAdd(" {$tn}.event_when <=  '$dt' ");
        }
        
        if(!empty($q['_with_dupe_count'])){
            $this->dupe_id = 0;        	                   
            $this->selectAdd("(select count(*)+1 from Events ev_cnt where ev_cnt.dupe_id = {$tn}.id) as dupe_count");
        }
        /*
        if (!empty($q['query']['grouped']) && $q['query']['grouped'] == 'gr') {
            // grouped..
            DB_DataObject::Debuglevel(1);
            $this->groupBy('on_id');
            $this->selectAdd('
                (SELECT count(id) FROM core_event_audit WHERE event_id = Events.id) as changed
                ');
        }
        */
        
        // _join = tablename,tablename...
        
        /// on_table=cohead
        //   &_join=cohead
        //   &_join_cols=cohead_number
        //    &_columns=on_id_cohead_number,event_when << this is ignored at present.
        // max(event_when) is not supported... by any query yet..
        
        if (isset($q['on_table']) && !strlen($q['on_table'])) {
            // empty ontable queries.. these are valid..
            $this->whereAdd("$tn.on_table = ''");
        }
      
        if (isset($q['query']['person_sum'])) {
            //DB_DataObject::debugLevel(1);
            $this->_extra_cols = array('qty' );
            $this->selectAdd("count($tn.id) as qty");
            $this->selectAdd("count( distinct $tn.on_id) as uqty");
            $this->whereAdd('LENGTH(join_person_id_id.name) > 0 ');
            $this->groupBy('person_id,join_person_id_id.name,join_person_id_id.email');
        }
         if (isset($q['query']['table_sum'])) {
            //DB_DataObject::debugLevel(1);
            $this->_extra_cols = array('qty' , 'uqty');
            $this->selectAdd("count($tn.id) as qty");
            $this->selectAdd("count( distinct $tn.on_table, $tn.on_id) as uqty");
            
            $this->groupBy('on_table');
        }
         if (isset($q['query']['day_sum'])) {
            //DB_DataObject::debugLevel(1);
            $this->_extra_cols = array('qty' , 'uqty');
            $this->selectAdd("DATE_FORMAT(event_when, '%Y-%m-%d') as on_day");
            $this->selectAdd("count($tn.id) as qty");
            $this->selectAdd("count( distinct $tn.on_id) as uqty");
            
            $this->groupBy('on_day');
        }
        
        if (isset($q['_join'])) {
            //DB_DataObject::DebugLevel(1);
            $joins = explode(',',$q['_join']);
            
            //$this->selectAdd(); // ??? << this wipes out the default options
            $distinct = false;
            
            foreach($joins as $t) {
                $t = preg_replace('/[^a-z_]+/', '', $t); // protection.
                $x = DB_DataObject::Factory($t);
                if (!is_a($x,'DB_DataObject')) {
                    continue;
                }
                $jtn = $x->tableName();
                $jks = $x->keys();
                $jk = array_shift($jks);
                $this->_join .= "
                
                    LEFT JOIN {$jtn} as join_on_id_{$jtn} ON {$tn}.on_id = join_on_id_{$jtn}.{$jk}
                        AND on_table = '{$jtn}'
                ";
                $keys = array_keys($x->tableColumns());
                if (isset($q['_join_cols'])) {
                    $jcs = explode(',',$q['_join_cols'] );
                    //DB_DataObject::DebugLevel(1);
                    
                    foreach($jcs as $jc) { 
                        if (! in_array($jc, $keys)) {
                            continue;
                        }
                        if ($distinct) { 
                        
                       
                            $this->selectAdd( " join_on_id_{$jtn}.{$jc}   as on_id_{$jc} ");
                        } else {
                            $this->selectAdd( " distinct(join_on_id_{$jtn}.{$jc}  ) as on_id_{$jc} ");
                            $distinct = true;
                        }
                        $this->groupBy("on_id_{$jc} ");
                        $this->whereAdd("join_on_id_{$jtn}.{$jc} IS NOT NULL");
                    }
                    $this->selectAdd( "MAX(events.event_when) as event_when");
                    $this->orderBy('event_when DESC');
                   // $this->selectAs(array($q['_join_cols']) , 'on_id_%s', "join_on_id_{$jtn}");
                } else { 
                    $this->selectAs($x, 'on_id_%s', "join_on_id_{$jtn}");
                }
            }
                 
            
        }
        
        if (isset($q['_related_on_id']) && isset($q['_related_on_table'])) {
            // example: sales order - has invoices,
            ///DB_DataObject::DebugLevel(1);
            $ev  =$this->factory('Events');
            $ev->setFrom(array(
                'on_id' => $q['_related_on_id'],
                'on_table' => $q['_related_on_table'],
                               ));
            $obj = $ev->object();
            
            if (!$obj) {
                $roo->jerr("ontable is invalid");
            }
            
            if (!method_exists($obj,'relatedWhere')) {
                $roo->jerr( $q['_related_on_table'] . " Does not have method relatedWhere  or relatedEventsWhere:" .
                           implode(',', get_class_methods($obj)));
            }
            if ($obj && method_exists($obj,'relatedWhere')) {
                $ar = $obj->relatedWhere();
                $tn = $this->tableName();
                
                $w = array();
                $w[] = "( {$tn}.on_table = '" .
                        $this->escape($q['_related_on_table']) .
                        "' AND {$tn}.on_id = ". ((int)  $q['_related_on_id']) .
                    ")";
                
                
                foreach($ar as $k=>$v) {
                    if (empty($v)) {
                        continue;
                    }                
                     $w[] = "( {$tn}.on_table = '$k' AND {$tn}.on_id IN (". implode(',', $v). "))";
                    
                }
                $this->whereAdd(implode(' OR ' , $w));
            }
            
         
            
        }
        
        // since roo does not support autojoin yet..
        if (!isset($q['_distinct'])) {
            //$this->autoJoinExtra();
        }
        
        if(!empty($q['query']['action'])) {
            $act = $this->escape($q['query']['action']);
            $this->whereAdd("Events.action LIKE '%{$act}%'");
        }
        
        if(!empty($q['query']['on_table'])) {
            $tnb = $this->escape($q['query']['on_table']);
            $this->whereAdd("Events.on_table LIKE '%{$tnb}%'");
        } 
        
    }
    
    function applyPermissionFilters($q, $au ,$roo)
    {
        if (!$au->hasPerm("Admin.Admin_Tab", 'S')) {
            //DB_DataObject::DebugLevel(1);
            // they can only view their changes..
            $this->whereAdd("($tn.person_id = {$au->id} OR $tn.person_id = 0)");
//            $this->person_id = $au->id;
        }
    }
    
    /**
     * check who is trying to access this. false == access denied..
     * @return {boolean} true if access is allowed.
     */
    function checkPerm($lvl, $au) 
    {
        if ($lvl == 'S') {
            return true;
        }
        // listing is controleed by applyfilters..
        return $au->hasPerm("Admin.Admin_Tab", 'S');
    }
    /**
     * object :
     * return the object that this relates to.
     * 
     * @return {DB_DataObject} related object
     */
    function object()
    {
        $o = DB_DataObject::factory($this->on_table);
        $o->get($this->on_id);
        return $o;
        
    }
    
    
    /**
     * init:
     * Initialize an event - ready to insert..
     * 
     * @param {String} action  - group/name of event
     * @param {DataObject|false} obj - dataobject action occured on.
     * @param {String} any remarks 
     */
    
    function init($act, $obj, $remarks)
    {
        $ff = HTML_FlexyFramework::get();
        $pg = $ff->page;
        $au = $pg->getAuthUser();

        if ($ff->cli) { // && empty($au) && isset($obj->person_id)) {
            $au = false;
           // $au = DB_DataObject::Factory('Person'); // not always a person..
           // $au->get($obj->person_id);
        } 

        $this->person_name = $au && !empty($au->name) ? $au->name : '';
        //print_r($au);
        //exit;
        //$this->who = $au && !empty($au->name) ? $au->name : '';
        if (empty($au) || (isset($au->id) && empty($au->id))) {
            // not authenticated - and a standard id based object
            $this->person_id = 0;
        } else {
            $col = $au->tableName() == "Person" ? 'person_id' : $au->tableName() . '_id';
            // does $col exist?
            $cols = $this->tableColumns();
            $col = isset($cols[$col]) ? $col : 'person_id'; // for BC.... - revert to using person_id
            $this->{$col} = $au->pid();
            //$this->person_id = $au ? (!empty($au->id) ? $au->id : $au->pid()) : -1;            
        }
        $this->person_table = $au ? $au->tableName() : '';
        $this->ipaddr = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : 'cli';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->ipaddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        $this->action = $act;
        $this->on_table = $obj ? $obj->tableName() : '';
        $pk = $obj ? $obj->keys()  : false;
        $this->on_id  = $obj && $pk ? $obj->{$pk[0]}: 0;
        $rem  = array();
        // should this really go in remarks? - 
        if ($obj && method_exists($obj,'toEventString')) {
            if($obj->toEventString() !== false){
                $rem[] = $obj->toEventString();
            }
        }
        
        if(!empty($remarks)){
            $rem[] = $remarks;
        }
        
        $this->remarks = implode(' : ', $rem);
    }
    
    /**
     * Generate an audit for this field.
     *
     * @param {DB_DataObject} new data
     * @param {DB_DataObject} old data
     * 
     * @return {int} number of entries logged.
     */
    
    function audit($new, $old = false)
    {
        if ($old == $new) {
            return 0; // they are the same...
        }
         
        $ret = 0;
        foreach(array_keys($new->tableColumns()) as $k) {
            // should we JSON serialize this?
            $n = empty($new->$k) ? '' : $new->$k;
            $o = empty($old->$k) || empty($old->$k) ? '' : $old->$k;
            if ($n == $o) {
                continue;
            }
            $this->auditField($k, $o, $n, $old);
            $ret++;
        }
        return $ret;
    }
    /**
     * Record an audited change, in theory so we can audit data that is not just
     * database Fields...
     *
     * @param {string} $name    table field anme
     * @param {mixed} $ov  old value
     * @param {mixed} $onv  new value
     * @param {mixed} $old  old object (false if we are creating..)
     */
    function auditField($name, $ov, $nv, $old=false )
    {
        // hack..
        if (is_object($nv)) {
            return;
        
        }
        
        $x = DB_DataObject::factory('core_event_audit');
        $x->setFrom(array(
            'event_id' => $this->id,
            'name' => $name,
            'old_audit_id' => $old ? $x->findLast($this, $name) : 0,
            'newvalue' => $nv

        ));
        $x->insert();
    
    }
    
    function beforeInsert($request,$roo)
    {
        if(empty($this->event_when)){
            $this->event_when = $this->sqlValue("NOW()");
        }
        
        if(empty($this->person_id)){
            $this->person_id = $roo->authUser->id;
            $this->person_name = $roo->authUser->name;
            $this->person_table = $roo->authUser->tableName();
        }
        
        if(empty($this->ipaddr)){
            $this->ipaddr = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : 'cli';
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $this->ipaddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        }        
    }
    
    function beforeUpdate($old, $request,$roo)
    {
        if(!empty($request['_restore'])){
            $this->restore($roo);
        }
    }
    
    function onInsert($request,$roo)
    {
        $this->writeEventLog();
    }
    
    static $deleted = array();
    
    
    // log deleting of a record so that it can be written later..
    function logDeletedRecord($obj = false)
    {
        if (is_array($obj)) {
            $ret = false;
            foreach($obj as $o) {
                $aret = $this->logDeletedRecord($o);
                $ret = $ret ? $ret : $aret;
            }
            return true;
        }
        
        if(empty($obj) || !is_a($obj, 'DB_DataObject')){
            return false;
        }
        
        
        $del = $obj->toArray();
        $del['_table'] = $obj->tableName();
        
        self::$deleted[] = $del;
        return true;
    }
    static $extra_data = false;
    
    static function writeEventLogExtra($data) {
        self::$extra_data = $data;
    }
    
    function logDir($user = false)
    {
        $ff  = HTML_FlexyFramework::get();
        if ($user === false) {
            if (function_exists('posix_getpwuid')) {
                $uinfo = posix_getpwuid( posix_getuid () ); 
             
                $user = $uinfo['name'];
            } else {
                $user = getenv('USERNAME'); // windows.
            }
        } 
        
   
        if (!empty($ff->Pman['storedir'])) {
            return $ff->Pman['storedir'] .'/Events/'.$user;
        }
        // DEPRICATED... 
        if (!empty($ff->Pman['event_log_dir'])) {
            return $ff->Pman['event_log_dir'] . '/'.$user;
        }
        
        return false;
    }
    
    
    
    function writeEventLog($extra_data  = false)
    {
        $logdir = $this->logDir();
        
        if (!$logdir) {
            return false;
        }
        
         
        //print_r($this);
        $file = $logdir.  date('/Y/m/d/'). $this->id . ".json";
        if (!file_exists(dirname($file))) {
            
            @mkdir(dirname($file),0700,true); // this might fail if it does not have correct permissions..
            clearstatcache();
            if (!file_exists(dirname($file))) {
                //print_r($this);
                die("Events:: writeEventLog: could not create $file - permissons are not correct\n"); // fatal, otherwise we loop!?
            }
            
        }
        
        // Remove all the password from logs...
        $p =  empty($_POST) ? array() : $_POST;
        foreach(array('passwd', 'password','passwd1',  'passwd2','password1', 'password2') as $rm) {
            if (isset($p[$rm])) {
                $p[$rm] = '******';
            }
        }
        
        
        $i=0;
        $files = array();
         
        $i = 0;
        foreach ($_FILES as $k=>$f){
            // does not handle any other file[] arrary very well..
            if (empty($f['tmp_name']) || !file_exists($f['tmp_name'])) {
                continue;
            }
            $i++;
            $files[$k] = $f;
            
             
            $files[$k]['tmp_name'] =  $this->id . '-'. $i;
            $nf = $logdir .   date('/Y/m/d/').   $files[$k]['tmp_name']; 
            if (!copy($f['tmp_name'], $nf)) {
                print_r("failed to copy {$f['tmp_name']}...\n");
            }
        }
        
        
        
        $out = array(
            'REQUEST_URI' => empty($_SERVER['REQUEST_URI']) ? 'cli' : $_SERVER['REQUEST_URI'],
            'HTTP_USER_AGENT' => empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
            'GET' => empty($_GET) ? array() : $_GET,
            'POST' =>$p,
            'FILES' => $files,
            
        );
        if (!empty(self::$deleted)) {
            $out['DELETED_DATAOBJECTS'] = self::$deleted;
        }
        if ($extra_data !== false) {
            $out['EXTRA'] = $extra_data;
        }
        if ( self::$extra_data !== false) {
            $out['EXTRA_DATA'] =  self::$extra_data;
        }
        $total_data = 0;
        foreach($out as $k=>$v) {
            if (!empty($v) && $k != 'REQUEST_URI') {
                $total_data++;
            }
        }
        if (!$total_data) {
            return; // do not write an empty file with no usefull info.
        }
        
        
        file_put_contents($file, json_encode($out));
        
        
    }
    
    function toRooArray( $q)
    {
        $ret = $this->toArray();
        
        // fill toEventString.
        if (!empty($q['_with_obj_summary']) && !empty($this->on_id)) {
            $obj = $this->object();
              
            if ($obj && $obj->pid() && method_exists($obj,'toEventString')) {
                $es = $obj->toEventString();
                
                if (empty($this->remarks) || strpos($this->remarks, $es) < 0) {
                    $ret['remarks'] = $es . ' ' . $this->remarks;
                }
            } else if (empty($this->remarks)){
                $ret['remarks'] = "DELETED";
                
            }
        }
        //print_r($ret);
        return $ret;
        
    }
    
    function toRooSingleArray($au, $q)
    {
        $ret = $this->toArray();
        
         
        
        if(empty($q['_retrieve_source'])){
            return $ret;
        }
        
        $file = $this->retrieveEventLog();
        
        if(!$file){
            return "No records?!";
        }
        
        $source = json_decode(file_get_contents($file));
        
        return $source;
    }
    
    function retrieveEventLog()
    {

        $logdir = $this->logDir();
        if (!$logdir) {
            return false;
         
        }
        
        $date = date('/Y/m/d/', strtotime($this->event_when));
        
        $file = $logdir. $date. $this->id . ".json";
        if (!file_exists(dirname($file))) {
            
            // try looking www-dir..
            $file = $this->logDir('www-data'). $date. $this->id . ".json";
            if (!file_exists(dirname($file))) {   
                return false;
            }
        }
        
        return $file;
    }
    
    function restore($roo)
    {
        $file = $this->retrieveEventLog();
        
        if(empty($file) || !file_exists($file)){
            $roo->jerr('Could not retrieve the event log file');
        }
        
        $log = json_decode(file_get_contents($file), true);
        
        if(empty($log['POST']) || empty($log['POST']['_delete']) || empty($log['DELETED_DATAOBJECTS'])){
            $roo->jerr('Invalid url');
        }
        
        $restored = array();
        
        foreach ($log['DELETED_DATAOBJECTS'] as $d){
            if(
                    empty($d['id']) || 
                    empty($d['_table']) || 
                    (
                            !empty($restored[$d['_table']]) && 
                            in_array($d['id'], $restored[$d['_table']])
                    )
            ){
                continue;
            }
            
            if(!isset($restored[$d['_table']])){
                $restored[$d['_table']] = array();
            }
            
            $restored[$d['_table']][] = $d['id'];
            
            $table = DB_DataObject::factory($d['_table']);
            
            if (!is_a($table,'DB_DataObject')) {
                continue;
            }
            
            unset($d['_table']);
            
            $table->setFrom($d);
            
            /*
             * need to use the old id
             */
            $table->id = $d['id'];
            
            $table->sequenceKey(false,false);
            
            $table->insert();
            
            if($table->tableName() == 'crm_mailing_list_member'){
                $ml = DB_DataObject::factory('crm_mailing_list');
                if(!$ml->get($table->mailing_list_id)){
                    continue;
                }

                $mlm = DB_DataObject::factory('crm_mailing_list_member');
                $mlm->setFrom(array(
                    'mailing_list_id' => $table->mailing_list_id,
                    'is_active' => 1
                ));

                $o = clone($ml);
                $ml->no_members = $mlm->count();
                $ml->update($o);
            }
        }
        
        $roo->jok('RESTORE');
    }
    function selectAddPersonEmail()
    {
        $this->selectAdd('join_person_id_id.email as email');
        
    }
    
    
}

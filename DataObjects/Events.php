<?php
/**
 * Table Definition for Events
 *
 * objects can implement relatedWhere(), which should return
 *    'tablename' => array of ids
 *
 * 
 */
require_once 'DB/DataObject.php';

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
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    
    
      
    
    
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
        $this->person_id = $au ? $au->id : -1;
        $this->person_table = $au ? $au->tableName() : '';
        $this->ipaddr = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : 'cli';
        $this->action = $act;
        $this->on_table = $obj ? $obj->tableName() : '';
        $pk = $obj ? $obj->keys()  : false;
        $this->on_id  = $obj && $pk ? $obj->{$pk[0]}: 0;
        $rem  = array();
        // should this really go in remarks? - 
        if ($obj && method_exists($obj,'toEventString')) {
            $rem[] = $obj->toEventString() ;
        }
        $rem[] = $remarks;
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
        foreach(array_keys($new->table()) as $k) {
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
    
    
    
    function onInsert($request,$roo)
    {
        $this->writeEventLog();
    }
    
    function writeEventLog()
    {
        $ff  = HTML_FlexyFramework::get();
        if (empty($ff->Pman['event_log_dir'])) {
            return false;
        }
        
        // add user (eg. www-data or local user if not..)
        if (function_exists('posix_getpwuid')) {
            $uinfo = posix_getpwuid( posix_getuid () ); 
         
            $user = $uinfo['name'];
        } else {
            $user = getenv('USERNAME'); // windows.
        }
        //print_r($this);
        $file = $ff->Pman['event_log_dir']. '/'. $user. date('/Y/m/d/'). $this->id . ".json";
        if (!file_exists(dirname($file))) {
            mkdir(dirname($file),0700,true);
        }
        
        // Remove all the password from logs...
        $p =  empty($_POST) ? array() : $_POST;
        foreach(array('passwd', 'password', 'passwd2', 'password2') as $rm) {
            if (isset($p[$rm])) {
                $p['passwd'] = '******';
            }
        }
        $i=0;
        $files = array();
        foreach ($_FILES as $k=>$f){
            if (empty($f['tmp_name']) || !file_exists($f['tmp_name'])) {
                continue;
            }
            $i++;
            $files[$k] = $f;
            $files[$k]['tmp_name'] = $this->id . '.file_'. $i.'.jpg';
            $nf = $ff->Pman['event_log_dir']. '/'. $this->id . ".file_$i.jpg";
            if (!copy($f['tmp_name'], $nf)) {
                print_r("failed to copy {$f['tmp_name']}...\n");
            }
        }
        
        file_put_contents($file, json_encode(array(
            'REQUEST_URI' => empty($_SERVER['REQUEST_URI']) ? 'cli' : $_SERVER['REQUEST_URI'],
            'GET' => empty($_GET) ? array() : $_GET,
            'POST' =>$p,
            'FILES' => $files,
        )));
        
    }
    
    
}

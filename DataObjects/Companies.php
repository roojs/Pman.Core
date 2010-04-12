<?php
/**
 * Table Definition for Companies
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Companies extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Companies';                       // table name
    public $code;                            // string(32)  not_null
    public $name;                            // string(128)  multiple_key
    public $remarks;                         // blob(65535)  blob
    public $owner_id;                        // int(11)  not_null
    public $address;                         // blob(65535)  blob
    public $tel;                             // string(32)  
    public $fax;                             // string(32)  
    public $email;                           // string(128)  
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $isOwner;                         // int(11)  
    public $logo_id;                         // int(11)  not_null
    public $background_color;                // string(8)  not_null
    public $comptype;                        // string(8)  not_null
    public $ava_craft;                       // string(254)  
    public $url;                             // string(254)  not_null
    public $main_office_id;                  // int(11)  not_null
    public $created_by;                      // int(11)  not_null
    public $created_dt;                      // datetime(19)  not_null binary
    public $updated_by;                      // int(11)  not_null
    public $updated_dt;                      // datetime(19)  not_null binary
    public $passwd;                          // string(64)  not_null

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function applyFilters($q, $au)
    {
        $x = DB_DataObject::factory('Companies');
        $x->isOwner = 1;
        $x->find(true);
        
        if (!empty($q['query']['company_project_id'])) {
            $add = '';
            if (!empty($q['query']['company_include_self'])) {
                $add = ' OR Companies.id = ' . $x->id;
            }
            if (!empty($q['query']['company_not_self'])) {
                $add = ' AND Companies.id != ' . $x->id;
            }
            $pids = array();
            $pid = $q['query']['company_project_id'];
            if (strpos($pid, ',')) {
                $bits = explode(',', $pid);
                foreach($bits as $b) {
                    $pids[] = (int)$b;
                }
            } else {
                $pids = array($pid);
            }
            
            
            $pids = implode(',', $pids);
            $this->whereAdd("Companies.id IN (
                SELECT distinct(company_id) FROM ProjectDirectory where project_id IN ($pids)
            ) $add" );
            
           // DB_DataObject::debugLevel(1);
            
            
        }
        
    }
    function toEventString() {
        return $this->name;
    }
    
    // ---------- AUTHENTICATION
     function isAuth()
    {
        $db = $this->getDatabaseConnection();
        $sesPrefix = $db->dsn['database'];
        @session_start();
        if (!empty($_SESSION[__CLASS__][$sesPrefix .'-auth'])) {
            // in session...
            $a = unserialize($_SESSION[__CLASS__][$sesPrefix .'-auth']);
            $u = DB_DataObject::factory('Companies');
            if ($u->get($a->id)) { //&& strlen($u->passwd)) {
                return true;
            }
            $_SESSION[__CLASS__][$sesPrefix .'-auth'] = '';
            
        }
        // not in session or not matched...
        
        
        return false;
        
    }
    function getAuthUser()
    {
        if (!$this->isAuth()) {
            return false;
        }
        $db = $this->getDatabaseConnection();
        $sesPrefix = $db->dsn['database'];
        if (!empty($_SESSION[__CLASS__][$sesPrefix .'-auth'])) {
            $a = unserialize($_SESSION[__CLASS__][$sesPrefix .'-auth']);
            
            $u = DB_DataObject::factory('Companies');
            if ($u->get($a->id)) { /// && strlen($u->passwd)) {
                return clone($u);
            }
             
        }
        
        
        return false;
    }     
    function login()
    {
        $this->isAuth(); // force session start..
         $db = $this->getDatabaseConnection();
        $sesPrefix = $db->dsn['database'];
        $_SESSION[__CLASS__][$sesPrefix .'-auth'] = serialize($this);
        
    }
    function logout()
    {
        $this->isAuth(); // force session start..
        $db = $this->getDatabaseConnection();
        $sesPrefix = $db->dsn['database'];
        $_SESSION[__CLASS__][$sesPrefix .'-auth'] = "";
        
    }    
    // ---------- AUTHENTICATION
    function checkPassword($val)
    {
        //echo '<pre>'.$val .  print_R($this,true);
        if (substr($this->passwd,0,1) == '$') {
            return crypt($val,$this->passwd) == $this->passwd ;
        }
        // old style md5 passwords...- cant be used with courier....
        return md5($val) == $this->passwd;
    }
    function setPassword($value) 
    {
        $salt='';
        while(strlen($salt)<9) {
            $salt.=chr(rand(64,126));
            //php -r var_dump(crypt('testpassword', '$1$'. (rand(64,126)). '$'));
        }
        $this->passwd = crypt($value, '$1$'. $salt. '$');
       
    }      
    function onUpload($controller)
    {
        $image = DB_DataObject::factory('Images');
        return $image->onUploadWithTbl($this, 'logo_id');
         
    }
    function  onUpdate($old, $req,$roo) 
    {
        if (!empty($req['password1'])) {
            $this->setPassword($req['password1']);
            $this->update();
        }
    }
    function onInsert($req, $roo)
    {
        if (!empty($this->logo_id)) { // update images table to sycn with this..
            $img = DB_DataObject::factory('Images');
            if ($img->get($this->logo_id) && ($img->onid != $this->id)) {
                $img->onid = $this->id;
                $img->update();
            }
        }
        if (!empty($req['password1'])) {
            $this->setPassword($req['password1']);
            $this->update();
        }
        $img = DB_DataObject::factory('Images');
        $img->onid= 0;
        
        $img->ontable = 'Companies';
        $img->imgtype = 'LOGO';
        // should check uploader!!!
        if ($img->find()) {
            while($img->fetch()) {
                $ii = clone($img);
                $ii->onid = $this->id;
                $ii->update();
                $this->logo_id = $ii->id;
            }
            $this->update();
        }
        
        
        
        
    }
    
    function beforeDelete()
    {
        // should check for members....
        
        $img = DB_DataObject::factory('Images');
        $img->ontable = 'Companies';
        $img->onid = $this->id;
        $img->find();
        while ($img->fetch()) {
            $img->beforeDelete();
            $img->delete();
        }
        return true;
        
         
    }
    /**
     * check who is trying to access this. false == access denied..
     */
    function checkPerm($lvl, $au, $changes = false) 
    {
        if ($au->company()->comptype != 'OWNER') {
            
            // hacking!
            if ($changes && isset($changes['comptype']) && $changes['comptype'] != $this->comptype) {
                return false;
            }
            
            return $this->id == $au->company_id;
        }
        
        return $au->hasPerm("Core.".$this->tableName(), $lvl);    
    } 
    function whereAddIn($key, $list, $type= 'int') 
    {
        $ar = array();
        foreach($list as $k) {
            $ar[] = $type =='int' ? (int)$k : $this->escape($k);
        }
        if (!$ar) {
            return;
        }
        return $this->whereAdd("$key IN (". implode(',', $ar). ')');
    }
    function fetchAll($k= false, $v = false) 
    {
        if ($k !== false) {
            $this->selectAdd();
            $this->selectAdd($k);
            if ($v !== false) {
                $this->selectAdd($v);
            }
        }
        
        $this->find();
        $ret = array();
        while ($this->fetch()) {
            if ($v !== false) {
                $ret[$this->$k] = $this->$v;
                continue;
            }
            $ret[] = $k === false ? clone($this) : $this->$k;
        }
        return $ret;
         
    }
}

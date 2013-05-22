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
    public $name;                            // string(128)  
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
    public $url;                             // string(254)  not_null
    public $main_office_id;                  // int(11)  not_null
    public $created_by;                      // int(11)  not_null
    public $created_dt;                      // datetime(19)  not_null binary
    public $updated_by;                      // int(11)  not_null
    public $updated_dt;                      // datetime(19)  not_null binary
    public $passwd;                          // string(64)  not_null
    public $dispatch_port;                   // string(255)  not_null
    public $province;                        // string(255)  not_null
    public $country;                         // string(4)  not_null

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function applyFilters($q, $au)
    {
        $tn = $this->tableName();
        //DB_DataObject::debugLevel(1);
        $x = DB_DataObject::factory('Companies');
        $x->comptype= 'OWNER';
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
             
        }
        if (!empty($q['query']['comptype'])) {
           
            $this->whereAddIn('comptype', explode(',', $q['query']['comptype']), 'string');
            
        }
        
        // depricated - should be moved to module specific (texon afair)
        
         if (!empty($q['query']['province'])) {
             $prov = $this->escape($q['query']['province']);
            $this->whereAdd("province LIKE '$prov%'");
            
            
        }
        // ADD comptype_display name.. = for combos..
        $this->selectAdd("
            (SELECT display_name
                FROM
                    core_enum
                WHERE
                    etype='comptype'
                    AND
                    name={$tn}.comptype
                LIMIT 1
                ) as comptype_display_name
        ");
        
         
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
        
        // do we have an empty system..
        if ($au && $au->id == -1) {
            return true;
        }
        
        
        
        if ($au->company()->comptype != 'OWNER') {
            
            // hacking!
            if ($changes && isset($changes['comptype']) && $changes['comptype'] != $this->comptype) {
                return false;
            }
            
            return $this->id == $au->company_id;
        }
        
        return $au->hasPerm("Core.Companies", $lvl);    
    }
    
    function logoImageToHTML($size)
    {
        $i = DB_DataObject::factory('Images');
        if (!$this->logo_id || !$i->get($this->logo_id)) {
            return '';
        }
        return $i->toHTML($size);
        
    }
     function firstImage($filter='image/%')
    {
        $i = DB_DataObject::factory('Images');
        //DB_DataObject::debugLevel(1);
        $im = $i->gather($this, $filter);
        if (empty($im)) {
            return false;
        }
        return $im[0];
    }
    
    function firstImageTag($size =-1, $base="/Images/Thumb", $filter='image/%')
    {
        $fm = $this->firstImage($filter);
         if (empty($fm)) {
            return '';
        }
        return $fm->toHTML($size, $base);
    }
    
    function toRooSingleArray($authUser, $request)
    {
        $ret = $this->toArray();
       // DB_DataObject::debugLevel(1);
        // get the comptype display
        $e = DB_DataObject::Factory('core_enum');
        $e->etype = 'COMPTYPE';
        $e->name = $this->comptype;
        $ret['comptype_display'] = $ret['comptype'];
        if ($e->find(true) && !empty($e->name_display)) {
            $ret['comptype_display'] = $e->name_display;
        }
        
        
        return $ret;
    }
    
    function initCompanies($roo, $name, $type)
    {
        $companies = DB_DataObject::factory('Companies');
        $companies->setFrom(array(
            'name' => $name,
            'comptype' => $type
        ));
        $companies->insert();
        $companies->onInsert(array(), $roo);
    }
    
}

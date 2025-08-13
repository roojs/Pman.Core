<?php
/**
 * Table Definition for Companies
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_Company extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_company';                       // table name
    public $code;                            // string(32)  not_null
    public $name;                            // string(128)  
    public $remarks;                         // blob(65535)  blobeforeDeleteb
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
    public $is_system;                       // int(2)
    
    
    public $comptype_id;
    public $address1;
    public $address2;
    public $address3;
    
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function applyFilters($q, $au, $roo)
    {
        
        $tn = $this->tableName();
        if(empty($q['_skip_country_display_name'])) {
            $this->selectAdd("i18n_translate('c' , {$tn}.country, 'en') as country_display_name ");
        }
      
        $tn = $this->tableName();
        //DB_DataObject::debugLevel(1);
        $x = DB_DataObject::factory('core_company');
        $x->comptype= 'OWNER';
        $x->find(true);

        if (!empty($q['query']['company_project_id'])) {
            $add = '';
            if (!empty($q['query']['company_include_self'])) {
                $add = " OR {$tn}.id = {$x->id}";
            }
            if (!empty($q['query']['company_not_self'])) {
                $add = " AND {$tn}.id != {$x->id}";
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
            $this->whereAdd("{$tn}.id IN (
                SELECT distinct(company_id) FROM ProjectDirectory where project_id IN ($pids)
            ) $add" );
             
        }
        if (!empty($q['query']['comptype'])) {
           
            $this->whereAddIn($tn.'.comptype', explode(',', $q['query']['comptype']), 'string');
            
        }
        
        // depricated - should be moved to module specific (texon afair)
        
         if (!empty($q['query']['province'])) {
             $prov = $this->escape($q['query']['province']);
            $this->whereAdd("province LIKE '$prov%'");
            
            
        }
        // ADD comptype_display name.. = for combos..
        if(empty($q['_skip_comptype_display_name'])) {
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
        
        if(!empty($q['query']['name']) || !empty($q['search']['name'])){
            
            $s = (!empty($q['query']['name'])) ? $this->escape($q['query']['name']) : $this->escape($q['search']['name']);
            
            $this->whereAdd("
                {$tn}.name LIKE '%$s%'
            ");
        }
        
        if(!empty($q['query']['name_starts']) || !empty($q['search']['name_starts'])){
            
            $s = (!empty($q['query']['name_starts'])) ? $this->escape($q['query']['name_starts']) : $this->escape($q['search']['name_starts']);
            
            $this->whereAdd("
                {$tn}.name LIKE '$s%'
            ");
        }
    }
    
    function toEventString() {
        return $this->name;
    }
    
    // ---------- AUTHENTICATION
    // not sure where authetnication via company is used?? posibly media-outreach
    
    function isAuth()
    {
        $db = $this->getDatabaseConnection();
        $sesPrefix = $db->dsn['database'];
        if  (empty($_SERVER['PHP_AUTH_USER'])) {
            @session_start();
        }
        if (!empty($_SESSION[__CLASS__][$sesPrefix .'-auth'])) {
            // in session...
            $a = unserialize($_SESSION[__CLASS__][$sesPrefix .'-auth']);
            $u = DB_DataObject::factory('core_company');
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
            
            $u = DB_DataObject::factory('core_company');
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
        return $image->onUploadWithTbl($this, false, array('imgtype' => 'LOGO'));
         
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
        if (!empty($req['password1'])) {
            $this->setPassword($req['password1']);
            $this->update();
        }
    }
    
    function beforeInsert($q, $roo)
    {
        $companies = DB_DataObject::factory($this->tableName());
        if($companies->get('name', $this->name)){
            $roo->jnotice("DUPE", "{$this->name} already exists!");
        }
        
        // we still use comptype in some old systems...
        
        if(!empty($q['comptype']) && empty($q['comptype_id'])) {
            $en = DB_DataObject::Factory('core_enum');
            $en->setFrom(array(
                'etype' => 'COMPTYPE',
                'name' => $q['comptype'],
                'active' => 1
            ));
            if($en->find(true)) {
                $this->comptype_id = $en->id;
            }
        }
        
        if (!empty($q['comptype_id'])) {
            $en = DB_DataObject::Factory('core_enum');
            $en->get($q['comptype_id']);
            $this->comptype = $en->name;
        }
        
        if(!empty($q['_check_name'])){
            if($this->checkName()){
                $roo->jok('OK');
            }
            
            $roo->jerror('NOTICE-EXIST-CHECK','EXIST');
        }
    }
    
    function beforeUpdate($old, $q,$roo)
    {
        $companies = DB_DataObject::factory($this->tableName());
        $companies->name  = $this->name;
        $companies->whereAdd("id != {$this->id}");
            
        if($companies->find(true)){
            $roo->jnotice("DUPE", "{$this->name} already exists!");
        }

        // we still use comptype in some old systems...
        
        if(!empty($q['comptype']) && empty($q['comptype_id'])) {
            $en = DB_DataObject::Factory('core_enum');
            $en->setFrom(array(
                'etype' => 'COMPTYPE',
                'name' => $q['comptype'],
                'active' => 1
            ));
            if($en->find(true)) {
                $this->comptype_id = $en->id;
            }
        }
        
        if (!empty($q['comptype_id'])) {
            $en = DB_DataObject::Factory('core_enum');
            $en->get($q['comptype_id']);
            $this->comptype = $en->name;
        }
        
        if(!empty($q['_flag_delete'])){
            $this->deleted_dt = $this->sqlValue("NOW()");
            $this->deleted_by = $roo->getAuthUser()->id;
        }
        
        if(!empty($q['_flag_undelete'])){
            $this->deleted_dt = "";
            $this->deleted_by = 0;
        }
        if(!empty($q['_check_name'])){
            if($this->checkName()){
                $roo->jok('OK');
            }
            
            $roo->jerror('NOTICE-EXIST-CHECK','EXIST');
        }
        
        if(!empty($q['_merge_id'])){
            $this->merge($q['_merge_id'], $roo);
        }
        
        if(!empty($this->is_system) && 
            ($old->code != $this->code  ) // used to be not allowed to change name..
        ){
            $roo->jerr('This company is not allow to editing Ref. or Company Name...');
        }
    }
    
    function beforeDelete($req, $roo)
    {
        
        // should check for members....
        if(!empty($this->is_system) && 
            ($old->code != $this->code || $old->name != $this->name)
        ){
            $roo->jerr('This company is not allow to delete');
        }
        
        
    }
    function onDelete($req, $roo)
    {   
        $img = DB_DataObject::factory('Images');
        $img->ontable = $this->tableName();
        $img->onid = $this->id;
        $img->find();
        while ($img->fetch()) {
            $img->beforeDelete(array(), $roo);
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
        $logo = $this->logo();
        if(!$logo) {
            return '';
        }
        return $logo->toHTML($size);
        
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
        $e = DB_DataObject::Factory('core_enum')->lookupObject('COMPTYPE', $this->comptype);
        
        $ret['comptype_display'] = $ret['comptype'];
        if ($e   && !empty($e->name_display)) {
            $ret['comptype_display'] = $e->name_display;
        }
        
        
        return $ret;
    }
    
    /**
     * # 2028 
     * create the suppliers...
     * 
     * @param object $roo
     * @param array $data
     * 
     */
    function initCompaniesArray($roo, $data)
    {
        $tn = $this->tableName();
        
        foreach($data as $d){
            $com = DB_DataObject::factory($tn);
            $com->setFrom($d);
            if(!$com->find(true)){
                $com->created_dt = Date('Y-m-d H:i:s');
                $com->updated_dt = Date('Y-m-d H:i:s');
                $com->is_system = 1;// new column.. block the user changing the code and name..
                $com->insert();
            }
        }
        
        
    }
    
    
    function initCompanies($roo, $opts)
    {
        $companies = DB_DataObject::factory('core_company');
        
        $ctype = empty($opts['add-company-with-type']) ? 'OWNER' : $opts['add-company-with-type'];
        
        $enum = DB_DataObject::Factory('core_enum')->lookup('COMPTYPE', $ctype  );
        
        if (empty($enum)) {
            $roo->jerr("invalid company type '$ctype'");
        }
        if ($ctype =='OWNER') {
            $companies = DB_DataObject::factory('core_company');
            $companies->comptype_id = $enum;
            if ($companies->count()) {
                $roo->jerr("Owner  company already exists");
            }
        }
        $companies = DB_DataObject::factory('core_company');
        
        // check that 
        $companies->setFrom(array(
            'name' => $opts['add-company'],
            'comptype' => $ctype,
            'comptype_id' => $enum,
        ));
        if ($companies->find(true)) {
            $roo->jerr("company already exists");
        }
        $companies->setFrom(array(
            'background_color' => '',
            'created_dt' => $this->sqlValue('NOW()'),
            'updated_dt' => $this->sqlValue('NOW()')
        ));
        
        
        $companies->insert();
        $companies->onInsert(array(), $roo);
    }
    
    function owner()
    {
        if (empty($this->owner_id)) {
            return false;
        }
        static $cache = false;
        if ($cache !== false && isset($cache[$this->owner_id])) {
            return $cache[$this->owner_id]; 
        }
        $o = DB_DataObject::factory('core_company');
        if (!$o->get($this->owner_id)) {
            return false;
        }
        $cache[$this->owner_id] = $o;
        return $o;
        
    }
    
    /// look up the company which is the system owner...
    static function lookupOwner()
    {
        static $cache = false;
        if ($cache !== false) {
            return clone($cache); // no updating this object..
        }
        
        $enum = DB_DataObject::Factory('core_enum')->lookup('COMPTYPE', 'OWNER'  );
        $companies = DB_DataObject::factory('core_company');
        $companies->whereAdd("comptype_id = {$enum} OR comptype = 'OWNER'"); // support old way...
        
        if ($companies->find(true)) {
            $cache = clone($companies);
            return $companies;
        }
        return false;
    }
    
    function merge($merge_to, $roo)
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
        
        foreach($affects as $k => $true) {
            $ka = explode('.', $k);

            $chk = DB_DataObject::factory($ka[0]);
            
            if (!is_a($chk,'DB_DataObject')) {
                $roo->jerr('Unable to load referenced table, check the links config: ' .$ka[0]);
            }
            
            $chk->{$ka[1]} = $this->id;

            foreach ($chk->fetchAll() as $c){
                $cc = clone ($c);
                $c->{$ka[1]} = $merge_to;
                $c->update($cc);
            }
        }
        
        $this->delete();
        
        $roo->jok('Merged');
        
    }
    
    function checkName()
    {
        $company = DB_DataObject::factory('core_company');
        $company->setFrom(array(
            'name' => $this->name
        ));
        
        if(!empty($this->id)){
            $company->whereAdd("id != {$this->id}");
        }
        
        if(!$company->find(true)){
            return true;
        }
        
        return false;
    }

    function logo()
    {
        $i = DB_Dataobject::factory('Images');
        $i->ontable = $this->tableName();
        $i->onid = $this->id;
        $i->imgtype = 'LOGO';
        $i->orderBy('id desc');
        $i->limit(1);
        if(!$i->find(true)) {
            return false;
        }
        return $i;
    }
}

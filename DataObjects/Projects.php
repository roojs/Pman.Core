<?php
/**
 * Table Definition for Projects
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Projects extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Projects';                        // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $name;                            // string(254)  not_null
    public $remarks;                         // blob(65535)  not_null blob
    public $owner_id;                        // int(11)  
    public $code;                            // string(32)  not_null multiple_key
    public $active;                          // int(11)  
    public $type;                            // string(1)  not_null
    public $client_id;                       // int(11)  not_null
    public $team_id;                         // int(11)  not_null
    public $file_location;                   // string(254)  not_null
    public $open_date;                       // date(10)  binary
    public $open_by;                         // int(11)  not_null
    public $countries;                       // string(128)  not_null
    public $languages;                       // string(128)  not_null
    public $close_date;                      // date(10)  binary
    public $agency_id;                       // int(11)  not_null

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    function getProjectManagers()
    {
        $c = DB_DataObject::factory('Companies');
        $c->isOwner = 1;
        if (!$c->find(true)) {
            return array();
        }
        
        
        $pmids = array();
        $pd = DB_DataObject::factory('ProjectDirectory');
        $pd->project_id = $this->id;
        $pd->company_id = $c->id;
        $pd->ispm = 1;
        if (!$pd->count()) {
            return array();
        }
        $pd->selectAdd();
        $pd->selectAdd('distinct (person_id)');
        
        $pd->find();
        while ($pd->fetch()) {
            $pmids[] = $pd->person_id;
            
        }
        $ret = array();
        $p =  DB_DataObject::factory('Person');
        $p->whereAdd('id IN ('. implode(',', $pmids) .')');
        $p->find();
        while ($p->fetch()) {
            $ret[] = clone($p);
        }
        return $ret;
        
    }

    function toEventString() {
        return $this->name;
    }
    
    /**
     * apply filter arguemnts
     * @param $query - see below
     * @param $authUser  - authenticated user
     * 
     * Query specs:
     *   [query]
     *       project_search = text string.
     *       project_indaterange - a/c/o 
     *       project_filter = ALL || P,N,U ....
     * 
     * // to get a users valid project list - just use array('query' => array('project_filter'=> 'ALL'));
     * 
     */
    
    function applyFilters($q, $au)
    {
         
        if (!empty($q['query']['project_search'])) {
            $s = $this->escape($q['query']['project_search']);
            $this->whereAdd(" (Projects.code LIKE '$s%') OR (Projects.name LIKE '%$s%')");
        }
        // types of project to list ... - default is only the open ones...
        if (!empty($q['query']['project_indaterange'])) {
            switch($q['query']['project_indaterange']) {
                case 'A': // all
                    break; 
                case 'C': // current
                     $this->whereAdd('Projects.close_date >= NOW()');
                    break;
                case 'O': // old
                    $this->whereAdd('Projects.close_date < NOW()');
                    break;
               }
        }
        
        if (empty($q['query']['project_filter'])  || $q['query']['project_filter'] != 'ALL') {
            
               
            $pf = empty($q['query']['project_filter']) ? 'P,N,U' : $q['query']['project_filter'];
            $bits= explode(',' ,$pf);
            foreach($bits as $i=>$k) {
                $bits[$i] = $this->escape($k);
            }
            $this->whereAdd("Projects.type in ('". implode("','", $bits) . "')");
        }
         // user projects!!!! - make sure they can only see project they are suppsed to..
         // only applies to document stuff..
          
        if (!$au->hasPerm('Core.Projects_All','S') &&
            $au->hasPerm('Documents.Documents','S')) {
            
            
            
            $pr = DB_DataObject::factory('Projects');
            $pr->whereAdd("Projects.type IN ('N','X')");
            $prjs = $pr->fetchAll('id');
            
            
            $pd = DB_DataObject::factory('ProjectDirectory');
            $pd->joinAdd(DB_DataObject::factory('Projects'), 'LEFT');
            $pd->whereAdd("Projects.type NOT IN ('N','X')");
            $pd->person_id = $au->id;
            
            $prjs = array_merge($prjs, $pd->fetchAll('project_id'));
            if (count($prjs)) {
                $this->whereAdd("
                     (Projects.id IN (".implode(',', $prjs).")) 
                ");
            }  else {
                $this->whereAdd("1=0"); // can see nothing!!!
            }
        }
        
        if (!empty($q['query']['distinct_client_id'])) {
            $this->selectAdd();
            $this->selectAdd('distinct(client_id)');
            
        }
        
        // this is clipping related..  -- we should have an API for addons like this.. (and docs)
        
        if ($au->company()->comptype == 'SUPPLIER') {
            $pr = DB_DataObject::factory('CampaignAssign');
            $pr->supplier_id = $au->company_id;
            $prjs = $pr->fetchAll('project_id');
             if (count($prjs)) {
                $this->whereAdd("
                     (Projects.id IN (".implode(',', $prjs).")) 
                ");
            }  else {
                $this->whereAdd("1=0"); // can see nothing!!!
            }
        }
        if ($au->company()->comptype == 'CLIENT') {
            $this->client_id = $au->company()->id; // can see nothing!!!
            
        }
                 
        
        
        
    }
    function whereAddIn($key, $list, $type) {
        $ar = array();
        foreach($list as $k) {
            $ar[] = $type =='int' ? (int)$k : $this->escape($k);
        }
        if (!$ar) {
            return;
        }
        $this->whereAdd("$key IN (". implode(',', $ar). ')');
    }
    function onInsert()
    {
        $oo = clone($this);
        if (empty($this->code)) {
            $this->code = 'C' + $this->client_id + '-P' + $this->id;
            $this->update($oo);
        }
    }
    
    function onUpdate($old)
    {
        $oo = clone($this);
        if (empty($this->code)) {
            $this->code = 'C' + $this->client_id + '-P' + $this->id;
            $this->update($oo);
        }
        
        if ($old->code == $this->code) {
            return;
        }
        
        
        $opts = PEAR::getStaticProperty('Pman', 'options');
        
        $olddir =  $opts['storedir'] . '/' . $old->code;
        $newdir =  $opts['storedir'] . '/' . $this->code;
        if ( file_exists($olddir)) {
            move ($olddir, $newdir);
        }
         
        
    }
    
    function prune()
    {
        if (!$this->prune) { // non-expiring..
            return;
        }
        
        $d = DB_DataObject::factory('Document');
        $d->whereAdd("date_rec < NOW - INTERVAL {$this->expires} DAYS"); 
        $d->find();
        while ($d->fetch()) {
            $d->prune();
        }
        
        
        
        
        
        
        
    }
    /**
     * our camp interface uses the format Cxxx-Pyyyyyy to refer to the project.
     */
    function getByCodeRef($str)
    {
        $bits = explode('-', $str);
        if ((count($bits) != 2) || $bits[0][0] != 'C' ||  $bits[1][0] != 'P' ) {
            return false;
        }
        $comp = substr($bits[0], 1);
        $id = (int) substr($bits[1], 1);
        return $id && $this->get($id);
        
    }
    
    
    function i18toArray($type, $str) 
    {
        if (empty($str)) {
            return array();
        }
        static $au;
        static $langs;
        static $cts;
        
        if (!$au) {
            $u = DB_DataObject::factory('Person');
            $au =$u->getAuthUser();
            $lang = empty($au->lang ) ? 'en' : $au->lang;
            $lbits = explode('_', strtoupper($lang));
            // no validation here!!!!
            require_once 'I18Nv2/Language.php';
            require_once 'I18Nv2/Country.php';
            $langs = new I18Nv2_Language($lbits[0]); // locale support not there??
            $cts = new I18Nv2_Country($lbits[0]); // lo
            
        }
        $lk = $type == 'c' ? $cts : $langs;
        $ar  =explode(',', $str);
        $ret = array();
        foreach($ar as $k) {
            $ret[] = array('code'=>$k, 'title' => $lk->getName($k));
        }
        return $ret;
        // work out locale...
        
        
        
        
    }
    
    
    function toRooArray($f='%s') {
        $ret = parent::toArray($f);
        // sor tout 
        $ret['countrylist'] = $this->I18toArray('c',$ret['countries']);
        $ret['languagelist'] = $this->I18toArray('l',$ret['languages']);
        return $ret;
    }
    function setFromRoo($q) 
    {
        $this->setFrom($q);
        if (isset($q['open_date'])) {
            $this->open_date = date('Y-m-d', strtotime(
                implode('-', array_reverse(explode('/', $q['open_date'])))
            ));
        }
        return true;
    }
    
    function fetchAll($k= false) {
        if ($k !== false) {
            $this->selectAdd();
            $this->selectAdd($k);
        }
        
        $this->find();
        $ret = array();
        while ($this->fetch()) {
            $ret[] = $k === false ? clone($this) : $this->$k;
        }
        return $ret;
         
    }
    
    /**
     * fetch a list of user projects.
     * if you need to filter open/closed.. then add whereAdds before calling
     */
    function getUserProjects($au, $data='id') // COMPANY BASED!!!!
    {
        $id = (int) $au->company_id;
        $this->whereAdd("
            (client_id= $id) OR (agency_id= $id)
        ");
        if (!empty($data)) {
            $this->selectAdd();
            $this->selectAdd($data);
        }
        $this->find();
        $ret = array();
        while ($this->fetch()) {
            $ret[] = empty($data) ? clone($this) : $this->$data;
        }
        return $ret;
            
    }
    
    
    /**
     * check who is trying to access this. false == access denied..
     */
    function checkPerm($lvl, $au) 
    {
        return $au->hasPerm("Core.Projects_Member_Of",$lvl) || $au->hasPerm("Core.Projects_All",$lvl);
    }
}

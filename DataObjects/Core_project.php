<?php
/**
 * Table Definition for Projects
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_project extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_project';            // table name
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
    public $close_date;                      // date(10)  binary
    public $countries;                       // string(128)  not_null
    public $languages;                       // string(128)  not_null
    public $agency_id;                       // int(11)  not_null
    public $updated_dt;                      // datetime(19)  not_null binary

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    function getProjectManagers()
    {
        $c = DB_DataObject::factory('core_company');
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
        $p =  DB_DataObject::factory('core_person');
        $p->whereAdd('id IN ('. implode(',', $pmids) .')');
        $p->find();
        while ($p->fetch()) {
            $ret[] = clone($p);
        }
        return $ret;
        
    }

    function toEventString() {
        $c = $this->client();
        return ($c && $c->id ? $c->toEventString() : '??'). ':' . $this->name;
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
    
    function applyFilters($q, $au, $roo)
    {
         
        $tn = $this->tableName();
        if (!empty($q['query']['project_search'])) {
            $s = $this->escape($q['query']['project_search']);
            $this->whereAdd(" ({$tn}.code LIKE '$s%')
                            OR
                            ({$tn}.name LIKE '%$s%')
                            OR
                            join_client_id_id.name LIKE '%$s%'
                ");
        }
        // types of project to list ... - default is only the open ones...
        if (!empty($q['query']['project_indaterange'])) {
            switch($q['query']['project_indaterange']) {
                case 'A': // all
                    break; 
                case 'C': // current
                     $this->whereAdd("{$tn}.close_date >= NOW()");
                    break;
                case 'O': // old
                    $this->whereAdd("{$tn}.close_date < NOW()");
                    break;
               }
        }
        
        if (empty($q['_is_update_request']) &&
            
            (empty($q['query']['project_filter'])  || $q['query']['project_filter'] != 'ALL')) {
            
               
            $pf = empty($q['query']['project_filter']) ? 'P,N,U' : $q['query']['project_filter'];
        
         
        
            $this->whereAddIn("{$tn}.type", explode(',', $pf), 'string');
        }
         // user projects!!!! - make sure they can only see project they are suppsed to..
         // only applies to document stuff..
        
        //&& $au->hasPerm('Documents.Documents','S') << this is dependant on the doc modules
          
        if (!$au->hasPerm('Core.Projects_All','S') ) {
            
            
            
            $pr = DB_DataObject::factory($tn);
            $pr->whereAdd("{$tn}.type IN ('N','X')");
            $prjs = $pr->fetchAll('id');
            
            //DB_DataObject::debugLevel(1);
            $pd = DB_DataObject::factory('ProjectDirectory');
            $pd->joinAdd(DB_DataObject::factory($tn), 'LEFT');
            $pd->whereAdd("{$tn}.type NOT IN ('N','X')");
            $pd->person_id = $au->id;
            
            $prjs = array_merge($prjs, $pd->fetchAll('project_id'));
            if (count($prjs)) {
                $this->whereAdd("
                     ({$tn}.id IN (".implode(',', $prjs).")) 
                ");
            }  else {
                $this->whereAdd("1=0"); // can see nothing!!!
            }
        }
        
        if (!empty($q['query']['distinct_client_id'])) {
          // DB_DataObjecT::debuglevel(1);
            $this->selectAdd();
            $this->selectAdd('distinct(client_id)');
            $this->selectAs(DB_DataObject::factory('core_company'), 'client_id_%s','join_client_id_id');
            $this->groupBy('client_id');
             
        }
        
        // this is clipping related..  -- we should have an API for addons like this.. (and docs)
        
       
        
        
        
                 
        
        
        
    }
 
    function onInsert($request,$roo)
    {
        $oo = clone($this);
        if (empty($this->code)) {
            $this->code = 'C' + $this->client_id + '-P' + $this->id;
            $dt = new DateTime();
            $this->updated_dt = $dt->format('Y-m-d H:i:s');
            $this->update($oo);
        }
    }
    
    function onUpdate($old, $request, $roo)
    {
        $oo = clone($this);
        if (empty($this->code)) {
            $this->code = 'C' + $this->client_id + '-P' + $this->id;
            $dt = new DateTime();
            $this->updated_dt = $dt->format('Y-m-d H:i:s');
            $this->update($oo);
        }
        
        if ($old->code == $this->code) {
            return;
        }
        
        $opts = HTML_FlexyFramework::get()->Pman;
        
        $olddir =  $opts['storedir'] . '/' . $old->code;
        $newdir =  $opts['storedir'] . '/' . $this->code;
        if ( file_exists($olddir)) {
            rename($olddir, $newdir);
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
    
    
    /**
     * fetch a list of user projects.
     * if you need to filter open/closed.. then add whereAdds before calling
     */
    function userProjects($au, $data='id') // COMPANY BASED!!!!
    {
        
        $id = (int) $au->company_id;
        
        $this->whereAdd("
            (client_id= $id) OR (agency_id= $id)
        ");
        
        return empty($data) ? $this->fetchAll() :$this->fetchAll($data); 
         
            
    }
    
    
    
    function client()
    {
        if (!$this->client_id) {
            return false;
        }
        $c = DB_DataObject::factory('core_company');
        $c->get($this->client_id);
        return $c;
    }
    
    
    
    function team()
    {
        $c = DB_DataObject::factory('core_group');
        $c->get($this->team_id);
        return $c;
    }
    
    
    
    // DEPRICATED - use userProjects
    
    function getUserProjects($au, $data='id') // COMPANY BASED!!!!
    {
        return $this->userProjects($au, $data);
         
            
    }
    
    
    /**
     * check who is trying to access this. false == access denied..
     */
    function checkPerm($lvl, $au) 
    {
        return $au->hasPerm("Core.Projects_Member_Of",$lvl) || $au->hasPerm("Core.Projects_All",$lvl);
    }
    
}

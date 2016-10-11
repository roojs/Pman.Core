<?php
/**
 * Table Definition for ProjectDirectory
 *
 * Note - projectdirectory is linked to this - due to an issue with postgres - we should keep to lowercase names only for tables..
 * 
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_ProjectDirectory extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'ProjectDirectory';                // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $project_id;                      // int(11)  not_null
    public $person_id;                       // int(11)  not_null
    public $ispm;                            // int(11)  not_null
    public $role;                            // string(16)  not_null

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    
    function person()
    {
        $p = DB_DataObject::factory('core_person');
        $p->get($this->person_id);
        return $p;
    }
    
    function toEventString() {
        $p = $this->person();
        // this is weird... company is in the person.. - effieciency??
        // for seaching??
        $c = DB_DataObject::factory('core_company');
        $c->get($this->company_id);
        $pr = DB_DataObject::factory('core_project');
        $pr->get($this->project_id);
        
        return $pr->code . ' '. $p->name . '('. $c->name .')';
    }
    
    function personMemberOf($pe, $pr) {
        $this->person_id = $pe->id;
        $this->project_id = $pr->id;
        $this->limit(1);
        if ($this->find(true)) {
            return true;
        }
        return false;
    }
 
    function ensureProjectMember($pr, $pe) // used where?
    {
        if ($this->personMemberOf($pe, $pr)) {
           return;
        }
        $this->company_id = $pe->company_id;
        $this->office_id = $pe->office_id;
        $this->role = $pe->role;
        $this->insert();
         
    }
    /**
     * project id's for a user.
     * @param DB_DataObject_Core_Person|array - who, or list of people.
     * @return array id's of the project they are a member of..
     */
    function projects($au)
    {
        if (empty($au)) {
            $p = DB_DataObject::Factory('Projects');
            $p->get('code',  '*PUBLIC');
            return array($p->id);          
            
        } 
        $c = clone ($this);
        
        if (is_array($au)) {
            $c->whereAddIn('person_id', $au, 'int');
        } else {
            $c->person_id = $au->id;
        }
        $c->selectAdd();
        // people may have multiple roles for a project..
        $c->selectAdd("distinct({$this->tableName()}.project_id) as project_id");
        return $c->fetchAll('project_id');
    }
        /**
     * project id's for a user.
     * @param DB_DataObject_Core_Person - who
     * @return array id's of the project they are a member of..
     */
    function people($pr)
    {
        $c = clone ($this);
        //echo '<PRE>';print_R($this);exit;
        
        if (is_array($pr)) {
            $c->whereAddIn("{$this->tableName()}.project_id", $pr, 'int');
        } else {
            $c->project_id = $pr->id;
        }
        $c->selectAdd();
        $c->selectAdd("{$this->tableName()}.person_id as person_id");
        return $c->fetchAll('person_id');
        
         
    }
    
    
    function checkPerm($lvl, $au) 
    {
        return $au->hasPerm('Documents.Project_Directory', $lvl);
    } 
    function setFromRoo($ar,$roo)
    {
        $this->setFrom($ar);
        
        if ($this->id && 
            ($this->project_id == $roo->old->project_id) &&
            ($this->person_id  == $roo->old->person_id) &&
            ($this->company_id == $roo->old->company_id) )
        {
            return true;
        }

        $xx = DB_Dataobject::factory('ProjectDirectory');
        $xx->setFrom(array(
            'project_id' => $this->project_id,
            'person_id'  => $this->person_id,
            'company_id' => $this->company_id,
        ));
        
        if ($xx->count()) {
            return "Duplicate entry found Project Directory entry";
        }
        return true;

    }
    function applyFilters($q, $au)
    {
        //DB_DAtaObject::debugLevel(1);  var_dump($q);
       
        // otherwise only the project they are involved with..
         
        // can  see - their projects + their personal mail...
        if (!empty($q['project_id_ar'])) {
            // can filter projects!
            $this->whereAddIn('ProjectDirectory.project_id', explode(',',$q['project_id_ar']), 'int');
        }
        
        
         if (!empty($q['query']['company_ids'])) {
             $this->whereAddIn('ProjectDirectory.company_id', explode(',',$q['query']['company_ids']), 'int');
        }
        
        // whos should they see as far as personal contacts.!?!?
        // their projects... and their mail or.. just their mail if no projects..
        
        
        /// ------------ PERMISSION FILTERERIN!!!!!!
        
        if ($au->hasPerm('Core.Projects_All', 'S')) {
            return; // can see it all!!!
        }
        
        
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
                    (ProjectDirectory.project_id IN (".implode(',', $prjs).")) 
                  
                
            ");
        }  else {
            $this->whereAdd("1=0"); // can see nothing!!!
        }
       
        
        
         
         
        
    }
    
    
    
}

<?php
/**
 * Table Definition for Groups
 *
 *group types
 *
 * 0 = permission group..
 * 1 = team
 * 2 = contact group
 *
 * 
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Groups extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Groups';                          // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $name;                            // string(64)  not_null
    public $type;                            // int(11)  
    public $leader;                          // int(11)  not_null

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    // group types??
    
    function toEventString() {
        return $this->name;
    }
    
    function beforeDelete()
    {
        $x = DB_DataObject::factory('Groups');
        $x->query("DELETE FROM group_rights WHERE group_id = {$this->id}");
        $x->query("DELETE FROM group_members WHERE group_id = {$this->id}");
    }
    /**
     * check who is trying to access this. false == access denied..
     */
    function checkPerm($lvl, $au) 
    {
        return $au->hasPerm("Core.Groups", $lvl);    
    } 
    function onUpdate($old, $req, $roo)
    {
        $this->ensureLeaderMembership($roo);
    }
    function onInsert($req, $roo)
    {
        $this->ensureLeaderMembership($roo);
    }
    function ensureLeaderMembership($roo)
    {
        
        // groups - make sure the leader is a member...
        if (!$this->type || !$this->leader)
        {
            return true;
        }
        
        $pi = DB_DataObject::factory(empty($ff->Pman['authTable']) ? 'Person' : $ff->Pman['authTable']);
        $pi->get($this->leader);
            
        $p = DB_DataObject::factory('group_members');
        $p->group_id = $this->id;
        $p->user_id = $this->leader;
        //$p->type = 1; //???????
        if (!$p->count()) {
            
            $p->insert();
            $roo->addEvent("ADD", $p, $this->toEventString(). " Added " . $pi->toEventString());
        }
             
    }
    
    function memberIds()
    {
        $gm = DB_Dataobject::factory('group_members');
        $gm->group_id = $this->id;
        return $gm->fetchAll('user_id');
        
    }
    
    
    function addMember($person)
    {
        $gm = DB_Dataobject::factory('group_members');
        $gm->group_id = $this->id;
        $gm->user_id = $person->id;
        if (!$gm->count()) {
            $gm->insert();
        }
    }
    /**
     *
     *  grab a list of members - default is the array of person objects..
     *  @param $what  = set to 'email' to get a list of email addresses.
     *
     *
     */
    
    function members($what = false)
    {
        $ids = $this->memberIds();
        if (!$ids) {
            return array();
        }
        //$p = DB_Dataobject::factory(empty($ff->Pman['authTable']) ? 'Person' : $ff->Pman['authTable']);
        // groups databse is hard coded to person.. so this should not be used for other tables.????
        $p = DB_Dataobject::factory( 'Person' );
        
        $p->whereAdd('id IN ('. implode(',', $ids) .')');
        $p->active = 1;
        return $p->fetchAll($what);
    }
    
    function lookup($k,$v = false) {
        if ($v === false) {
            $v = $k;
            $k = 'id';
        }
        $this->get($k,$v);

        return $this;
    } 
    
    function postListFilter($ar, $au, $req)
    {      
        
        $ret[] = array( 'id' => 0, 'name' => 'EVERYONE');
        $ret[] = array( 'id' => -1, 'name' => 'NOT_IN_GROUP');
        return array_merge($ret, $ar);
            //$ret[] = array( 'id' => 999999, 'name' => 'ADMINISTRATORS');

    }
    
    function initGroups()
    {
        
        $g = DB_DataObject::factory('Groups');
        $g->type = 0;
        $g->name = 'Administrators';
        if ($g->count()) {
            return;
        }
        $g->insert();
        $gr = DB_DataObject::factory('group_rights');
        $gr->genDefault();
    
        
    }
    
    function initDatabase($roo, $data)
    {
        $this->initGroups();
        foreach($data as $gi) {
            $g = DB_DataObject::factory('Groups');
            $g->setFrom($gi);
            if ($g->count()) {
                continue;
            }
            $g->insert();
            
            
        }
     
    }
    
}

<?php
/**
 * Table Definition for Groups
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
        $x->query("DELETE FROM Group_Rights WHERE group_id = {$this->id}");
        $x->query("DELETE FROM Group_Members WHERE group_id = {$this->id}");
    }
    /**
     * check who is trying to access this. false == access denied..
     */
    function checkPerm($lvl, $au) 
    {
        return $au->hasPerm("Core.".$this->tableName(), $lvl);    
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
            
        $p = DB_DataObject::factory('Group_Members');
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
        $gm = DB_Dataobject::factory('Group_Members');
        $gm->group_id = $this->id;
        return $gm->fetchAll('user_id');
        
    }
    
    function members()
    {
        
        
        $ids = $this->memberIds();
        if (!$ids) {
            return array();
        }
        $p = DB_Dataobject::factory(empty($ff->Pman['authTable']) ? 'Person' : $ff->Pman['authTable']);
        $p->whereAdd('id IN ('. implode(',', $ids) .')');
        return $p->fetchAll();
     
        
        
    }
    
    
}

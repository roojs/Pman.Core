<?php
/**
 * Table Definition for Group_Members
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Group_Members extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Group_Members';                   // table name
    public $group_id;                        // int(11)  
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $user_id;                         // int(11)  not_null

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    var $inAdmin = false;
    
    
    function change($person, $group, $state)
    {
        $gm = DB_DataObject::factory('Group_Members');
        $gm->group_id = $group->id;
        $gm->user_id = $person->id;
        $gm->find(true);
        if ($state) {
            if (!$gm->id) {
                $gm->insert();
            }
            return;
        }
        // remove..
        if ($gm->id) {
            $gm->delete();
        }
        
    }
    
    /**
     * Get a list of memberships for a person
     * @param Pman_Core_DataObjects_Person $person who
     * @param String column to fetch.. eg. group_id or 'name'
     *
     */
    
    
    function listGroupMembership($person, $arrayof = 'group_id') 
    {
        $this->inAdmin = false;
        $t = clone($this);
        //DB_DataObject::debugLevel(1);
         
        
        $t->joinAdd(DB_DataObject::factory('Groups'), 'LEFT');
        //$person->id = (int)$person->id;
        $t->whereAdd("
            user_id = {$person->id}
        ");
        $t->selectAdd();
        $t->selectAdd('distinct(group_id), Groups.name as name');
        
        $t->find();
        
        $ret = array() ;
       // $ret = $arrayof == 'group_id' ? array(0) : array();
        // default member of 'All groups'!!
        
        while ($t->fetch()) {
            $ret[] = $t->$arrayof;
            if ($t->name == 'Administrators') { /// mmh... bit risky?
                $this->inAdmin = true;
            }
        }
        if ($arrayof == 'group_id' && !count($ret)) {
            $ret = array(0); /// default if they are not a member of any group.
        }
        
        return $ret;
        
    }
    function checkPerm($lvl, $au) 
    {
        return false;
    } 
   
}

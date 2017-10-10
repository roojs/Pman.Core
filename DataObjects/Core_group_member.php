<?php
/**
 * Table Definition for Group_Members
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_group_member extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_group_member';                   // table name
    public $group_id;                        // int(11)  
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $user_id;                         // int(11)  not_null

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
   
    
    var $inAdmin = false;
    
    
    function change($person, $group, $state)
    {
        $gm = DB_DataObject::factory($this->tableName());
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
         
        $grp = DB_DataObject::factory('core_group');
        $t->joinAdd($grp , 'LEFT');
        //$person->id = (int)$person->id;
        $t->whereAdd("
            user_id = {$person->id}
        ");
        $t->selectAdd();
        $t->selectAdd("distinct(group_id), {$grp->tableName()}.name as name");
        $t->whereAdd('group_id IS NOT NULL');
        
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
        //var_dump($ret);
        return $ret;
        
    }
    
    function checkPerm($lvl, $au) 
    {
        return false;
    }
    
    function beforeInsert($q, $roo)
    {
        if(!empty($q['_persons']) && !empty($q['group_id'])){
            $this->bulkAddToGroup($q, $roo);
        }
    }
    
    function bulkAddToGroup($q, $roo)
    {
        $core_group = DB_DataObject::factory('core_group');
        
        if(!$core_group->get($q['group_id'])){
            $roo->jerr('Invalid URL');
        }
        
        $persons = array_unique(array_filter(explode(',', $q['_persons'])));
        
        foreach ($persons as $p){
            
            $core_person = DB_DataObject::factory('core_person');
            
            if(!$core_person->get($p)){
                continue;
            }
            
            $core_group_member = DB_DataObject::factory('core_group_member');
            $core_group_member->setFrom(array(
                'group_id' => $core_group->id,
                'user_id' => $core_person->id
            ));
            
            if($core_group_member->find(true)){
                continue;
            }
            
            $core_group_member->insert();
            
        }
        
        $roo->jok('OK');
        
    }
    
   
}

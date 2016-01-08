<?php

/*
 * this is loaded by the pman admin..
 *
 * it checks that required variables are set...
 *
 */
class Pman_Core_Pman {
    
    
    function init ($pg)
    {
        if(!empty($_REQUEST['_core_skip_check'])){
            return;
        }
        
        $group = DB_DataObject::factory('groups');
        
        // group should be auto created - by update-database...
        
        if(!$group->get('name', 'core-person-signup-bcc')){
            die("group core-person-signup-bcc does not exist : add ?_core_skip_check=1 to bypass this check");
        }
        
        $p = DB_DataObject::factory('Person');
        
        
        $member = DB_DataObject::factory('group_members');
        $member->group_id = $group->id;
        if ($member->count()) {
            return;
        }
        
        // not got members..
        
        // if we only have one member - then add it .... (it's the admin, and they can modify this later.. - after they get annoyed with it..
        if ($p->count() == 1) {
            $p->find(true);
            $member = DB_DataObject::factory('group_members');
            $member->group_id = $group->id;
            $member->user_id = $p->id;
            $member->insert();
            return;
        }
        
        // only display if we have members..
        die("group core-person-signup-bcc does not have any members : add ?_core_skip_check=1 to bypass this check");
    
        
        
    }
    
    
    
}
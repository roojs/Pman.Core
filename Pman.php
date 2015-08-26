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
        
        $member = DB_DataObject::factory('group_members');
        $member->group_id = $group->id;
        
        if(!$member->count()){
            die("group core-person-signup-bcc does not have any members : add ?_core_skip_check=1 to bypass this check");
        }
        
        
    }
    
    
    
}
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
        
        
        $group = DB_DataObject::factory('groups');
        
        // group should be auto created - by update-database...
        
        
        if(!$group->get('name', 'core-person-signup-bcc')){
            $group = DB_DataObject::factory('groups');
            $group->name = 'core-person-signup-bcc';
            $group->insert();
        }
        
        $p = DB_DataObject::factory('Person');
        if (!$p->count()) {
            return; // can not check people...
        }
            // got people...
        
        
        
        $member = DB_DataObject::factory('group_members');
        $member->group_id = $group->id;
        if ($member->count()) {
            return;
        }
        
        // not got members..
        
        // if we only have one member - then add it .... (it's the admin, and they can modify this later.. - after they get annoyed with it..
        
        $p->find(true);
        $member = DB_DataObject::factory('group_members');
        $member->group_id = $group->id;
        $member->user_id = $p->id;
        $member->insert();
        
            // only display if we have members..
         
        
        
    }
    
    
    
}
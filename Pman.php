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
        
        if(!$group->get('name', 'core-person-signup-bcc')){
        }
        
    }
    
    
    
}
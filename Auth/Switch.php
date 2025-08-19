<?php

require_once 'Pman/Core/Auth/Required.php';

/***
* 
* Authentication - Switch User
*
* (was ?switch)
*
* change logged in user.
*
* GET only 
* 
*/



class Pman_Core_Auth_Switch extends Pman_Core_Auth_Required
{ 
     
    
    function post($v, $opts=array())
    {
        
        if (empty($_REQUEST['user_id'])) {
            $this->jnotice("NOUID", "Missing User id");
        }
        
        $au = $this->authUser;
        
        // first check they have perms to do this..
        if (!$au|| ($au->company()->comptype != 'OWNER') || !$this->hasPerm('Core.Person', 'E')) {
            $this->jerr("User switching not permitted");
        }
                
        $u = $this->userdb();
        $u->get($_REQUEST['user_id']);
        if (!$u->active()) {
            $this->jerr('Account disabled');
        }
        $u->login();
        DB_DataObject::factory('core_person_window')->register($u, $_REQUEST);
            // we might need this later..
        $this->addEvent("LOGIN-SWITCH-USER". $this->event_suffix, false, $au->name . ' TO ' . $u->name);
        $this->jok("SWITCH");
    }
}
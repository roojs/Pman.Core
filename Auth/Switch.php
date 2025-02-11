<?php

require_once 'Pman/Core/Auth.php';

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



class Pman_Core_Auth_Switch extends Pman_Core_Auth
{ 
    function post($v, $opts=array())
    {
        
        $tbl = empty($ff->Pman['authTable']) ? 'core_person' : $ff->Pman['authTable'];
        $u = DB_DataObject::factory($tbl);
        if (!$u->isAuth()) {
            $this->jnotice("AUTH-FAILED", "not logged in");
        }
        if (empty($_REQUEST['user_id'])) {
            $this->jnotice("NOUID", "Missing User id");
        }
        
        $au = $u->getAuthUser();
        
        // first check they have perms to do this..
        if (!$au|| ($au->company()->comptype != 'OWNER') || !$this->hasPerm('Core.Person', 'E')) {
            $this->jerr("User switching not permitted");
        }
                
        $u = DB_DataObject::factory($tbl);
        $u->get($_REQUEST['user_id']);
        if (!$u->active()) {
            $this->jerr('Account disabled');
        }
        $u->login();
            // we might need this later..
        $this->addEvent("LOGIN-SWITCH-USER". $this->event_suffix, false, $au->name . ' TO ' . $u->name);
        $this->jok("SWITCH");
    }
}
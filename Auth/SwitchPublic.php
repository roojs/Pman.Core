<?php

require_once 'Pman/Core/Auth.php';

/***
* 
* Authentication - Switch User (public system version)
*
* (was ?loginPublic)
*
* change logged in user on a public system.
*
* GET only 
* 
*/



class Pman_Core_Auth_SwitchPublic extends Pman_Core_Auth_Required
{ 
   
    function get($v, $opts=array())
    {
         
        $u = $this->userdb();
        $u->get($id);
        
        if (!$u->active()) {
            $this->jerr('Account disabled');
        }
        
        if(!$u->loginPublic()){
            $this->jerr('Switch fail');
        }
         
        $this->jok('OK');
    }
}
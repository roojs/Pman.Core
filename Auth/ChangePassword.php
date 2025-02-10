<?php

require_once 'Pman/Core/Auth.php';

/***
* 
* Authentication - ResetPassword
*
* (was ?resetPassword)
* ** supports ?_verifyCheckSum
*
* password reset request (response)
*
* POST only 
* 
*/



class Pman_Core_Auth_ChangePassword extends Pman_Core_Auth
{ 
    
   
    
    function post($v, $opts=array())
    {
        $au = $this->getAuthUser();
        if (!$au) {
			$this->jerr("Password change attempted when not logged in");
		}
		$uu = clone($au);
		$au->setPassword($_REQUEST['passwd1']);
		$au->update($uu);
		$this->addEvent("LOGIN-CHANGEPASS". $this->event_suffix, $au);
		$this->jok($au);
    }
}
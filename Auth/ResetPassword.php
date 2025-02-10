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



class Pman_Core_Auth_ResetPassword extends Pman_Core_Auth
{ 
    
   
    
    function post($v, $opts=array())
    {
        
        
        
        if (!empty($_REQUEST['_verifyCheckSum'])) {
			if (empty($_REQUEST['id']) || 
                empty($_REQUEST['ts']) ||
                empty($_REQUEST['key'])
                 
                ) {
                $this->jerr("Invalid request to reset password");
            }
                
			$this->verifyResetPassword($_REQUEST['id'], $_REQUEST['ts'], $_REQUEST['key']);
			$this->jok("Checksum is ok");
		}
        
		if (empty($_REQUEST['id']) || 
			empty($_REQUEST['ts']) ||
			empty($_REQUEST['key']) ||
			empty($_REQUEST['password1']) ||
			empty($_REQUEST['password2']) ||
			($_REQUEST['password1'] != $_REQUEST['password2'])
		) {
			$this->jerr("Invalid request to reset password");
		}
			 
        
        $u = $this->verifyResetPassword($_REQUEST['id'],$_REQUEST['ts'],$_REQUEST['key']);
	
	
        $uu = clone($u);
        $u->no_reset_sent = 0;
		if ($newpass != false) {
			$u->setPassword($_REQUEST['password1'] );
		}
        $u->update($uu);
		$this->addEvent("LOGIN-CHANGEPASS". $this->event_suffix, $u);

        $this->jok("Password has been Updated");
        
    }
    function verifyResetPassword($id,$t, $key)
    {
		$au = $this->getAuthUser();
		//print_R($au);
        if ($au) {
            $this->jerr( "Already Logged in - no need to use Password Reset");
        }
        
        $u = DB_DataObject::factory('core_person');
        //$u->company_id = $this->company->id;
        $u->active = 1;
        if (!$u->get($id) || !strlen($u->passwd)) {
            $this->jerr("Password reset link is not valid (id)");
        }
        
        // validate key.. 
        if ($key != $u->genPassKey($t)) {
            $this->jerr("Password reset link is not valid (key)");
        }
	
		if ($t < strtotime("NOW - 1 DAY")) {
            $this->jerr("Password reset link has expired");
        }
        return $u;
	
	
	
    }
}
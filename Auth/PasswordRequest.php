<?php

require_once 'Pman/Core/Auth.php';

/***
* 
* Authentication - Password Request
*
* (was ?passwordRequest)
*
* password reset request
*
* POST only 
* 
*/



class Pman_Core_Auth_PasswordRequest extends Pman_Core_Auth
{ 
    
    var $authFrom;
    var $authKey;
    var $person;
    var $bcc;
    var $rcpts;
      
    
    function post($v, $opts=array())
    {
        if (empty($_REQUEST['passwordRequest'])) {
            $this->jnotice("INVALIDREQ", "missing email");
        }
        
        $u = DB_DataObject::factory('core_person');
        //$u->company_id = $this->company->id;
        
        $u->whereAdd('LENGTH(passwd) > 1');
        $u->email = $_REQUEST['email'];
        $u->active = 1;
        if ($u->count() > 1 || !$u->find(true)) {
            $this->jnotice("INVALIDREQ",'invalid User (1)');
        }
        // got a avlid user..
        if (!strlen($u->passwd)) {
            $this->jnotice("INVALIDREQ",'invalid User (2)');
        }
        // check to see if we have sent a request before..
        
        if ($u->no_reset_sent > 3) {
            $this->jerr('We have issued to many resets - please contact the Administrator');
        }
        
        
        
        
        // sort out sender.
        $cm = DB_DataObject::factory('core_email');
        if (!$cm->get('name', 'ADMIN_PASSWORD_RESET')) {
            $this->jerr("no template  Admin password reset (ADMIN_PASSWORD_RESET) exists - please run importer ");
        }
		if (!$cm->active) {
			$this->jerr("template for Admin password reset has been disabled");
		}
        /*
        
        $g = DB_DAtaObject::factory('Groups');
        if (!$g->get('name', 'system-email-from')) {
            $this->jerr("no group 'system-email-from' exists in the system");
        }
        $from_ar = $g->members();
        if (count($from_ar) != 1) {
            $this->jerr(count($from_ar) ? "To many members in the 'system-email-from' group " :
                       "'system-email-from' group  does not have any members");
        }
        */
        
        
        
        // bcc..
        $g = DB_DAtaObject::factory('core_group');
        if (!$cm->bcc_group_id || !$g->get($cm->bcc_group_id)) {
            $this->jerr("BCC for ADMIN_PASSWORD_RESET email has not been set");
        }
        $bcc = $g->members('email');
        if (!count($bcc)) {
            $this->jerr( "'BCC group for ADMIN_PASSWORD_RESET  does not have any members");
        }
        
        
        
        $this->authFrom = time();
        $this->authKey = $u->genPassKey($this->authFrom);
        //$this->authKey = md5($u->email . $this->authFrom . $u->passwd);
        $this->person = $u;
        $this->bcc = $bcc;
        $this->rcpts = $u->getEmailFrom();
        
	
		$mailer = $cm->toMailer($this, false);
		if (is_a($mailer,'PEAR_Error') ) {
			$this->addEvent('SYSERR',false, $mailer->getMessage());
			$this->jerr($mailer->getMessage());
		}
        $sent = $mailer->send();
		if (is_a($sent,'PEAR_Error') ) {
			$this->addEvent('SYSERR',false, $sent->getMessage());
			$this->jerr($sent->getMessage());
        }
	
        $this->addEvent('LOGIN-PASSREQ'. $this->event_suffix,$u, $u->email);
        $uu = clone($u);
        $uu->no_reset_sent++;
        $uu->update($u);
        $this->jok("done");
    }
}
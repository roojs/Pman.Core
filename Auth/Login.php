<?php

require_once 'Pman/Core/Auth/State.php';

/***
* 
* Authentication - Login
*
* 
* Pman[auth_comptype] = '' << allow all
* Pman[auth_comptype] = (missing) << only 'OWNER'
* Pman[auth_comptype] = '???' << only that type/
*
*
* 
* 
*/



class Pman_Core_Auth_Login extends Pman_Core_Auth_State
{ 
    
    
    function get($v, $opts= array())
    {
        $this->jnotice("INVALIDURL", "INVALID URL");
    }
    function post($v, $opts= array())
    {
        //DB_DataObject::debugLevel(1);

        $u = $this->userdb();
        
        $ip = $this->ip_lookup();
        // ratelimit
        if (!empty($ip)) {
            //DB_DataObject::DebugLevel(1);
            $e = DB_DataObject::Factory('Events');
            $e->action = 'LOGIN-BAD'. $this->event_suffix;
            $e->ipaddr = $ip;
            $e->whereAdd('event_when > NOW() - INTERVAL 10 MINUTE');
            if ($e->count() > 5) {
                $this->jerror('LOGIN-RATE'. $this->event_suffix, "Login failures are rate limited - please try later");
            }
        }
        
	// this was removed before - not quite sure why.
	// when a duplicate login account is created, this stops the old one from interfering..
        $u->active = 1;
        
        // empty username = not really a hacking attempt.
        
        if (empty($_REQUEST['username'])) { //|| (strpos($_REQUEST['username'], '@') < 1)) {
            $this->jerror('LOGIN-EMPTY'. $this->event_suffix, 'You typed the wrong Username or Password (0)');
            exit;
        }
        
        $u->authUserName($_REQUEST['username']);
        
        if ($u->count() > 1 || !$u->find(true)) {
            $this->jerror('LOGIN-BAD'. $this->event_suffix,'You typed the wrong Username or Password  (1)');
            exit;
        }
        
        if (!$u->active()) { 
            $this->jerror('LOGIN-BAD'. $this->event_suffix,'Account disabled');
        }
        
        if(!empty($u->oath_key) && empty($_REQUEST['oath_password'])){
            $this->jerror('LOGIN-2FA'. $this->event_suffix,'Your account requires Two-Factor Authentication');
        }
        
        // check if config allows non-owner passwords.
        // auth_company = "OWNER" // auth_company = "CLIENT" or blank for all?
        // perhaps it should support arrays..
        $ff= HTML_FlexyFramework::get();
        $ct = isset($ff->Pman['auth_comptype']) ? $ff->Pman['auth_comptype'] : 'OWNER';
        if ($u->company()->comptype != $ct) {
            //print_r($u->company());
            $this->jerror('LOGIN-BADUSER'. $this->event_suffix, "Login not permited to outside companies"); // serious failure
        }
        
        
        // note we trim \x10 -- line break - as it was injected the front end
        // may have an old bug on safari/chrome that added that character in certian wierd scenarios..
        if (!$u->checkPassword(trim($_REQUEST['password'],"\x10"))) {
            $this->jerror('LOGIN-BAD'. $this->event_suffix, 'You typed the wrong Username or Password  (2)'); // - " . htmlspecialchars(print_r($_POST,true))."'");
            exit;
        }
        
        if(
            !empty($u->oath_key) &&
            (
            empty($_REQUEST['oath_password']) ||
            !$u->checkTwoFactorAuthentication($_REQUEST['oath_password'])
            )
        ) {
            $this->jerror('LOGIN-BAD'. $this->event_suffix, 'You typed the wrong Username or Password  (3)');
            exit;
        }
        
        $this->ip_checking();
        
        $this->window_check($u);
        
        
        $u->login();
        
        $this->window_register($u);
        // we might need this later..
        $this->addEvent("LOGIN". $this->event_suffix, false, session_id());
		
		
		
        if (!empty($_REQUEST['lang'])) {
			
			if (!empty($ff->languages['avail']) && !in_array($_REQUEST['lang'],$ff->languages['avail'])) {
				// ignore.	
			} else {
			
				$u->lang($_REQUEST['lang']);
			}
        }
         // log it..

        parent::get($v, $opts);
        exit;
         
        
    }
    function ip_lookup()
    {

        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        return $_SERVER['REMOTE_ADDR'];
    }
    
    function ip_checking()
    {
        if(empty($this->ip_management)){
            return;
        }
        
        $ip = $this->ip_lookup();
        
        if(empty($ip)){
            $this->jerr('BAD-IP-ADDRESS', array('ip' => $ip));
        }
        
        $core_ip_access = DB_DataObject::factory('core_ip_access');
        
        if(!DB_DataObject::factory('core_ip_access')->count()){ // first ip we always mark it as approved..
            
            $core_ip_access = DB_DataObject::factory('core_ip_access');
            
            $core_ip_access->setFrom(array(
                'ip' => $ip,
                'created_dt' => $core_ip_access->sqlValue("NOW()"),
                'authorized_key' => md5(openssl_random_pseudo_bytes(16)),
                'status' => 1,
                'email' => (empty($_REQUEST['username'])) ? '' : $_REQUEST['username'],
                'user_agent' => (empty($_SERVER['HTTP_USER_AGENT'])) ? '' : $_SERVER['HTTP_USER_AGENT']
            ));
            
            $core_ip_access->insert();
            
            return;
        }
        
        $core_ip_access = DB_DataObject::factory('core_ip_access');
        
        if(!$core_ip_access->get('ip', $ip)){ // new ip
            
            $core_ip_access->setFrom(array(
                'ip' => $ip,
                'created_dt' => $core_ip_access->sqlValue("NOW()"),
                'authorized_key' => md5(openssl_random_pseudo_bytes(16)),
                'status' => 0,
                'email' => (empty($_REQUEST['username'])) ? '' : $_REQUEST['username'],
                'user_agent' => (empty($_SERVER['HTTP_USER_AGENT'])) ? '' : $_SERVER['HTTP_USER_AGENT']
            ));
            
            $core_ip_access->insert();
            
            $core_ip_access->sendXMPP();
            
            $this->jerror('NEW-IP-ADDRESS', "New IP Address = needs approving", array('ip' => $ip));
            
            return;
        }
        
        if(empty($core_ip_access->status)){
            $this->jerror('PENDING-IP-ADDRESS', "IP is still pending approval", array('ip' => $ip));
        }
        
        if($core_ip_access->status == -1){
            $this->jerror('BLOCKED-IP-ADDRESS', "Your IP is blocked", array('ip' => $ip));
            return;
        }
        
        if($core_ip_access->status == -2 && strtotime($core_ip_access->expire_dt) < strtotime('NOW')){
            $this->jerror('BLOCKED-IP-ADDRESS', "Your IP is blocked", array('ip' => $ip));
            return;
        }
        
        return;
    }
    /**
     * window checking
     *  * we use window.sessionStorage on the client to identify windows.
     *
     * couple of things
     *  * restrict user to single window ?? (now or later?)
     *  * allow admin to log out a user (by flagging core_person_windows to logout)
     *    * This is a force logout - and affects the 'State calls'
     *  * if login is presented - (eg session timeout on an existing window)
     *    * we might have a record of that user being logged in.
     *    *   ( normally this is ok - unless the force logout exists - in which case we return forced-logout )
     * 
     *
     *
     */
    
    function window_check($user)
    {
        if (empty($_REQUEST['window_id'])) { // we don't do any checks on no window data.
            return;
        }
        $w = DB_DataObject::factory('core_person_window');
        $w->person_id = $user->id;
        $mw = clone($w);
        $w->window_id = $_REQUEST['window_id'];
        if (!$w->find(true)) {
            
            if (!$mw->count()) {
                return;
            
            }
            return;
        }
        if ($w->force_logout) {
            $user->logout();
            session_regenerate_id(true);
            session_commit();
            $this->jnotice("FORCE-LOGOUT", "Logout forced");
            return;
        }
        
        // if the user does not have other windows open - and we don't have a record - we do allow this.
        
        
         
    }
     
    function window_register($user)
    {
        if (empty($_REQUEST['window_id'])) { // we don't do any checks on no window data.
            return;
        }
        $w = DB_DataObject::factory('core_person_window');
        $w->person_id = $user->id;
        $w->window_id = $_REQUEST['window_id'];
        $w->login_dt = $w->sqlValue("NOW()");
        
        if ($w->count()) {
            $this->jnotice("MULTI-WIN", "window already exists for user");
        }
        $w->insert();
    }
    
}
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
    var $person;
    var $loginCode;
    var $rcpts;
    
    function get($v, $opts= array())
    {
        $this->jnotice("INVALIDURL", "INVALID URL");
    }
    function post($v, $opts= array())
    {
        

        $u = $this->userdb();
        $ff = HTML_FlexyFramework::get();
        
        $ip =  DB_DataObject::factory('core_person_window')->ip_lookup();
        // ratelimit — skip if this IP had a successful login (core_person_window.register) in the last 10 minutes
       
        // else count LOGIN-BAD events after max(rolling 10 min window, person's updated_dt), in PHP
        if (!empty($ip)  ) {
            $recentWin = DB_DataObject::factory('core_person_window');
            $recentWin->ip = $ip;
            $recentWin->whereAdd('login_dt > NOW() - INTERVAL 1 HOUR');
            $recentWin->limit(1);
             

            //DB_DataObject::DebugLevel(1);
            $e = DB_DataObject::Factory('Events');
            $e->action = 'LOGIN-BAD'. $this->event_suffix;
            $e->ipaddr = $ip;
            $e->whereAdd('event_when > NOW() - INTERVAL 10 MINUTE');
            if (!$recentWin->find(true) && $e->count() > 5) {
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
        
        $oauthWhitelist = empty($ff->Pman['oauth_whitelist']) ? array() : $ff->Pman['oauth_whitelist'];
        $oauthEmailCode = !isset($ff->Pman['oauth_email_code']) ? true : $ff->Pman['oauth_email_code'];

        // check if config allows non-owner passwords.
        // auth_company = "OWNER" // auth_company = "CLIENT" or blank for all?
        // perhaps it should support arrays..
        $this->isUserValid($u);
        
        // note we trim \x10 -- line break - as it was injected the front end
        // may have an old bug on safari/chrome that added that character in certian wierd scenarios..
        if (!$u->checkPassword(trim($_REQUEST['password'],"\x10"))) {
            $this->jerror('LOGIN-BAD'. $this->event_suffix, 'You typed the wrong Username or Password  (2)'); // - " . htmlspecialchars(print_r($_POST,true))."'");
            exit;
        }

        if (!empty($oauthWhitelist) && !in_array($ip, $oauthWhitelist)) {
            $this->requireLoginCode($u, $oauthEmailCode);
        }
        
        $this->ip_checking();
        
		
        DB_DataObject::factory('core_person_window')->check($u, $_REQUEST, false);
         
        $u->login();
        
		DB_DataObject::factory('core_person_window')->register($u, $_REQUEST);
        
        // we might need this later..
        $this->addEvent("LOGIN". $this->event_suffix, false, session_id());

        $this->updateCloudflare();
		
		
		
        if (!empty($_REQUEST['lang'])) {
			
			if (!empty($ff->languages['avail']) && !in_array($_REQUEST['lang'],$ff->languages['avail'])) {
				// ignore.	
			} else {
			
				$u->lang($_REQUEST['lang']);
			}
        }
		// get again with join..
		$u = $this->getAuthUser();
         

        $this->returnUser($u); // in state..
         
    }

    function requireLoginCode($u, $oauthEmailCode)
    {
        if (empty($u->oath_key)) {
            $this->jnotice(
                'LOGIN-OFFICE' . $this->event_suffix,
                'You are trying to access outside the office. Please contact an admin to set up two-factor.'
            );
            exit;
        }

        $pending = empty($_SESSION['Pman_Core_Auth_Login']['email_code']) ? array() : $_SESSION['Pman_Core_Auth_Login']['email_code'];
        $submitted = isset($_REQUEST['oath_password']) ? trim($_REQUEST['oath_password']) : '';
        $pendingOk = (
            !empty($pending['person_id']) &&
            $pending['person_id'] == $u->id &&
            !empty($pending['expires']) &&
            $pending['expires'] >= time() &&
            $pending['tries'] < 5
        );

        if ($submitted !== '') {
            $emailOk = (
                $oauthEmailCode &&
                $pendingOk &&
                !empty($pending['code']) &&
                $submitted === $pending['code']
            );
            if ($emailOk || $u->checkTwoFactorAuthentication($submitted)) {
                unset($_SESSION['Pman_Core_Auth_Login']['email_code']);
                return;
            }
            if (!$pendingOk) {
                $this->jnotice('LOGIN-BAD' . $this->event_suffix, 'You typed the wrong Username or Password  (3)');
                exit;
            }
            $pending['tries'] = $pending['tries'] + 1;
            $_SESSION['Pman_Core_Auth_Login']['email_code'] = $pending;
            if ($pending['tries'] >= 5) {
                $this->jnotice('LOGIN-2FA-LOCK' . $this->event_suffix, 'Too many tries. Reload the page to start again.');
                exit;
            }
            $this->jnotice('LOGIN-BAD' . $this->event_suffix, 'You typed the wrong Username or Password  (3)');
            exit;
        }

        if (!$oauthEmailCode) {
            if (!$pendingOk) {
                $_SESSION['Pman_Core_Auth_Login']['email_code'] = array(
                    'person_id' => $u->id,
                    'code' => '',
                    'expires' => time() + 300,
                    'tries' => 0,
                );
            }
            $this->jnotice('LOGIN-2FA' . $this->event_suffix, 'Use your authenticator app to find a code, and enter it here.');
            exit;
        }

        if ($pendingOk && !empty($pending['code'])) {
            $this->jnotice('LOGIN-2FA-EMAIL' . $this->event_suffix, 'We have sent a login code to your email. Check your email and enter the code from there.');
            exit;
        }

        require_once 'System.php';
        $oathtool = System::which('oathtool');
        $code = '';
        if ($oathtool) {
            $cmd = "{$oathtool} --totp --base32 " . escapeshellarg($u->oath_key);
            $code = trim(exec($cmd));
        }
        if ($code === '') {
            $this->jerror('LOGIN-2FA' . $this->event_suffix, 'Two-factor authentication is not available. Contact an admin.');
            exit;
        }
        $this->person = $u;
        $this->loginCode = $code;
        $this->rcpts = $u->getEmailFrom();
        $cm = DB_DataObject::factory('core_email');
        if (!$cm->get('name', 'ADMIN_LOGIN_CODE') || !$cm->active) {
            $this->jerror('LOGIN-2FA' . $this->event_suffix, 'Login code email is not set up. Contact an admin.');
            exit;
        }
        $mailer = $cm->toMailer($this, false);
        if (is_a($mailer, 'PEAR_Error')) {
            $this->jerror('LOGIN-2FA' . $this->event_suffix, $mailer->getMessage());
            exit;
        }
        $sent = $mailer->send();
        if (is_a($sent, 'PEAR_Error')) {
            $this->jerror('LOGIN-2FA' . $this->event_suffix, $sent->getMessage());
            exit;
        }
        $_SESSION['Pman_Core_Auth_Login']['email_code'] = array(
            'person_id' => $u->id,
            'code' => $code,
            'expires' => time() + 300,
            'tries' => 0,
        );
        $this->jnotice('LOGIN-2FA-EMAIL' . $this->event_suffix, 'We have sent a login code to your email. Check your email and enter the code from there.');
        exit;
    }
    
    function ip_checking()
    {
        if(empty($this->ip_management)){
            return;
        }
        
        $ip = DB_DataObject::factory('core_person_window')->ip_lookup();
        
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

    function updateCloudflare()
    {
        
        $ff = HTML_FlexyFramework::get();

        if(empty($ff->Pman_Core_Auth['cloudflare']['account']) || empty($ff->Pman_Core_Auth['cloudflare']['apiToken'])) {
            return;
        }

        $ip = DB_DataObject::factory('core_person_window')->ip_lookup();

        // don't whitelist loopback address
        if($ip == '::1' || strpos($ip, '127.') === 0) {
            return;
        }

        if(empty($ip)) {
            return;
        }

        $e = DB_DataObject::factory('Events');
        $e->action = 'CLOUDFLARE-WHITELIST';
        $e->remarks = $ip;
        $e->whereAdd('event_when > NOW() - INTERVAL 2 WEEK');
        // we have already whitelisted this ip address within last two weeks
        if($e->count()) {
            return;
        }

        require_once 'Services/Cloudflare/Firewall.php';

        $fw = new Services_Cloudflare_Firewall($ff->Pman_Core_Auth['cloudflare']);

        $ret = $fw->update($ip, "logged in via {$ff->appName}");
        if (is_a($ret, 'PEAR_Error')) {
            $this->errorlog('Cloudflare whitelist failed: ' . $ret->getMessage());
            return;
        }

        $this->addEvent("CLOUDFLARE-WHITELIST", false, $ip);
    }
}
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
        

        $u = $this->userdb();
        
        $ip =  DB_DataObject::factory('core_person_window')->ip_lookup();
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
        $this->isUserValid($u);
        
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
            // return;
        }

        if(empty($ip)) {
            return;
        }

        require_once 'Services/Cloudflare/Firewall.php';

        $fw = new Services_Cloudflare_Firewall($ff->Pman_Core_Auth['cloudflare']);

        $rules = $fw->get($ip);

        // Headers for API requests
        $headers = array(
            "Authorization: Bearer $apiToken",
            "Content-Type: application/json"
        );

        // set mode to 'whitelist' and notes to 'logged in via {$appName}'
        $data = array(
            'mode' => 'whitelist',
            'configuration' => array(
                'target' => 'ip',
                'value' => $ip
            ),
            'notes' => "logged in via {$ff->appName}"
        );

        $rules = $this->getFirewallRulesByIp($baseURL, $headers, $ip);

        var_dump($rules);
        die('test');

        // no such rule -> add
        if(empty($rules)) {
            $this->addFirewallRule($baseURL, $headers, $data);
            return;
        }

        $rule = $rules[0];

        // matching rule's mode is not 'whitelist' -> update
        if($rule['mode'] != 'whitelist') {
            $this->updateFirewallRule($baseURL, $headers, $data, $rule['id']);
            return;
        }
    }
    
    // Function to get firewall rules by ip
    function getFirewallRulesByIp($url, $headers, $ip) 
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . "?configuration.target=ip&configuration.value=$ip");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            return json_decode($response, true)['result'];
        }
		
        $this->errorlog("Failed to get firewall rule with ip: $ip - $httpCode - $response");
        
    }

    // Function to add a firewall rule
    function addFirewallRule($url, $headers, $data) 
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($httpCode != 200) {
            $this->errorlog("Failed to add firewall rule: $httpCode - $response");
        }
    }

    // Function to update a firewall rule
    function updateFirewallRule($url, $headers, $data, $ruleId) 
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$url/$ruleId");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($httpCode != 200) {
            $this->errorlog("Failed to update firewall rule with ID: $ruleId - $httpCode - $response");
        }
    }
}
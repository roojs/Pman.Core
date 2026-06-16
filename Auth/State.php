<?php

require_once 'Pman/Core/Auth.php';

/***
* 
* Authentication - State
*
* Queries current state of authentication.
* was getAuthUser
*
* GET/POST do the same
* 
*/



class Pman_Core_Auth_State extends Pman_Core_Auth
{ 
    function post($v, $opts=array())
    {
        return $this->get($v, $opts);
    }
    
    function get($v, $opts=array()) 
    {
        
        // remove for normal use - it's a secuirty hole!
         
        if (!empty($_REQUEST['_debug'])) {
           // DB_DataObject::debugLevel(1);
        }
        
        $u = $this->userdb();
        
        if (!$u->isAuth()) {
            $this->jok(array( 'id' => 0 , 'code' => empty($u->_auth_error) ? '' : $u->_auth_error ));
            //exit;
        }
        
        // Check firewall option - only for authenticated users
        if (!empty($_REQUEST['check_firewall'])) {
            $this->checkFirewall();
            return;
        }
        
        $au = $u->getAuthUser();
        
        if (!DB_DataObject::factory('core_person_window')->check($au, $_REQUEST)) {
             $this->jok(array( 'id' => 0 , 'code' => 'new-window'));
        }
        
        $this->isUserValid($au);
        
        $this->returnUser($au);
        
        
        
    }
    
    function checkFirewall()
    {
        $ff = HTML_FlexyFramework::get();
        
        if(empty($ff->Pman_Core_Auth['cloudflare']['account']) || empty($ff->Pman_Core_Auth['cloudflare']['apiToken'])) {
            $this->jerror('CLOUDFLARE-NOT-CONFIGURED', 'Cloudflare firewall is not configured');
        }
        
        require_once 'Services/Cloudflare/Firewall.php';
        
        $fw = new Services_Cloudflare_Firewall($ff->Pman_Core_Auth['cloudflare']);
        
        // Get firewall rules for this IP
        $rules = $fw->get(!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
        
        // Debug output - just print the raw results
        echo "<pre>Cloudflare Firewall Rules:\n";
        print_r($rules);
        echo "</pre>";
        exit;
    }
    function isUserValid($u)
    {
        $ff= HTML_FlexyFramework::get();
        $ct = isset($ff->Pman['auth_comptype']) ? $ff->Pman['auth_comptype'] : 'OWNER';
         
        if ($u->company()->comptype != $ct) {
              
            $this->jerror('LOGIN-BADUSER'. $this->event_suffix, "Login not permited to outside companies"); // serious failure
        }
        
    }
    
    
    function returnUser($au)
    {
        
        $aur = $au ?  $au->authUserArray() : array();
        
        /** -- these need modulizing somehow! **/
        
        
        
        // basically calls Pman_MODULE_Login::sendAuthUserDetails($aur) on all the modules
        //echo '<PRE>'; print_r($this->modules());
        // technically each module should only add properties to an array named after that module..
        
        foreach($this->modules() as $m) {
            if (empty($m)) {
                continue;
            }
            if (!file_exists($this->rootDir.'/Pman/'.$m.'/Login.php')) {
                continue;
            }
            $cls = 'Pman_'.$m.'_Login';
            require_once 'Pman/'.$m.'/Login.php';
            $x = new $cls;
            $x->authUser = $au;
            $aur = $x->sendAuthUserDetails($aur);
        }
        
                 
//        
//        echo '<PRE>';print_r($aur);
//        exit;
        $this->jok($aur);
        exit;
        
         
        
           
    }
   
}
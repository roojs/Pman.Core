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
        //DB_DataObject::debugLevel(1);
        if (!empty($_REQUEST['_debug'])) {
           // DB_DataObject::debugLevel(1);
        }
        // 
       $u = $this->userdb();
        
        
        if (!$u->isAuth()) {
            $this->jok(array(
                'id' => 0
            ));
            exit;
        }
        
        //die("got here?");
        $au = $u->getAuthUser();
        
        $this->window_check($au);
        
        /*
         // might occur on shared systems.
        $ff= HTML_FlexyFramework::get();
        
        // IS THIS VALID?? - should be in U-> is auth?
        if (!empty($ff->Pman['auth_comptype'])  && $au->id > 0 &&
                ($ff->Pman['auth_comptype'] != $au->company()->comptype)) {
            $au->logout();
            $this->jerr("Login not permited to outside companies - please reload");
        }
        
        */
        
        //$au = $u->getAuthUser();
        
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
    function window_check($user)
    {
        if (empty($_REQUEST['window_id'])) { // we don't do any checks on no window data.
            return;
        }
        $w = DB_DataObject::factory('core_person_window');
        $w->person_id = $user->id;
        $w->window_id = $_REQUEST['window_id'];
        if (!$w->find(true)) {
            $w = DB_DataObject::factory('core_person_window');
            $w->person_id = $user->id;
            if ($w->count()) {
                $this->jnotice("MULTI-WIN", "window already exists for user");
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
}
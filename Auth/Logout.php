<?php

require_once 'Pman/Core/Auth.php';

/***
* 
* Authentication - State
*
* Queries current state of authentication.
*
* GET only?
* 
*/



class Pman_Core_Auth_Logout extends Pman_Core_Auth
{ 
    function get($v, $opts=array())
    {
        $ff = class_exists('HTML_FlexyFramework2') ?  HTML_FlexyFramework2::get()  :  HTML_FlexyFramework::get();
        
		//DB_DAtaObject::debugLevel(1);
        $u = $this->getAuthUser();
        //print_r($u);
        if ($u) {
            
            $this->addEvent('LOGOUT'. $this->event_suffix);
            $e = DB_DataObject::factory('Events');
          
            
            $u->logout();
			
			DB_DataObject::factory('core_person_window')->clear($u, $_REQUEST);
			
            session_regenerate_id(true);
            session_commit(); 

            if(!empty($ff->Pman['local_autoauth']) && !empty($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'localhost') {
                $this->jerr("you are using local autoauth!?");                
            }
            //echo '<PRE>';print_R($_SESSION);
            $this->jok("Logged out - user ");
        }
        // log it..
        
        //$_SESSION['Pman_I18N'] = array(); << 
        session_regenerate_id(true);
        session_commit();
        
        $this->jok("Logged out - no user");
    }
    // allow post to logut?
    function post($v, $opts= array()) {
        return $this->get($v, $opts);
    }
	
	
	
}
        
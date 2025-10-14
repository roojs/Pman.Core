<?php
/**
 * Table Definition for core company
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_person_window extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_person_window';               // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $person_id;
    public $window_id;                            // string(64)  not_null
    public $app_id;
    public $login_dt;
  
    //public $force_logout; // replace with state?
    public $last_access_dt;
    
    public $ip;    // this is for information only?
    public $user_agent;  // this is for information only?
    public $status;  // ENUM  IN|OUT|KILL (default IN)
    
    function applyFilters($q, $au, $roo)
    {
        $g = $au->groups('name');
        if (!in_array('Administrators', $au->groups('name'))) {
            $roo->jnotice("NOPERM", "Only admins can view this");
        }
        
        if (isset($q['_with_person_data'])) {
            $this->_join .= "
                LEFT JOIN core_person as join_person_id_id ON (join_person_id_id.id=core_person_window.person_id)
            ";
            $this->selectAdd("
                join_person_id_id.name as person_id_name,
                join_person_id_id.email as person_id_email
            ");
            if (!empty($q['search']['name'])) {
                $n = $this->escape($q['search']['name']);
                $this->whereAdd("
                    join_person_id_id.name LIKE '%{$n}%'
                    OR
                    join_person_id_id.email LIKE '%{$n}%'
                ");
            }
        }
        
    }
       /**
     * window checking
     *  * we use window.sessionStorage on the client to identify windows.
     *  * we will use ?? to check on browser?
     *  *
     *
     
     * Load/Reload
     *   * do we have any windows open? (except this one?)
     *     * close other windows -> then always login?
     *     * do what - blank window?
     *   * next => send 'clear logins'
     *     * then login or show UI
     *
     * Login:
     *   * login - fails if we are already logged in?
     *   * 
     *   
     *
     * Regular Checking?
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
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    function register($user, $req)
    {
        if (empty($req['window_id']) )   { // we don't do any checks on no window data.
            return;
        }
        $this->cleanup();
        
        $w = DB_DataObject::factory('core_person_window');
        $w->person_id = $user->id;
        $w->window_id = $req['window_id'];
        $ff = HTML_FlexyFramework::get();
		$w->app_id = $ff->appNameShort;
        
        if ($w->count() ) {
            $w->find(true);
            $ww = clone($w);
            
            $w->login_dt = $w->sqlValue("NOW()");
            $w->last_access_dt = $w->login_dt;
            $w->status = 'IN';
            // these might have been empty?
            $w->ip = $this->ip_lookup();
            $w->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            $w->update($ww);
            return; /// already registered?
        }
        
        $w->login_dt = $w->sqlValue("NOW()");
        $w->last_access_dt = $w->login_dt;
        
        $w->ip = $this->ip_lookup();
        $w->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $w->status = 'IN';
        $w->insert();
    }
  
    
    function  check($user, $req, $log_error = true)
    {
        if (empty($req['window_id']) ) { // we don't do any checks on no window data.
            return true;
        }
        $w = DB_DataObject::factory('core_person_window');
        $w->person_id = $user->id;
        $ff = HTML_FlexyFramework::get();
		$w->app_id = $ff->appNameShort;
        
        if (!empty($ff->Pman['local_autoauth']) ) { // we don't do any checks on no window data.
            return true;
        }
        $mw = clone($w);
        $w->window_id = $req['window_id'];
         
        if (!$w->find(true)) {
            $mw->status = 'IN';
            
            if (!empty($req['_require_window'])) {
                return false;
            }
            
            if ($mw->count()) {
                
                // means this user has logins  (on other windows, but not this one)
                $this->register($user, $req);
                return true;
                /*
                // we should create it?
                if ($log_error) {
                    $ff->page->jerror("LOGIN-BAD",
                        "There appears to be a problem with the user you have logged in as - please try logging in again");
                    //$ff->page->errorlog(
                    //    "No login found - wid:{$req['window_id']} but have multiple logins for {$w->person()->email}\n" . 
                    //    print_R($mw->fetchAll(false,false,'toArray'), true)
                    //);
                    
                }
                */
            } else {
                $this->register($user, $req);
                return true;
                    /*
                if ($log_error) {
                    $ff->page->jerror("LOGIN-BAD", "There was a problem with your login \n" .
                                      " - Please try logging in again - you were previously logged in as {$w->person()->email}");
                }
                */
            }
            
            // allow multiwindows at present
            //$ff->page->jnotice("MULTI-WIN", "You have to many windows  open");
            // no record exists - it's ok - it's created later
            return true;
        }
        if ($w->status == 'OUT') {
            if ($log_error) {
                $ff->page->errorlog("User session appears to be logged out {$w->person()->email}");
            }
            return true;
        }
        
        if ($w->status == 'KILL') {
            $w->person()->logout();
            session_regenerate_id(true);
            session_commit();
            $ww  = clone($w);
            $w->status = 'OUT';
            $w->update($ww);
            
            $ff->page->jnotice("FORCE-LOGOUT", "this window must be reloaded");
        }
        $ww = clone($w);
        
        $w->last_access_dt = $w->sqlValue("NOW()");;
        //$w->status = 'IN'; // ?? needed?  since it can only get her eif status is in...
        $w->update($ww);
         return true;
        
         
    }
    function clear($user, $req)
    {
        if (empty($req['window_id'])) { // we don't do any checks on no window data.
            return;
        }
        $w = DB_DataObject::factory('core_person_window');
        $w->person_id = $user->id;
        $w->window_id = $_REQUEST['window_id'];
        $ff = HTML_FlexyFramework::get();
		$w->app_id = $ff->appNameShort;
		$w->find();
        while ($w->fetch()) {
			$ww = clone($w);
			$ww->status = 'OUT';
            $ww->last_access_dt = $ww->sqlValue("NOW()");;
            $ww->update($w);
		}
        
        
    }
    function ip_lookup()
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        return $_SERVER['REMOTE_ADDR'];
    }
    
    function cleanup()
    {
        // last_access_dt is set when we login - so it's always current.
        $w = DB_DataObject::factory('core_person_window');
        $w->query("
                DELETE FROM
                    core_person_window
                WHERE
                    last_access_dt < NOW() - INTERVAL 1 DAY
                 
        ");
        
        
    }
    function person()
    {
        $p = DB_DataObject::Factory('core_person');
        return $p->get($this->person_id) ? $p : false;
        return $p;
    }
    
     
    function beforeInsert($q,$roo )
    {
        
        if (empty($q['status'])  || empty($q['person_id']) || $q['status'] != 'KILL') {
            $roo->jnotice("INVALIDURL", "no direct insert to server");
        }
        $w = DB_DataObject::factory('core_person_window');
        $w->person_id = $q['person_id'];
        if (!$w->person()) {
            $roo->jnotice("INVALIDURL", "invalid person id");
        }
        $w->status = 'IN';
        foreach($w->fetchAll() as $w) {
            $ww = clone($w);
            $w->status = 'KILL';
            $w->update($ww);
        }
        $roo->jok("Killed");
        
    }
    function beforeDelete($dependants_array, $roo, $request)
    {
        $roo->jnotice("INVALIDURL", "no direct delete to server");
        
    }
    function beforeUpdate($old,$request,$roo)
    {
        $roo->jnotice("INVALIDURL", "no direct update to server");
        
    }
}

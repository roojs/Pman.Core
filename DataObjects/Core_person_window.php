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
    public $state;  // ENUM  IN|OUT|KILL (default IN)
    
    
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
            $w->state = 'IN';
            $w->update($ww);
            return; /// already registered?
        }
        
        $w->login_dt = $w->sqlValue("NOW()");
        $w->last_access_dt = $w->login_dt;
        
        $w->ip = $this->ip_lookup();
        $w->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $w->state = 'IN';
        $w->insert();
    }
  
    
    function  check($user, $req)
    {
        if (empty($req['window_id']) ) { // we don't do any checks on no window data.
            return;
        }
        $w = DB_DataObject::factory('core_person_window');
        $w->person_id = $user->id;
        $ff = HTML_FlexyFramework::get();
		$w->app_id = $ff->appNameShort;
       
        
        $mw = clone($w);
        $w->window_id = $req['window_id'];
         
        if (!$w->find(true)) {
            $mw->status = 'IN';
            if ($mw->count()) {
                // we should create it?
                $ff->page->syslog("No login found - but have multiple logins for {$this->user()->email}");
                return;
                
            }
            $ff->page->syslog("No login found - but appears to be logged in {$this->user()->email}");
            // allow multiwindows at present
            //$ff->page->jnotice("MULTI-WIN", "You have to many windows  open");
            // no record exists - it's ok - it's created later
            return;
        }
        if ($w->status == 'OUT') {
            $ff->page->syslog("User session appears to be logged out {$this->user()->email}");
            return;
        }
        
        if ($w->status == 'KILL') {
            $u->logout();
            session_regenerate_id(true);
            session_commit();
            $ff->page->jnotice("FORCE-LOGOUT", "this window must be reloaded");
        }
        $ww = clone($w);
        
        $w->last_access_dt = $w->sqlValue("NOW()");;
        //$w->state = 'IN'; // ?? needed?  since it can only get her eif status is in...
        $w->update($ww);
        
         
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
        $w = DB_DataObject::factory('core_person_window');
        $w->query("
                DELETE FROM
                    core_person_window
                WHERE
                    last_access_dt < NOW() - INTERVAL 1 DAY
                AND
                    last_access_dt > '1970-01-01'
        ");
        
        
    }
}

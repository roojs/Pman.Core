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
  
    public $force_logout;

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    function register($user, $req)
    {
        if (empty($req['window_id']) )   { // we don't do any checks on no window data.
            return;
        }
        
        $w = DB_DataObject::factory('core_person_window');
        $w->person_id = $user->id;
        $w->window_id = $req['window_id'];
        $ff = HTML_FlexyFramework::get();
		$w->app_id = $ff->appNameShort;
        $w->login_dt = $w->sqlValue("NOW()");
        
        if ($w->count() > 2) {
            $ff->page->jnotice("MULTI-WIN", "window already exists for user (max 2 per user)");
        }
        $w->insert();
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
            if ($mw->count() < 3) {
                // we should create it?
                $w->login_dt = $w->sqlValue("NOW()");
                $w->insert();
                return;
                
            }
            if (!empty($req['logout_other_windows'])) {
                foreach($mw->fetchAll() as $mw) {
                    $mmw = clone($mw);
                    $mw->delete();
                }
                return;
            }
            $ff->page->jnotice("MULTI-WIN", "You have to many windows  open");
            // no record exists - it's ok - it's created later
            return;
        }
        if ($w->force_logout) {
            $u->logout();
            session_regenerate_id(true);
            session_commit();
            $ff->page->jnotice("FORCE-LOGOUT", "this window must be reloaded");
        }
        
         
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
			$ww->delete();
		}
        
    }
    
}

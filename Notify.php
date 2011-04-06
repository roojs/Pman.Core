<?php
require_once 'MTrackWeb.php';

/**
 * notification script runner
 *
 * This does not actualy send stuf out, it only starts the NotifySend/{id}
 * which does the actuall notifcations.
 *
 * It manages a pool of notifiers.
 * 
 * 
 */

class Pman_Core_Notify extends Pman
{
    
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        if (!$ff->cli) {
            die("access denied");
        }
        HTML_FlexyFramework::ensureSingle(__FILE__, $this);
        return true;
        
    }
    
    var $pool = array();
    
    function get()    
    {
        //DB_DataObject::debugLevel(1);
        date_default_timezone_set('UTC');
        
        
        $w = DB_DataObject::factory('core_notify');
        $w->whereAdd('act_when < sent');
        $w->orderBy('act_when ASC'); // oldest first.
        $w->limit(1000); // we can run 1000 ...
        $ar = $w->fetchAll('id');
        
        while (true) {
            if (empty($ar)) {
                break;
            }
            if (!$this->poolfree()) {
                sleep(10);
                continue;
            }
            $p = array_shift($ar);
            $this->run($p);
        }
        
         
    }
    
    function run($id) {
        $php = 'php';
        $cwd = realpath(dirname(__FILE__) . '/../../');
        $app = 'index.php' .'/Core/NotifySend.php '. $id;
        $cmd = $php . ' ' . $app;
        $p = proc_open($cmd, $cwd , array());
        $this->pool[] = $p;
    }
    
    function poolfree() {
        foreach($this->pool as $p)
        
        
        
    }
    
    
}
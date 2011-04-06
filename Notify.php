<?php
require_once 'Pman.php';

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
    
    var $table = 'core_notify';
    var $target = 'Core/NotifySend';
    
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
        DB_DataObject::debugLevel(1);
        //date_default_timezone_set('UTC');
       // phpinfo();exit;
        
        $w = DB_DataObject::factory($this->table);
        $w->whereAdd('act_when > sent'); // eg.. sent is not valid..
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
        while($this->poolfree()) {
            sleep(10);
        }
        
        die("DONE\n");
    }
    
    function run($id)
    {
        
        $php = $_SERVER["_"];
        $cwd = getcwd(); // same as run on.. (so script should end up being same relatively..)
        $app = $cwd . '/'. $_SERVER["SCRIPT_NAME"] . '  ' . $this->target . '/'. $id;
        $cmd = $php . ' ' . $app;
        echo $cmd . "\n";
        $pipe = array();
        $p = proc_open($cmd, array(), $pipes, $cwd );
        $this->pool[] = $p;
    }
    
    function poolfree() {
        $pool = array();
        foreach($this->pool as $p) {
            $ar = proc_get_Status($p);
            if (!$p['running']) {
                $pool[] = $p;
            }
        }
        $this->pool = $pool;
        if (count($pool) < 10) {
            return true;
        }
        return false;
        
    }
    
    
}
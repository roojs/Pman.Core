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


class Pman_Core_NotifySend extends Pman
{
    
    var $table = 'core_notify'
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        if (!$ff->cli) {
            die("access denied");
        }
        //HTML_FlexyFramework::ensureSingle(__FILE__, $this);
        return true;
        
    }
    
    var $pool = array();
    
    function get($id)    
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
                sleep(3);
                continue;
            }
            $p = array_shift($ar);
            $this->run($p);
        }
        while(count($this->pool)) {
            $this->poolfree();
            sleep(3);
        }
        
        die("DONE\n");
    }
    
    function run($id)
    {
        $descriptorspec = array(
            0 => array("file", "/dev/null", 'r'),  // stdin is a pipe that the child will read from
            1 => array("file", "/dev/null", 'a'),  // stdout is a pipe that the child will write to
            2 => array("file", "/dev/null", "a") // stderr is a file to write to
         );
        $php = $_SERVER["_"];
        $cwd = getcwd(); // same as run on.. (so script should end up being same relatively..)
        $app = $cwd . '/'. $_SERVER["SCRIPT_NAME"] . '  ' . $this->target . '/'. $id;
        $cmd = $php . ' ' . $app;
        echo $cmd . "\n";
        $pipe = array();
        $p = proc_open($cmd, $descriptorspec, $pipes, $cwd );
        $this->pool[] = $p;
    }
    
    function poolfree() {
        $pool = array();
        foreach($this->pool as $p) {
            $ar = proc_get_Status($p);
            //var_dump($ar);
            if ($p['running']) {
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
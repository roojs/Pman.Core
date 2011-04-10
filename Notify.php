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
        $w->whereAdd('act_when < NOW()'); // eg.. not if future..

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
        
        $tn = tempnam(ini_get('session.save_path'),'stdout');
        $descriptorspec = array(
            0 => array("pipe", 'r'),  // stdin is a pipe that the child will read from
            1 => array("file", $tn, 'w'),  // stdout is a pipe that the child will write to
            2 => array("pipe", "w") // stderr is a file to write to
         );
        $php = $_SERVER["_"];
        $cwd = getcwd(); // same as run on.. (so script should end up being same relatively..)
        $app = $cwd . '/'. $_SERVER["SCRIPT_NAME"] . '  ' . $this->target . '/'. $id;
        $cmd = $php . ' ' . $app. ' &';
        echo $cmd . "\n";
        $pipe = array();
        $p = proc_open($cmd, $descriptorspec, $pipes, $cwd );
        $this->pool[] = array('proc' => $p, 'out' => $tn,  'cmd' => $cmd);
    }
    
    function poolfree() {
        $pool = array();
        foreach($this->pool as $p) {
            $ar = proc_get_status($p['proc']);
            print_r($p);
            print_r($ar);
            if ($ar['running']) {
                $pool[] = $p;
                continue;
            }
            echo $p['cmd'] . " : " . file_get_contents($p['out']);
            //unlink($p['out']);
        }
        $this->pool = $pool;
        if (count($pool) < 10) {
            return true;
        }
        return false;
        
    }
    
    
}
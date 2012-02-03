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
    
    static $cli_desc = "Send out notification emails (usually from cron)";
    
    static $cli_opts = array(
        'debug' => array(
            'desc' => 'Turn on debugging (see DataObjects debugLevel )',
            'default' => 0,
            'short' => 'v',
            'min' => 1,
            'max' => 1,
            
        ),
        'list' => array(
            'desc' => 'List message to send, do not send them..',
            'default' => 0,
            'short' => 'l',
            'min' => 0,
            'max' => 0,
            
        ),
        'old' => array(
            'desc' => 'Show old messages..',
            'default' => 0,
            'short' => 'o',
            'min' => 0,
            'max' => 0,
            
        ),
         'force' => array(
            'desc' => 'Force redelivery, even if it has been sent before or not queued...',
            'default' => 0,
            'short' => 'f',
            'min' => 0,
            'max' => 0,
        ),
    );
    
    
    
    var $table = 'core_notify';
    var $target = 'Core/NotifySend';
    var $evtype = ''; // any notification...
                    // this script should only handle EMAIL notifications..
    
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
    
    function get($r,$opts)    
    {
        if ($opts['debug']) {
            DB_DataObject::debugLevel($opts['debug']);
            print_r($opts);
        }
        //date_default_timezone_set('UTC');
       // phpinfo();exit;
        $showold = !empty($opts['old']);
        if (!empty($opts['old'])) {
            $opts['list'] = 1; // force listing..
        }
        
        $this->force = empty($opts['force']) ? 0 : 1;
     
        if (!empty($opts['send-to'])) {
            $this->send_to = $opts['send-to'];
        }
     
        
        $w = DB_DataObject::factory($this->table);
        
        if (!$showold) {
            $w->whereAdd('act_when > sent'); // eg.. sent is not valid..
            
            if (!$this->force) {
                $w->whereAdd('act_when < NOW()'); // eg.. not if future..
            }
    
            $w->orderBy('act_when ASC'); // oldest first.
            $w->limit(1000); // we can run 1000 ...
        } else {
            $w->orderBy('act_when DESC'); // latest first
            $w->limit(50); // we can run 1000 ...
        }
        if (!empty($this->evtype)) {
            $w->evtype = $this->evtype;
        }
        
        $w->autoJoin();
        
        
        $ar = $w->fetchAll();
        
        if (!empty($opts['list'])) {
            if (empty($ar)) {
                die("Nothing in Queue\n");
            }
            foreach($ar as $w) {
                $o = $w->object();
                
                
                echo "$w->id : $w->person_id_email email    : ".
                        $o->toEventString()."    ". $w->status() . "\n";
            }
            exit;
        }
        
        
        $pushed = array();
        while (true) {
            if (empty($ar)) {
                break;
            }
            
            
            $p = array_shift($ar);
            if (!$this->poolfree()) {
                array_unshift($ar,$p); /// put it back on..
                sleep(3);
                continue;
            }
            if ($this->poolHasDomain($p->person_id_email)) {
                if (in_array($p->person_id_email, $pushed)) {
                    // it's been pushed to the end, and nothing has
                    // been pushed since.
                    // give up, let the next run sort it out.
                    break;
                }
                
                $ar[] = $p; // push it on the end..
                
                $pushed[] = $p->person_id_email;
                
                echo "domain {$p->person_id_email} already on queue, pushing to end.\n";
                sleep(3);
                continue;
            }
            
            
            $this->run($p->id,$p->person_id_email);
            $pushed = array();
            
        }
        
        // we should have a time limit here...
        while(count($this->pool)) {
            $this->poolfree();
            sleep(3);
        }
        
        die("DONE\n");
    }
    
    function run($id, $email)
    {
       // phpinfo();exit;
        $tn = tempnam(ini_get('session.save_path'),'stdout') . '.stdout';
        $descriptorspec = array(
            0 => array("pipe", 'r'),  // stdin is a pipe that the child will read from
            1 => array("file", $tn, 'w'),  // stdout is a pipe that the child will write to
            2 => array("pipe", "w") // stderr is a file to write to
         );
        $php = $_SERVER["_"];
        $sn =  $_SERVER["SCRIPT_NAME"];
        
        $cwd = $sn[0] == '/' ? dirname($sn) : dirname(realpath(getcwd() . $sn)); // same as run on.. (so script should end up being same relatively..)
        $app = $cwd . '/' . basename($_SERVER["SCRIPT_NAME"]) . '  ' . $this->target . '/'. $id;
        if ($this->force) {
            $app .= ' -f';
        }
        if (!empty($this->send_to)) {
            $app .= ' --sent-to='.escapeshellarg($this->send_to);
        }
        $cmd = $php . ' ' . $app. ' &';
        
        echo $cmd . "\n";
        $pipe = array();
        $p = proc_open($cmd, $descriptorspec, $pipes, $cwd );
        $info =  proc_get_status($p);
        $this->pool[] = array(
                'proc' => $p,
                'pid' => $info['pid'],
                'out' => $tn,
                'cmd' => $cmd,
                'email' => $email,
                'pipes' => $pipes
            
                
        );
    }
    
    function poolfree()
    {
        $pool = array();
        clearstatcache();
        foreach($this->pool as $p) {
             
            echo "CHECK PID: " . $p['pid'] . "\n";
            $info =  proc_get_status($p['proc']);
            //var_dump($info);
            if ($info['pid']) {
                $p['pid'] = $info['pid'];
            }
            if ($info['running']) {
            
            //if (file_exists('/proc/'.$p['pid'])) {
                $pool[] = $p;
                continue;
            }
            
            echo "CLOSING: " . $p['cmd'] . " : " . file_get_contents($p['out']) . "\n";
            //fclose($p['pipes'][1]);
            fclose($p['pipes'][0]);
            fclose($p['pipes'][2]);
            proc_close($p['proc']);
            
            
            clearstatcache();
            if (file_exists('/proc/'.$p['pid'])) {
                $pool[] = $p;
                continue;
            }
            echo "ENDED: " . $p['cmd'] . " : " . file_get_contents($p['out']) . "\n";
            
            //unlink($p['out']);
        }
        echo "POOL SIZE: ". count($pool) ."\n";
        $this->pool = $pool;
        if (count($pool) < 10) {
            return true;
        }
        return false;
        
    }
    /**
     * see if pool is already trying to deliver to this domain.?
     * -- if so it get's pushed to the end of the queue.
     *
     */
    function poolHasDomain($email)
    {
        $dom = strtolower(array_pop(explode('@',$email)));
        foreach($this->pool as $p) {
            $mdom = strtolower(array_pop(explode('@',$p['email'])));
            if ($mdom == $dom) {
                return true;
            }
        }
        return false;
        
    }

}
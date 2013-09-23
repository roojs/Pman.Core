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
    
    static $cli_desc = "Runs the notification queue (usually from cron)
                        Normally used to sends out emails to anyone in the notification list.
    
                        /etc/cron.d/pman-core-notify
                        * *  * * *     www-data     /usr/bin/php /home/gitlive/web.mtrack/admin.php  Core/Notify > /dev/null
    
";
    
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
        'generate' => array(
            'desc' => 'Generate notifications for a table, eg. cash_invoice',
            'default' => '',
            'short' => 'g',
            'min' => 0,
            'max' => 1,
        ),
         'limit' => array(
            'desc' => 'Limit search for no. to send to ',
            'default' => 1000,
            'short' => 'L',
            'min' => 0,
            'max' => 999,
        ),
        'dryrun' => array(
            'desc' => 'Dry run - do not send.',
            'default' => 0,
            'short' => 'D',
            'min' => 0,
            'max' => 0,
        ),
        'poolsize' => array(
            'desc' => 'Pool size',
            'default' => 10,
            'short' => 'P',
            'min' => 0,
            'max' => 100,
        ),
    );
    /**
     * @var $nice_level Unix 'nice' level to stop it jamming server up.
     */
    var $nice_level = false;
    /**
     * @var $max_pool_size maximum runners to start at once.
     */
    var $max_pool_size = 10;
    /**
     * @var $max_to_domain maximum connections to make to a single domain
     */
    var $max_to_domain = 10;
    
    var $table = 'core_notify';
    var $target = 'Core/NotifySend';
    var $evtype = ''; // any notification...
                    // this script should only handle EMAIL notifications..
    var $force = false;
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
        $this->opts = $opts;
        if (!empty($opts['poolsize'])) {
            $this->max_pool_size = $opts['poolsize'];
        }
        
        if (empty($opts['limit'])) {
            $opts['limit'] = '1000'; // not sure why it's not picking up the defautl..
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
     
        
        $w = DB_DataObject::factory('core_notify_recur');
        if (is_a($w, 'DB_DataObject')) {
            $w->generateNotifications();
        }
        if (!empty($opts['generate'])) {
            $w = DB_DataObject::factory($opts['generate']);
            if (is_a($w, 'DB_DataObject')) {
                $w->generateNotifications();
            }
            exit;
            
            
        }
     
        //DB_DataObject::debugLevel(1);
        $w = DB_DataObject::factory($this->table);
        
        if (!$showold) {
            
            // standard
            
            //$w->whereAdd('act_when > sent'); // eg.. sent is not valid..
            $w->whereAdd("sent < '1970-01-01' OR sent IS NULL"); // eg.. sent is not valid..
            
            $w->whereAdd('act_start > NOW() - INTERVAL 14 DAY'); // ignore the ones stuck in the queue
            if (!$this->force) {
                $w->whereAdd('act_when < NOW()'); // eg.. not if future..
            }
    
            $w->orderBy('act_when ASC'); // oldest first.
            
            echo "QUEUE is {$w->count()}\n";
            
            $w->limit($opts['limit']); // we can run 1000 ...
        } else {
            $w->orderBy('act_when DESC'); // latest first
            $w->limit($opts['limit']); // we can run 1000 ...
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
        
        //echo "BATCH SIZE: ".  count($ar) . "\n";
        $pushed = array();
        $requeue = array();
        while (true) {
            
            
            echo "BATCH SIZE: ".  count($ar) . "\n";
            
            if (empty($ar)) {
                echo "COMPLETED MAIN QUEUE - running delated\n";
                
                if (empty($pushed)) {
                    break;
                }
                $ar = $pushed;
                $pushed = false;
                continue;
            }
            
            
            $p = array_shift($ar);
            if (!$this->poolfree()) {
                array_unshift($ar,$p); /// put it back on..
                sleep(3);
                continue;
            }
            if ($this->poolHasDomain($p->person_id_email) > $this->max_to_domain) {
                
                if ($pushed === false) {
                    // we only try once to requeue..
                    $requeue[] = $p;
                    continue;
                }
                $pushed[] = $p;
                
                
                //sleep(3);
                continue;
            }
            
            
            $this->run($p->id,$p->person_id_email);
            
            
            
        }
        
        // we should have a time limit here...
        while(count($this->pool)) {
            $this->poolfree();
             sleep(3);
        }
         
        foreach($requeue as $p) {
            $pp = clone($p);
            $p->act_when = $p->sqlValue('NOW + INTERVAL 1 MINUTE');
            $p->update($pp);
            
        }
        
        
        
        die("DONE\n");
    }
    
    function run($id, $email, $cmdOpts="")
    {
        
        static $renice = false;
        if (!$renice) {
            require_once 'System.php';
            $renice = System::which('renice');
        }
        
        // phpinfo();exit;
        $tn = tempnam(ini_get('session.save_path'),'stdout') . '.stdout';
        $descriptorspec = array(
            0 => array("pipe", 'r'),  // stdin is a pipe that the child will read from
            1 => array("file", $tn, 'w'),  // stdout is a pipe that the child will write to
            2 => array("pipe", "w") // stderr is a file to write to
         );
        $php = $_ENV["_"];
        $sn =  $_SERVER["SCRIPT_NAME"];
        
        $cwd = $sn[0] == '/' ? dirname($sn) : dirname(realpath(getcwd() . '/'. $sn)); // same as run on.. (so script should end up being same relatively..)
        $app = $cwd . '/' . basename($_SERVER["SCRIPT_NAME"]) . '  ' . $this->target . '/'. $id;
        if ($this->force) {
            $app .= ' -f';
        }
        if (!empty($this->send_to)) {
            $app .= ' --sent-to='.escapeshellarg($this->send_to);
        }
        $cmd = 'exec ' . $php . ' ' . $app . ' ' . $cmdOpts; //. ' &';
        
       
        $pipe = array();
        echo "call proc_open $cmd\n";
        
        
        if ($this->max_pool_size === 1) {
            passthru($cmd);
            return;
        }
        
        
        if (!empty($this->opts['dryrun'])) {
            echo "DRY RUN\n";
            return;
        }
        
        $p = proc_open($cmd, $descriptorspec, $pipes, $cwd );
        $info =  proc_get_status($p);
        
        if ($this->nice_level !== false) { 
            $rcmd = "$renice {$this->nice_level} {$info['pid']}";
            `$rcmd`;
        } 
        $this->pool[] = array(
                'proc' => $p,
                'pid' => $info['pid'],
                'out' => $tn,
                'cmd' => $cmd,
                'email' => $email,
                'pipes' => $pipes,
                'started' => time()
            
                
        );
        echo "RUN ({$info['pid']}) $cmd  \n";
    }
    
    function poolfree()
    {
        $pool = array();
        clearstatcache();
        $maxruntime = 2 * 60; // 2 minutes.. ?? should be long enoguh
        
        foreach($this->pool as $p) {
             
            //echo "CHECK PID: " . $p['pid'] . "\n";
            $info =  proc_get_status($p['proc']);
            //var_dump($info);
            
            // update if necessday.
            if ($info['pid'] && $p['pid'] != $info['pid']) {
                echo "CHANING PID FROM " . $p['pid']  .  "  TO ". $info['pid']. "\n";
                $p['pid'] = $info['pid'];
            }
            
            //echo @file_get_contents('/proc/'. $p['pid'] .'/cmdline') . "\n";
            
            if ($info['running']) {
            
                //if (file_exists('/proc/'.$p['pid'])) {
                $runtime = time() - $p['started'];
                //echo "RUNTIME ({$p['pid']}): $runtime\n";
                if ($runtime > $maxruntime) {
                    
                    proc_terminate($p['proc'], 9);
                    //fclose($p['pipes'][1]);
                    fclose($p['pipes'][0]);
                    fclose($p['pipes'][2]);
                    echo "\nTERMINATING: ({$p['pid']}) " . $p['cmd'] . " : " . file_get_contents($p['out']) . "\n";
                    @unlink($p['out']);
                    
                    continue;
                }
                
                $pool[] = $p;
                continue;
            }
            fclose($p['pipes'][0]);
            fclose($p['pipes'][2]);
            //echo "CLOSING: ({$p['pid']}) " . $p['cmd'] . " : " . file_get_contents($p['out']) . "\n";
            //fclose($p['pipes'][1]);
            
            proc_close($p['proc']);
            
            
            //clearstatcache();
            //if (file_exists('/proc/'.$p['pid'])) {
            //    $pool[] = $p;
            //    continue;
            //}
            echo "\nENDED: ({$p['pid']}) " .  $p['cmd'] . " : " . file_get_contents($p['out']) . "\n";
            @unlink($p['out']);
            //unlink($p['out']);
        }
        echo "POOL SIZE: ". count($pool) ."\n";
        $this->pool = $pool;
        if (count($pool) < $this->max_pool_size) {
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
        $ret = 0;
        $dom = strtolower(array_pop(explode('@',$email)));
        foreach($this->pool as $p) {
            $mdom = strtolower(array_pop(explode('@',$p['email'])));
            if ($mdom == $dom) {
                $ret++;
            }
        }
        return $ret;
        
    }

    function output()
    {
        die("Done\n");
    }
}
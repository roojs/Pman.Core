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
            'desc' => 'Show old messages.. (and new messages...)',
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
       /* removed - use GenerateNotifcations.php hooked classes
         'generate' =>  'Generate notifications for a table, eg. cash_invoice',
            
        ),
        */
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
    
    /**
     * @var $maxruntime - maximum seconds a child is allowed to run - defaut 2 minutes
     */
    var $maxruntime = 120;
    
    /**
    * @var {Boolean} log_events - default true if events should be logged.
    */
    var $log_events = true;
    /**
    * @var {Number} try_again_minutes how long after failing to try again default = 30 if max runtime fails
    */
    var $try_again_minutes = 30;
    
    /**
     * @var {String} table - the table that the class will query for notification events
     */
    var $table = 'core_notify';
    /**
     * @var {String} target - the application that will run for each Row in the table (eg. Pman/Core/NotifySend)
     */
    var $target = 'Core/NotifySend';
    
    
    
    var $evtype = ''; // any notification...
                    // this script should only handle EMAIL notifications..
    
    var $server_id;
    
    var $poolname = 'core';
    
    var $opts; 
    var $force = false;
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        if (!$ff->cli) {
            die("access denied");
        }
        HTML_FlexyFramework::ensureSingle($_SERVER["SCRIPT_NAME"] .'|'. __FILE__ .'|'. (empty($_SERVER['argv'][1]) ? '': $_SERVER['argv'][1]), $this);
        return true;
    }
    
    var $pool = array();
    
    function parseArgs(&$opts)
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
        
        if (!empty($opts['old'])) {
            $opts['list'] = 1; // force listing..
        }
        
        $this->force = empty($opts['force']) ? 0 : 1;
     
        if (!empty($opts['send-to'])) {
            $this->send_to = $opts['send-to'];
        }
    }
    
    var $queue = array();
    var $domain_queue = array(); // false to use nextquee
    var $next_queue = array();

   
    function get($r,$opts=array())    
    {
        $this->parseArgs($opts); 
         
        //date_default_timezone_set('UTC');
        
        
        $this->generateNotifications();
        
        $this->assignQueues();
        
        //DB_DataObject::debugLevel(1);
        $w = DB_DataObject::factory($this->table);
        $total = 0;
        
        
        
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->Core_Notify['servers']) && empty($ff->Core_Notify['servers-non-pool'][gethostname()])) {
            
            if (!isset($ff->Core_Notify['servers'][gethostname()])) {
                $this->jerr("Core_Notify['servers']['" . gethostname() ."'] is not set");
            }
            $w->server_id = array_search(gethostname(),array_keys($ff->Core_Notify['servers']));
        }
        if (!empty($this->evtype)) {
            $w->evtype = $this->evtype;
        }
        
        
        
        if (!empty($opts['old'])) {
            // show old and new...
            
            $w->orderBy('act_when DESC'); // latest first
            $w->limit($opts['limit']); // we can run 
            $total = min($w->count(), $opts['limit']);
        } else {   
            // standard
            
            //$w->whereAdd('act_when > sent'); // eg.. sent is not valid..
            $w->whereAdd("sent < '1970-01-01' OR sent IS NULL"); // eg.. sent is not valid..
            
            $w->whereAdd('act_start > NOW() - INTERVAL 14 DAY'); // ignore the ones stuck in the queue
            if (!$this->force) {
                $w->whereAdd('act_when < NOW()'); // eg.. not if future..
            }
    
            $w->orderBy('act_when ASC'); // oldest first.
            $total = min($w->count(), $opts['limit']);
            $this->logecho("QUEUE is {$w->count()} only running " . ((int) $opts['limit']));
            
            $w->limit($opts['limit']); // we can run 1000 ...
        }
        
        
        
    
        
         
        $w->autoJoin();
        $total = $w->find();
        
        if (empty($total)) {
            $this->logecho("Nothing In Queue - DONE");
            exit;
        }
        
        
        if (!empty($opts['list'])) {
            
            
            while ($w->fetch()) { 
                $o = $w->object();
                
                
                $this->logecho("{$w->id} : {$w->person()->email} email    : ".
                        $o->toEventString()."    ". $w->status()  );
            }
            exit;
        }
        
        //echo "BATCH SIZE: ".  count($ar) . "\n";
       
        
        while (true) {
            // only add if we don't have any queued up..
            if (empty($this->queue) && $w->fetch()) {
                $this->queue[] = clone($w);
                $total--;
            }
          
            $this->logecho("BATCH SIZE: Queue=".  count($this->queue) . " TOTAL = " . $total  );
            
            if (empty($this->queue)) {
                $this->logecho("COMPLETED MAIN QUEUE - running maxed out domains");
                if ($this->domain_queue !== false) {
                    $this->queue  = $this->remainingDomainQueue();
                     
                    continue;
                }
                break; // nothing more in queue.. and no remaining one
            }
            
            
            $p = array_shift($this->queue);
            if (!$this->poolfree()) {
                array_unshift($this->queue,$p); /// put it back on..
                sleep(3);
                continue;
            }
            // not sure what happesn if person email and to_email is empty!!?
            $email = empty($p->to_email) ? ($p->person() ? $p->person()->email : $p->to_email) : $p->to_email;
            
            $black = $this->isBlacklisted($email);
            if ($black !== false) {
                $this->logecho("DOMAIN blacklisted - {$email} - moving to another pool");
                $this->updateServer($p, $black);
                continue;
            }
             
            
            if ($this->poolHasDomain($email) > $this->max_to_domain) {
                
                // push it to a 'domain specific queue'
                $this->logecho("REQUEING - maxed out that domain - {$email}");
                $this->pushQueueDomain($p, $email);
                  
                
                //sleep(3);
                continue;
            }
            
            
            $this->run($p->id,$email);
            
            
            
        }
         $this->logecho("REQUEUING all emails that maxed out:" . count($this->next_queue));
        if (!empty($this->next_queue)) {
             
            foreach($this->next_queue as $p) {
                $this->updateServer($p);
            }
        }
        
        
        $this->logecho("QUEUE COMPLETE - waiting for pool to end");
        // we should have a time limit here...
        while(count($this->pool)) {
            $this->poolfree();
            sleep(3);
        }
         
        
        
        
        $this->logecho("DONE");
        exit;
    }
    
    
    function isBlacklisted($email)
    {
        // return current server id..
        $ff = HTML_FlexyFramework::get();
        //$this->logecho("CHECK BLACKLISTED - {$email}");
        if (empty($ff->Core_Notify['servers'])) {
            return false;
        }
      
        if (!isset($ff->Core_Notify['servers'][gethostname()]['blacklisted'])) {
            return false;
        }
       
        // get the domain..
        $ea = explode('@',$email);
        $dom = strtolower(array_pop($ea));
        
        //$this->logecho("CHECK BLACKLISTED DOM - {$dom}");
        if (!in_array($dom, $ff->Core_Notify['servers'][gethostname()]['blacklisted'] )) {
            return false;
        }
        //$this->logecho("RETURN BLACKLISTED TRUE");
        return array_search(gethostname(),array_keys($ff->Core_Notify['servers']));
    }
    
    // this sequentially distributes requeued emails.. - to other servers. (can exclude current one if we have that flagged.)
    function updateServer($ww, $exclude = -1)
    {
        $w = DB_DataObject::factory($ww->tableName());
        $w->get($ww->id);
        
        $ff = HTML_FlexyFramework::get();
        static $num = 0;
        if (empty($ff->Core_Notify['servers'])) {
            return;
        }
        $num = ($num+1) % count(array_keys($ff->Core_Notify['servers']));
        if ($exclude == $num ) {
            $num = ($num+1) % count(array_keys($ff->Core_Notify['servers']));
        }
        // next server..
        $pp = clone($w);
        $w->server_id = $num;
                    
        $w->act_when = $w->sqlValue('NOW() + INTERVAL 1 MINUTE');
        $w->update($pp);
        
         
    }
  
    
    function generateNotifications()
    {
        // this should check each module for 'GenerateNotifications.php' class..
        //and run it if found..
        $ff = HTML_FlexyFramework::get();
       
        $disabled = explode(',', $ff->disable);

        $modules = array_reverse($this->modulesList());
        
        // move 'project' one to the end...
        
        foreach ($modules as $module){
            if(in_array($module, $disabled)){
                continue;
            }
            $file = $this->rootDir. "/Pman/$module/GenerateNotifications.php";
            if(!file_exists($file)){
                continue;
            }
            
            require_once $file;
            $class = "Pman_{$module}_GenerateNotifications";
            $x = new $class;
            if(!method_exists($x, 'generate')){
                continue;
            };
            //echo "$module\n";
            $x->generate($this);
        }
                
    
    }
    
    function assignQueues()
    {
        
        DB_DataObject::Factory('core_notify_server')->assignQueues($this);
         
        
    }
    
    function run($id, $email='', $cmdOpts="")
    {
        
        static $renice = false;
        if (!$renice) {
            require_once 'System.php';
            $renice = System::which('renice');
        }
        
        // phpinfo();exit;
        
        
        $tn =  $this->tempName('stdout', true);
        $descriptorspec = array(
            0 => array("pipe", 'r'),  // stdin is a pipe that the child will read from
            1 => array("file", $tn, 'w'),  // stdout is a pipe that the child will write to
            2 => array("pipe", "w") // stderr is a file to write to
         );
        
        static $php = false;
        if (!$php) {
            require_once 'System.php';
            $php = System::which('php');
        }
        
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
        //$this->logecho("call proc_open $cmd");
        
        
        if ($this->max_pool_size === 1) {
            $this->logecho("call passthru [{$email}] $cmd");
            passthru($cmd);
            return;
        }
        
        
        if (!empty($this->opts['dryrun'])) {
            $this->logecho("DRY RUN");
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
                'notify_id' => $id,
                'started' => time()
            
                
        );
        $this->logecho("RUN [{$email}] ({$info['pid']}) $cmd ");
    }
    
    function poolfree()
    {
        $pool = array();
        clearstatcache();
         
        foreach($this->pool as $p) {
             
            //echo "CHECK PID: " . $p['pid'] . "\n";
            $info =  proc_get_status($p['proc']);
            //var_dump($info);
            
            // update if necessday.
            if ($info['pid'] && $p['pid'] != $info['pid']) {
                $this->logecho("CHANING PID FROM " . $p['pid']  .  "  TO ". $info['pid']);
                $p['pid'] = $info['pid'];
            }
            
            //echo @file_get_contents('/proc/'. $p['pid'] .'/cmdline') . "\n";
            
            if ($info['running']) {
            
                //if (file_exists('/proc/'.$p['pid'])) {
                $runtime = time() - $p['started'];
                //echo "RUNTIME ({$p['pid']}): $runtime\n";
                if ($runtime > $this->maxruntime) {
                    
                    proc_terminate($p['proc'], 9);
                    //fclose($p['pipes'][1]);
                    fclose($p['pipes'][0]);
                    fclose($p['pipes'][2]);
                    $this->logecho("TERMINATING: ({$p['pid']}) {$p['email']} " . $p['cmd'] . " : " . file_get_contents($p['out']));
                    @unlink($p['out']);
                    
                    // schedule again
                    $w = DB_DataObject::factory($this->table);
                    $w->get($p['notify_id']);
                    $ww = clone($w);
                    if ($this->log_events) {
                        $this->addEvent('NOTIFY', $w, 'TERMINATED - TIMEOUT');
                    }
                    $w->act_when = date('Y-m-d H:i:s', strtotime("NOW + {$this->try_again_minutes} MINUTES"));
                    $w->update($ww);
                    
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
            $this->logecho("ENDED: ({$p['pid']}) {$p['email']} " .  $p['cmd'] . " : " . file_get_contents($p['out']) );
            @unlink($p['out']);
            // at this point we could pop onto the queue the 
            $this->popQueueDomain($p['email']);
            
            //unlink($p['out']);
        }
        $this->logecho("POOL SIZE: ". count($pool) );
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
        $ea = explode('@',$email);
        $dom = strtolower(array_pop($ea));
        foreach($this->pool as $p) {
            $ea = explode('@',$p['email']);
            $mdom = strtolower(array_pop($ea));
            if ($mdom == $dom) {
                $ret++;
            }
        }
        return $ret;
        
    }
    function popQueueDomain($email)
    {
        $ea = explode('@',$email);
        $dom = strtolower(array_pop($ea));
        if (empty($this->domain_queue[$dom])) {
            return;
        }
        array_unshift($this->queue, array_shift($this->domain_queue[$dom]));
        
    }
    
    function pushQueueDomain($e, $email)
    {
        if ($this->domain_queue === false) {
            $this->next_queue[] = $e;
            return;
        }
        
        $ea = explode('@',$email);
        $dom = strtolower(array_pop($ea));
        if (!isset($this->domain_queue[$dom])) {
            $this->domain_queue[$dom] = array();
        }
        $this->domain_queue[$dom][] = $e;
    }
    function remainingDomainQueue()
    {
        $ret = array();
        foreach($this->domain_queue as $dom => $ar) {
            $ret = array_merge($ret, $ar);
        }
        $this->domain_queue = false;
        return $ret;
    }
    
    

    function output()
    {
        $this->logecho("DONE");
        exit;
    }
    function logecho($str)
    {
        echo date("Y-m-d H:i:s - ") . $str . "\n";
    }
}

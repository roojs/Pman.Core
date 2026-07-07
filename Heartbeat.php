<?php

// check if database is workign - used by nagios checking - to see if server is really up.

require_once 'Pman.php';

class Pman_Core_Heartbeat extends Pman
{
    function getAuth()
    {
        return true;
    }
    
    function get($req, $opts = array())
    {
        $this->post($req);
        die("POST only");
    }
    
    function post($req)
    {
        $this->initErrorHandling();
        
        if ($this->database_is_locked()) {
            $this->handle_database_locked();
            die("FAILED");
        }
        
        // Database is fine - clean up state and lock files
        if (file_exists(session_save_path() . '/heartbeat_kill_state.json')) {
            @unlink(session_save_path() . '/heartbeat_kill_state.json');
        }
        if (file_exists(session_save_path() . '/heartbeat_kill.lock')) {
            @unlink(session_save_path() . '/heartbeat_kill.lock');
        }
        
        // Use gethostbyaddr("127.0.1.1") to get FQN from hosts file
        die(DB_DataObject::Factory('core_heartbeat')->hostCheck(gethostbyaddr("127.0.1.1")));
    }
    
    function handle_database_locked()
    {
        $stateFile = session_save_path() . '/heartbeat_kill_state.json';
        $lockFile = session_save_path() . '/heartbeat_kill.lock';
        
        // Acquire file lock (non-blocking)
        if (!file_exists(session_save_path())) {
            $oldumask = umask(0);
            mkdir(session_save_path(), 0775, true);
            umask($oldumask);
        }
        
        $lock_fp = fopen($lockFile, "a");
        if (!$lock_fp) {
            return; // Can't open lock file, skip
        }
        
        if (!flock($lock_fp, LOCK_EX | LOCK_NB)) {
            fclose($lock_fp);
            return; // Another instance is running
        }
        
        // Read state from JSON file (or create new state if file doesn't exist)
        $state = array();
        if (file_exists($stateFile) && ($stateContent = file_get_contents($stateFile)) !== false) {
            $state = json_decode($stateContent, true);
            $state = is_array($state) ? $state : array();
        }
        
        // Update state with current down status and timestamp
        $state['down_since'] = empty($state['down_since']) ?  time()  : $state['down_since'];
        
        // Check if down for > 5 minutes and kill queries if needed
        $this->checkAndKillQueries($state,  time()); 
        
        // Write state to JSON file
        file_put_contents($stateFile, json_encode($state));
        
        // Release file lock
        flock($lock_fp, LOCK_UN);
        fclose($lock_fp);
    }
    
    function checkAndKillQueries(&$state, $now)
    {
        // Check if down for > 5 minutes
        if (($now - $state['down_since']) <= 300) { // 5 minutes = 300 seconds
            return;
        }
        
        // Check if kill was executed in last 10 minutes
        if (($now - (isset($state['last_kill_time']) ? $state['last_kill_time'] : 0)) < 600) { // 10 minutes = 600 seconds
            return;
        }
        
        // Check for processes with State containing "killed" or "Killed"
        $cd = DB_DataObject::Factory('core_enum');
        $cd->query("
            SELECT 
                COUNT(*) as cnt
            FROM
                INFORMATION_SCHEMA.PROCESSLIST
            WHERE
                UPPER(STATE) LIKE '%KILLED%'
            AND
                COMMAND IN ('Query')
            AND 
                USER = 'release'
            AND 
                DB IS NOT NULL
        ");
        if ($cd->fetch() && $cd->cnt > 0) {
            return;
        }
        
        // Query INFORMATION_SCHEMA.PROCESSLIST to get process IDs to kill
        $cd = DB_DataObject::Factory('core_enum');
        $cd->query("
            SELECT 
                ID
            FROM
                INFORMATION_SCHEMA.PROCESSLIST
            WHERE
                COMMAND IN ('Query')
            AND 
                USER = 'release'
            AND 
                DB IS NOT NULL
        ");
    
        $killedCount = 0;
        while ($cd->fetch()) {
            
                // Execute KILL command using database connection
            DB_DataObject::Factory('core_enum')->query("KILL " . (int)$cd->ID);
            $killedCount++;
            
        }
        
        // Update last_kill_time in state
        $state['last_kill_time'] = $now;
        
        // Log execution
        if ($killedCount > 0) {
            $this->errorlog("Heartbeat: Killed {$killedCount} MySQL queries from 'release' user");
        }
    }
    
     
    function onPearError($err)
    {
      //  print_r($err);
        die("FAILED");
    }
    function onException($err)
    {
      //  print_r($err);
        die("FAILED");
    }
    
   
}
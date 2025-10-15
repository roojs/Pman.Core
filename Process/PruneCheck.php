<?php

/**
 * Nagios monitoring for Core pruning operations
 * 
 * Monitors the backlog of records that need to be pruned in Core tables
 * and reports warning/critical status based on how many pruning runs would be needed.
 * 
 * @author System
 */

require_once 'Pman.php';

class Pman_Core_Process_PruneCheck extends Pman
{
    static $cli_desc = "Core Prune Check -- Nagios monitoring for Core pruning backlog";
    static $cli_opts = array(
        'table' => array(
            'desc' => 'Specific table to check (core_notify, Events, Events_duplicates)',
            'default' => '',
            'short' => 't',
        ),
        'warning' => array(
            'desc' => 'Warning threshold (number of runs needed)',
            'default' => 5,
            'short' => 'w',
            'min' => 1,
            'max' => 1,
        ),
        'critical' => array(
            'desc' => 'Critical threshold (number of runs needed)',
            'default' => 10,
            'short' => 'c',
            'min' => 1,
            'max' => 1,
        ),
        'months' => array(
            'desc' => 'How many months to check for (default: 6)',
            'default' => 6,
            'short' => 'm',
            'min' => 1,
            'max' => 1,
        )
    );
    
    var $cli = false;
    var $opts = array();
    var $results = array();
    
    function getAuth() 
    {
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->cli)) {
            $this->cli = true;
            return true;
        }
        return false;
    }
    
    function get($m="", $opts=array())
    {
        $this->opts = $opts;
        
         
        // Use introspection to find all check methods
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (strpos($method, 'check_') === false) {
                continue;
            }
                
            // If specific table requested, only run that check
            if (!empty($opts['table']) && $opts['table'] != substr($method, 6)) {
                continue;
            }
                
            $this->$method();
            
        }
        
        $this->outputNagiosResults();
    }
    
    /**
     * Check core_notify table pruning status
     */
    function check_core_notify()
    {
        $cn = DB_DataObject::factory('core_notify');
        $cn_old = DB_DataObject::factory('core_notify');
        $events = DB_DataObject::factory('Events');
        
        $cn_old->whereAdd("act_when < NOW() - INTERVAL {$this->opts['months']} MONTH");
        $prunable_event_ids = $cn_old->fetchAll('id', 'event_id');
        
        $events->whereAddIn('id', $prunable_event_ids, 'int');

        $this->results['core_notify'] = array(
            'table' => 'core_notify',
            'total_records' => $cn->count(),
            'prunable_records' => count($prunable_event_ids),
            'prunable_event_records' => $events->count(),
            'prunable_records_per_run' => 10000
        );
    }
    
    /**
     * Check Events table pruning status (for archiving)
     */
    function check_Events()
    {
        $events = DB_DataObject::factory('Events');
        $events_old = DB_DataObject::factory('Events');
        
        $events_old->whereAdd("event_when < NOW() - INTERVAL {$this->opts['months']} MONTH");
        
        $this->results['Old Events'] = array(
            'table' => 'Events',
            'total_records' => $events->count(),
            'prunable_records' => $events_old->count(),
            'prunable_records_per_run' => 500000
        );
    }
    
    /**
     * Check Events table for duplicate NOTIFY records that need cleanup
     */
    function check_Events_duplicates()
    {
        $events = DB_DataObject::factory('Events');
        $events_dup = DB_DataObject::factory('Events');
        
        $events_dup->selectAdd();
        $events_dup->selectAdd("on_id, on_table, min(id) as min_id, max(id) as max_id, count(*) as mm");
        $events_dup->whereAdd("action = 'NOTIFY' and event_when < NOW() - INTERVAL 1 WEEK");
        $events_dup->groupBy('on_id, on_table');
        $events_dup->having("mm > 2");
        $events_dup->orderBy('mm desc');
       
        
        $this->results['Duplicate Events'] = array(
            'table' => 'Duplicate Events',
            'total_records' => $events->count(),
            'prunable_records' => array_sum(array_map(function($group) {
                     return $group->mm - 1;
                }, $events_dup->fetchAll() )),
            'prunable_groups_per_run' => 10000
        );
    }
    
    /**
     * Output results in Nagios format
     */
    function outputNagiosResults()
    {
        $status_str = array('OK', 'WARNING', 'CRITICAL');
        $overall_status = 0;
        $status_messages = array();
        
        foreach ($this->results as $table => $result) {
            // Calculate runs needed on-the-fly
            $records_per_run = isset($result['prunable_groups_per_run']) ? $result['prunable_groups_per_run'] : $result['prunable_records_per_run'];
            $runs_needed = ceil($result['prunable_records'] / $records_per_run);
            
            // Calculate status on-the-fly
            $status = $runs_needed >= $this->opts['critical'] ? 2 :
                     ($runs_needed >= $this->opts['warning'] ? 1 : 0);

            
            // Determine overall status (worst status wins)
            $overall_status = max($overall_status, $status);
            
            
            // Build status message
            $status_messages[] = sprintf(
                "%s - %s: %d / %d prunable records, %d runs needed" .
                    " (%d " . (isset($result['prunable_groups_per_run']) ? 'groups of ' : '') . 
                    "records per run)",
                $status_str[$status],
                $table,
                $result['prunable_records'],
                $result['total_records'],
                $runs_needed,
                $records_per_run
            );

            if(isset($result['prunable_event_records'])){
                $status_messages[] = sprintf(
                    "-------- %s: %d prunable event records",
                    $table,
                    $result['prunable_event_records']
                );
            }
        }
      
        
        echo implode("\n", $status_messages)  . "\n";
        
        // Exit with appropriate code
        exit($overall_status);
    }
}

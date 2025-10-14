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

class Pman_Core_PruneCheck extends Pman
{
    static $cli_desc = "Core Prune Check -- Nagios monitoring for Core pruning backlog";
    static $cli_opts = array(
        'table' => array(
            'desc' => 'Specific table to check (core_notify, Events)',
            'default' => '',
            'short' => 't',
            'min' => 1,
            'max' => 1,
        ),
        'warning' => array(
            'desc' => 'Warning threshold (number of runs needed)',
            'default' => '5',
            'short' => 'w',
            'min' => 0,
            'max' => 1,
        ),
        'critical' => array(
            'desc' => 'Critical threshold (number of runs needed)',
            'default' => '10',
            'short' => 'c',
            'min' => 0,
            'max' => 1,
        ),
        'months' => array(
            'desc' => 'How many months to check for (default: 6)',
            'default' => '6',
            'short' => 'm',
            'min' => 1,
            'max' => 1,
        )
    );
    
    var $cli = false;
    var $warning_threshold = 5;
    var $critical_threshold = 10;
    var $months = 6;
    var $results = array();
    
    function getAuth() {
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->cli)) {
            $this->cli = true;
            return true;
        }
        return false;
    }
    
    function get($m="", $opts=array())
    {
        $this->warning_threshold = isset($opts['warning']) ? (int)$opts['warning'] : 5;
        $this->critical_threshold = isset($opts['critical']) ? (int)$opts['critical'] : 10;
        $this->months = isset($opts['months']) ? (int)$opts['months'] : 6;
        
        $specific_table = isset($opts['table']) ? $opts['table'] : null;
        
        // Check Core pruning tables
        if (!$specific_table || $specific_table === 'core_notify') {
            $this->checkCoreNotify();
        }
        
        if (!$specific_table || $specific_table === 'Events') {
            $this->checkEvents();
        }
        
        $this->outputNagiosResults();
    }
    
    /**
     * Check core_notify table pruning status
     */
    function checkCoreNotify()
    {
        echo "Checking core_notify table\n";
        // Count total records
        $cn = DB_DataObject::factory('core_notify');
        $total_records = $cn->count();
        
        // Count records that would be archived (older than specified months)
        $cn = DB_DataObject::factory('core_notify');
        $cn->whereAdd("act_when < NOW() - INTERVAL {$this->months} MONTH");
        $prunable_event_ids = $cn->fetchAll('id', 'event_id');
        $prunable_records = count($prunable_event_ids);
        
        // Calculate runs needed (based on 10,000 limit per run from Core_notify_archive)
        $runs_needed = ceil($prunable_records / 10000);

        // Count records that would be archived (linked to core_notify records to be pruned)
        $events = DB_DataObject::factory('Events');
        $events->whereAddIn('id', $prunable_event_ids, 'int');
        $prunable_event_records = $events->count();

        $this->results['core_notify'] = array(
            'table' => 'core_notify',
            'total_records' => $total_records,
            'prunable_records' => $prunable_records,
            'prunable_event_records' => $prunable_event_records,
            'runs_needed' => $runs_needed,
            'status' => $this->getStatus($runs_needed)
        );
    }
    
    /**
     * Check Events table pruning status (for archiving)
     */
    function checkEvents()
    {
        echo "Checking Events table\n";
        // Count total records
        $events = DB_DataObject::factory('Events');
        $total_records = $events->count();
        
        // Count records that would be archived (older than specified months)
        $events = DB_DataObject::factory('Events');
        $events->whereAdd("event_when < NOW() - INTERVAL {$this->months} MONTH");
        $prunable_records = $events->count();
        
        // Calculate runs needed (based on 10,000 limit per run from Core_events_archive)
        $runs_needed = ceil($prunable_records / 10000);
        
        $this->results['Events'] = array(
            'table' => 'Events',
            'total_records' => $total_records,
            'prunable_records' => $prunable_records,
            'prunable_event_records' => 0, // Not applicable
            'runs_needed' => $runs_needed,
            'status' => $this->getStatus($runs_needed)
        );
    }
    
    /**
     * Determine status based on runs needed
     */
    function getStatus($runs_needed)
    {
        if ($runs_needed >= $this->critical_threshold) {
            return 'CRITICAL';
        } elseif ($runs_needed >= $this->warning_threshold) {
            return 'WARNING';
        } else {
            return 'OK';
        }
    }
    
    /**
     * Output results in Nagios format
     */
    function outputNagiosResults()
    {
        $overall_status = 'OK';
        $status_messages = array();
        
        foreach ($this->results as $table => $result) {
            // Determine overall status (worst status wins)
            if ($result['status'] === 'CRITICAL') {
                $overall_status = 'CRITICAL';
            } elseif ($result['status'] === 'WARNING' && $overall_status !== 'CRITICAL') {
                $overall_status = 'WARNING';
            }
            
            // Build status message
            $status_messages[] = sprintf(
                "%10s | %10d | %10d | %10d",
                $table,
                $result['total_records'],
                $result['prunable_records'],
                $result['runs_needed']
            );
        }
        
        // Output Nagios format
        $message = implode("\n", $status_messages);
        
        echo $overall_status . "\n";
        echo sprintf("%10s | %10s | %10s | %10s\n", "Table", "Total", "Prunable", "Runs Needed");
        echo $message . "\n";
        
        // Exit with appropriate code
        switch ($overall_status) {
            case 'CRITICAL':
                exit(2);
            case 'WARNING':
                exit(1);
            case 'OK':
            default:
                exit(0);
        }
    }
}

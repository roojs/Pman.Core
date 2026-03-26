<?php

require_once 'Pman.php';

/**
 * CLI: list unsent core_notify rows (id, to, from, subject) for queue tracking.
 * All servers; no extra filters beyond unsent sent.
 *
 * php admin.php Core/Notify/Queue [--verbose] [-L N]
 */
class Pman_Core_Notify_Queue extends Pman
{
    static $cli_desc = 'List unsent core_notify rows (all servers): id, to, from, subject.';
    
    static $cli_opts = array(
        'debug' => array(
            'desc' => 'Turn on DataObjects debug logging',
            'default' => 0,
            'min' => 0,
            'max' => 1,
        ),
        'verbose' => array(
            'desc' => 'Include act_when, evtype, server_id, ontable, onid',
            'default' => 0,
            'short' => 'v',
            'min' => 0,
            'max' => 0,
        ),
        'limit' => array(
            'desc' => 'Max rows to print',
            'default' => 500,
            'short' => 'L',
            'min' => 0,
            'max' => 99999,
        ),
    );
    
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        if (!$ff->cli) {
            die("access denied");
        }
        return true;
    }
    
    function get($r, $opts = array())
    {
        if (!empty($opts['debug'])) {
            DB_DataObject::debugLevel($opts['debug']);
        }
        
        $limit = isset($opts['limit']) ? (int) $opts['limit'] : 500;
        if ($limit < 1) {
            $limit = 500;
        }
        
        $w = DB_DataObject::factory('core_notify');
        $w->selectAdd();
        $w->selectAdd("
            core_notify.id,
            core_notify.to_email,
            core_notify.act_when,
            core_notify.evtype,
            core_notify.server_id,
            core_notify.ontable,
            core_notify.onid,
            core_email.subject AS join_subject,
            core_email.from_email AS join_from_email,
            core_email.from_name AS join_from_name
        ");
        $w->joinAdd(array('email_id', 'core_email:id'), 'LEFT');
        
        $w->whereAdd("core_notify.sent < '1970-01-01' OR core_notify.sent IS NULL");
        
        $w->orderBy('core_notify.act_when ASC');
        $w->limit($limit);
        
        $count = $w->find();
        if (empty($count)) {
            echo "Nothing in queue (0 rows).\n";
            return;
        }
        
        $verbose = !empty($opts['verbose']);
        if ($verbose) {
            echo str_pad('id', 8) . str_pad('to', 36) . str_pad('from', 40) . str_pad('subject', 56)
                . "act_when            evtype  srv ontable:onid\n";
        } else {
            echo str_pad('id', 8) . str_pad('to', 40) . str_pad('from', 44) . "subject\n";
        }
        echo str_repeat('-', $verbose ? 120 : 100) . "\n";
        
        while ($w->fetch()) {
            $this->printRow($w, $verbose);
        }
    }
    
    function printRow($w, $verbose)
    {
        $to = trim($w->to_email);
        $from = $this->formatFrom($w);
        $subject = $this->formatSubject($w);
        
        if ($verbose) {
            echo str_pad($w->id, 8)
                . str_pad($this->truncate($to, 34), 36)
                . str_pad($this->truncate($from, 38), 40)
                . str_pad($this->truncate($subject, 54), 56)
                . str_pad($w->act_when, 20)
                . ' ' . str_pad($this->truncate($w->evtype, 14), 16)
                . str_pad($w->server_id, 4)
                . $w->ontable . ':' . $w->onid . "\n";
            return;
        }
        echo str_pad($w->id, 8)
            . str_pad($this->truncate($to, 38), 40)
            . str_pad($this->truncate($from, 42), 44)
            . $this->truncate($subject, 80) . "\n";
    }
    
    function formatFrom($w)
    {
        if (empty($w->join_from_email)) {
            return '-';
        }
        if (empty($w->join_from_name)) {
            return trim($w->join_from_email);
        }
        return trim('"' . addslashes($w->join_from_name) . '" <' . $w->join_from_email . '>');
    }
    
    function formatSubject($w)
    {
        if (empty($w->join_subject)) {
            return '-';
        }
        return str_replace(array("\r", "\n"), ' ', $w->join_subject);
    }
    
    function truncate($str, $len)
    {
        if (strlen($str) <= $len) {
            return $str;
        }
        return substr($str, 0, $len - 1) . "\xe2\x80\xa6";
    }
}

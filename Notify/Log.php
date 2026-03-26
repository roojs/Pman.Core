<?php

require_once 'Pman.php';

/**
 * CLI: list delivered core_notify rows (msgid set) in a time window on sent: id, to, sent, evtype, srv, ontable:onid, from, subject.
 * Uses join_person for to fallback; core_email for from/subject when email_id is set.
 *
 * php index.php Core/Notify/Log [--from "datetime"] [--to "datetime"] [-L N] [--debug]
 *
 * Default window: sent between NOW()-24h and NOW().
 * --from only: sent between {from} and {from}+24h.
 * --to only: sent between {to}-24h and {to}.
 * --from and --to: sent between {from} and {to} (from must be <= to).
 */
class Pman_Core_Notify_Log extends Pman
{
    static $cli_desc = 'List delivered core_notify rows (all servers): id, to, sent, evtype, server, ontable:onid, from, subject; filter by sent time.';
    
    static $cli_opts = array(
        'debug' => array(
            'desc' => 'Turn on DataObjects debug logging',
            'default' => 0,
            'min' => 0,
            'max' => 1,
        ),
        'from' => array(
            'desc' => 'Window start (strtotime). With --to: lower bound of sent. Alone: [from, from+24h].',
            'default' => '',
            'min' => 0,
            'max' => 1,
        ),
        'to' => array(
            'desc' => 'Window end (strtotime). With --from: upper bound of sent. Alone: [to-24h, to].',
            'default' => '',
            'min' => 0,
            'max' => 1,
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
            $this->jerr('access denied');
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
        
        $fromOpt = isset($opts['from']) ? trim($opts['from']) : '';
        $toOpt = isset($opts['to']) ? trim($opts['to']) : '';
        
        $fromTs = strlen($fromOpt) ? strtotime($fromOpt) : false;
        $toTs = strlen($toOpt) ? strtotime($toOpt) : false;
        
        if (strlen($fromOpt) && $fromTs === false) {
            $this->jerr('Invalid --from datetime.');
        }
        if (strlen($toOpt) && $toTs === false) {
            $this->jerr('Invalid --to datetime.');
        }
        
        if ($fromTs !== false && $toTs !== false) {
            if ($fromTs > $toTs) {
                $this->jerr('--from must be before or equal to --to.');
            }
            $start = $fromTs;
            $end = $toTs;
        } elseif ($fromTs !== false) {
            $start = $fromTs;
            $end = $fromTs + 86400;
        } elseif ($toTs !== false) {
            $start = $toTs - 86400;
            $end = $toTs;
        } else {
            $start = strtotime('-24 hours');
            $end = time();
        }
        
        $startStr = date('Y-m-d H:i:s', $start);
        $endStr = date('Y-m-d H:i:s', $end);
        
        $w = DB_DataObject::factory('core_notify');
        $w->autoJoin(array('exclude' => array('email_id')));
        $w->joinAdd(array('email_id', 'core_email:id'), 'LEFT');
        
        $w->selectAdd();
        $w->selectAdd("
            core_notify.id,
            COALESCE(NULLIF(TRIM(core_notify.to_email), ''), NULLIF(TRIM(join_person_id_id.email), ''), '') AS join_to_display,
            core_email.from_email AS join_from_email,
            core_email.from_name AS join_from_name,
            core_email.subject AS join_subject,
            core_notify.sent,
            core_notify.evtype,
            core_notify.server_id,
            core_notify.ontable,
            core_notify.onid
        ");
        
        $w->whereAdd("core_notify.msgid IS NOT NULL AND core_notify.msgid != ''");
        $w->whereAdd("core_notify.sent > '1970-01-01'");
        $w->whereAdd("core_notify.sent >= '" . $w->escape($startStr) . "'");
        $w->whereAdd("core_notify.sent <= '" . $w->escape($endStr) . "'");
        
        $w->orderBy('core_notify.sent DESC');
        $w->limit($limit);
        
        $count = $w->find();
        if (empty($count)) {
            $this->jok('No sent notifications in range (0 rows).');
        }
        
        echo str_pad('id', 10) . str_pad('to', 50) . str_pad('sent', 25) . str_pad('evtype', 50) . str_pad('srv', 4) . str_pad('ontable:onid', 50) . str_pad('from', 44) . str_pad('subject', 50) . "\n";
        echo str_repeat('-', 283) . "\n";
        
        while ($w->fetch()) {
            $this->printRow($w);
        }
        
        $this->jok('Done');
    }
    
    function printRow($w)
    {
        $to = trim((string) ($w->join_to_display ?? ''));
        $from = $this->formatFrom($w);
        $subject = $this->formatSubject($w);
        echo str_pad($w->id, 10)
            . str_pad($this->truncate($to, 50), 50)
            . str_pad((string) ($w->sent ?? ''), 25)
            . str_pad($this->truncate($w->evtype, 50), 50)
            . str_pad((string) $w->server_id, 4)
            . str_pad($this->truncate($w->ontable . ':' . $w->onid, 48), 50)
            . str_pad($this->truncate($from, 42), 44)
            . str_pad($this->truncate($subject, 48), 50)
            . "\n";
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
        return substr($str, 0, $len - 3) . "...";
    }
}

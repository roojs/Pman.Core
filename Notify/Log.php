<?php

require_once 'Pman/Core/Cli.php';

/**
 * CLI: list delivered core_notify rows (msgid set) in a time window on sent: id, to, sent, evtype, srv, ontable:onid, from, subject.
 * Uses join_person for to fallback; core_email for from/subject when email_id is set.
 * Without email_id: MAIL + crm_mailing_list_queue → crm_mailing_list_message; MAIL + mail_imap_message_user → mail_imap_user + mail_imap_message;
 * ontable crm_mailing_list_message (e.g. SendPreviewEmail): from/subject from that row by onid.
 * ontable core_email (e.g. Core_email::testData): from/subject from that row by onid when email_id is unset.
 *
 * php index.php Core/Notify/Log [--from "datetime"] [--to "datetime"] [-L N] [--debug]
 * php index.php Core/Notify/Log/{id}  — print raw SMTP debug (Events log EXTRA) for NOTIFYSENT on that core_notify id.
 *
 * Default window: sent between NOW()-24h and NOW().
 * --from only: sent between {from} and {from}+24h.
 * --to only: sent between {to}-24h and {to}.
 * --from and --to: sent between {from} and {to} (from must be <= to).
 */
class Pman_Core_Notify_Log extends Pman_Core_Cli
{
    static $cli_desc = 'List sent core_notify rows (all servers) or Core/Notify/Log/{id} for NOTIFYSENT SMTP debug from event log.';
    
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
        'view' => array(
            'desc' => 'View event log for a specific notify id',
            'default' => 0,
            'short' => 'N',
            'min' => 0,
            'max' => 1,
        )
    );

    function get($r, $opts = array())
    {
        if (!empty($opts['debug'])) {
            DB_DataObject::debugLevel($opts['debug']);
        }
        
        if (strlen((string) $r) && ctype_digit((string) $r)) {
            $this->outputSmtpLog((int) $r);
            return;
        }

        if(!empty($opts['view'])) {
            $this->outputSmtpLog((int) $opts['view']);
            return;
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
        // $w->_join .= "
        //     LEFT JOIN core_email join_log_ce_ontable
        //         ON join_log_ce_ontable.id = core_notify.onid
        //         AND core_notify.ontable = 'core_email'
        //     LEFT JOIN crm_mailing_list_queue join_log_mlq
        //         ON join_log_mlq.id = core_notify.onid
        //         AND core_notify.ontable = 'crm_mailing_list_queue'
        //         AND core_notify.evtype = 'MAIL'
        //     LEFT JOIN crm_mailing_list_message join_log_mlmsg
        //         ON join_log_mlmsg.id = join_log_mlq.message_id
        //     LEFT JOIN crm_mailing_list_message join_log_mlmsg_direct
        //         ON join_log_mlmsg_direct.id = core_notify.onid
        //         AND core_notify.ontable = 'crm_mailing_list_message'
        //     LEFT JOIN mail_imap_message_user join_log_mimu
        //         ON join_log_mimu.id = core_notify.onid
        //         AND core_notify.ontable = 'mail_imap_message_user'
        //         AND core_notify.evtype = 'MAIL'
        //     LEFT JOIN mail_imap_user join_log_miu
        //         ON join_log_miu.id = join_log_mimu.imap_user_id
        //     LEFT JOIN mail_imap_message join_log_mim
        //         ON join_log_mim.id = join_log_mimu.msg_id
        // ";
        
        $w->selectAdd();
        $w->selectAdd("
            core_notify.id,
            COALESCE(NULLIF(TRIM(core_notify.to_email), ''), NULLIF(TRIM(join_person_id_id.email), ''), '') AS join_to_display,
            core_email.from_email AS join_from_email,
            core_email.from_name AS join_from_name,
            core_email.subject AS join_subject,
            core_notify.sent,
            core_notify.evtype,
            core_notify.server_id
        ");
        // $w->selectAdd("
        //     core_notify.id,
        //     COALESCE(NULLIF(TRIM(core_notify.to_email), ''), NULLIF(TRIM(join_person_id_id.email), ''), '') AS join_to_display,
        //     CASE
        //         WHEN NULLIF(TRIM(core_email.from_email), '') IS NOT NULL THEN TRIM(core_email.from_email)
        //         WHEN NULLIF(TRIM(join_log_ce_ontable.from_email), '') IS NOT NULL THEN TRIM(join_log_ce_ontable.from_email)
        //         WHEN NULLIF(TRIM(join_log_mlmsg.from_email), '') IS NOT NULL THEN TRIM(join_log_mlmsg.from_email)
        //         WHEN NULLIF(TRIM(join_log_mlmsg_direct.from_email), '') IS NOT NULL THEN TRIM(join_log_mlmsg_direct.from_email)
        //         WHEN NULLIF(TRIM(join_log_miu.email), '') IS NOT NULL THEN TRIM(join_log_miu.email)
        //         ELSE ''
        //     END AS join_from_email,
        //     CASE
        //         WHEN NULLIF(TRIM(core_email.from_email), '') IS NOT NULL THEN TRIM(core_email.from_name)
        //         WHEN NULLIF(TRIM(join_log_ce_ontable.from_email), '') IS NOT NULL THEN TRIM(join_log_ce_ontable.from_name)
        //         WHEN NULLIF(TRIM(join_log_mlmsg.from_email), '') IS NOT NULL THEN TRIM(join_log_mlmsg.from_name)
        //         WHEN NULLIF(TRIM(join_log_mlmsg_direct.from_email), '') IS NOT NULL THEN TRIM(join_log_mlmsg_direct.from_name)
        //         WHEN NULLIF(TRIM(join_log_miu.email), '') IS NOT NULL THEN TRIM(join_log_miu.name)
        //         ELSE ''
        //     END AS join_from_name,
        //     CASE
        //         WHEN NULLIF(TRIM(core_email.subject), '') IS NOT NULL THEN core_email.subject
        //         WHEN NULLIF(TRIM(join_log_ce_ontable.subject), '') IS NOT NULL THEN join_log_ce_ontable.subject
        //         WHEN NULLIF(TRIM(join_log_mlmsg.subject), '') IS NOT NULL THEN join_log_mlmsg.subject
        //         WHEN NULLIF(TRIM(join_log_mlmsg_direct.subject), '') IS NOT NULL THEN join_log_mlmsg_direct.subject
        //         WHEN NULLIF(TRIM(join_log_mim.subject), '') IS NOT NULL THEN join_log_mim.subject
        //         ELSE ''
        //     END AS join_subject,
        //     core_notify.sent,
        //     core_notify.evtype,
        //     core_notify.server_id,
        //     core_notify.ontable,
        //     core_notify.onid
        // ");
        
        $w->whereAdd("
                core_notify.msgid IS NOT NULL
            AND
                core_notify.msgid != ''
            AND
                core_notify.sent > '1970-01-01'
            AND
                core_notify.sent >= '" . $w->escape($startStr) . "'
            AND
                core_notify.sent <= '" . $w->escape($endStr) . "'
        ");
        
        $w->orderBy('core_notify.sent DESC');
        $w->limit($limit);
        
        $count = $w->find();
        if (empty($count)) {
            $this->jok('No sent notifications in range (0 rows).');
        }
        
        echo str_pad('id', 10)
            . str_pad('to', 50)
            . str_pad('sent', 25)
            . str_pad('evtype', 50)
            . str_pad('srv', 4)
            . str_pad('ontable:onid', 50)
            . str_pad('from', 44)
            . str_pad('subject', 50)
            . "\n";
        echo str_repeat('-', 283) . "\n";
        
        while ($w->fetch()) {
            $this->printRow($w);
        }
        
        $this->jok('Done');
    }
    
    /**
     * Print SMTP debug_str from NOTIFYSENT event log (EXTRA) for a core_notify id.
     */
    private function outputSmtpLog($notifyId)
    {
        if (!DB_DataObject::factory('core_notify')->get($notifyId)) {
            $this->jerr('Unknown notify id.');
        }
        
        $ev = DB_DataObject::factory('Events');
        $ev->on_table = 'core_notify';
        $ev->on_id = $notifyId;
        $ev->action = 'NOTIFYSENT';
        $ev->orderBy('event_when DESC');
        $ev->limit(1);
        if (!$ev->find(true)) {
            $this->jerr('No NOTIFYSENT event for this notify id.');
        }
        
        $file = $ev->retrieveEventLog();
        if (!$file) {
            $this->jerr('Event log file not found.');
        }
        
        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data)) {
            $this->jerr('Event log file is not valid JSON.');
        }
        
        if (empty($data['EXTRA'])) {
            $this->jok('No SMTP debug data in event log (EXTRA empty or missing).');
        }
        
        echo $data['EXTRA'];
        echo "\n";
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

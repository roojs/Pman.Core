<?php

/**
 * Nagios monitoring for notify (mail) queue size
 *
 * Reports queue metrics in Nagios-friendly format with performance data
 * after a pipe for php4nagios/PNP4Nagios graphing.
 *
 * @author System
 */

require_once 'Pman/Core/Cli.php';

class Pman_Core_Process_MailQueueSize extends Pman_Core_Cli
{
    static $cli_desc = "Mail Queue Size -- Nagios monitoring for notify (mail) queue";
    static $cli_opts = array(
        'unread' => array(
            'desc' => 'due_untried thresholds as warning:critical (e.g. 10:100)',
            'default' => '1000000:1000000',
            'short' => 'w',
            'min' => 1,
            'max' => 1,
        ),
        'tried' => array(
            'desc' => 'tried_failed_pending thresholds as warning:critical (e.g. 10:100)',
            'default' => '1000000:1000000',
            'short' => 't',
            'min' => 1,
            'max' => 1,
        ),
        'minutes' => array(
            'desc' => 'Window in minutes for success_30m / failed_30m (default 30)',
            'default' => 30,
            'short' => 'm',
            'min' => 1,
            'max' => 1,
        ),
        'delivered' => array(
            'desc' => 'total_delivered thresholds as warning:critical',
            'default' => '1000000:1000000',
            'min' => 1,
            'max' => 1,
        ),
        'failed' => array(
            'desc' => 'failed_30m thresholds as warning:critical',
            'default' => '1000000:1000000',
            'min' => 1,
            'max' => 1,
        ),
        'notify-archive' => array(
            'desc' => 'notify_archive table total thresholds as warning:critical',
            'default' => '1000000:1000000',
            'min' => 1,
            'max' => 1,
        ),
        'event-archive' => array(
            'desc' => 'core_events_archive total thresholds as warning:critical',
            'default' => '1000000:1000000',
            'min' => 1,
            'max' => 1,
        ),
        'debug' => array(
            'desc' => 'Enable debug mode (DB_DataObject::debugLevel(1))',
            'default' => false,
            'short' => 'd',
            'max' => 1,
        ),
    );

    var $opts = array();
    var $notifyTable = 'core_notify';
    var $notify_needs_archiving;
    var $total_notify_archived;
    var $total_events;
    var $total_event_archive;
    var $success_30m;
    var $failed_30m;
    var $requeued_again;
    var $future_queue;

    /**
     * Fast approximate row counts for all tables (one information_schema query, keyed by table name).
     *
     * @return array table_name => TABLE_ROWS (int)
     */
    function tableRowsApproxMap()
    {
        $do = DB_DataObject::factory('Events');
        $do->query( "
            SELECT
                 TABLE_NAME AS table_name, TABLE_ROWS AS table_rows
            FROM
                 information_schema.TABLES
            WHERE
                 TABLE_SCHEMA = DATABASE()"
        );
        $map = array();
        while ($do->fetch()) {
            $map[$do->table_name] = (int) $do->table_rows;
        }
        return $map;
    }

    /**
     * Parse "warning:critical" threshold string, return array(warn, crit).
     */
    function parseThreshold($str)
    {
        $def = 1000000;
        if (empty($str) || strpos($str, ':') === false) {
            return array($def, $def);
        }
        list($w, $c) = explode(':', $str, 2);
        return array((int) $w, (int) $c);
    }

    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        if (empty($ff->cli)) {
            die("CLI ONLY");
        }
        return true;
    }

    function get($m = "", $opts = array())
    {
        $this->opts = $opts;
        if (!empty($opts['debug'])) {
            DB_DataObject::debugLevel(1);
        }
        $minutes = (int) $opts['minutes'];

        $cn = DB_DataObject::factory($this->notifyTable);

        // notify_needs_archiving: delivered rows older than 2 months (matches prune archive logic)
        $notify_needs_archiving = clone $cn;
        $notify_needs_archiving->whereAdd(
            "sent > '1970-01-01'
            AND msgid IS NOT NULL
            AND LENGTH(msgid) > 0
            AND event_id > 0
            AND act_when < NOW() - INTERVAL 2 MONTH"
        );
        $this->notify_needs_archiving = $notify_needs_archiving->count();

        $tableRows = $this->tableRowsApproxMap();
        $notifyArchiveTable = $this->notifyTable . '_archive';
        $this->total_notify_archived = isset($tableRows[$notifyArchiveTable]) ? $tableRows[$notifyArchiveTable] : 0;
        $eventsDo = DB_DataObject::factory('Events');
        $eventsTableName = $eventsDo->tableName();
        $this->total_events = isset($tableRows[$eventsTableName]) ? $tableRows[$eventsTableName] : 0;
        $this->total_event_archive = isset($tableRows['core_events_archive']) ? $tableRows['core_events_archive'] : 0;

        // requeued_again: act_when < NOW(), unsent, act_start != act_when
        $requeued = clone $cn;
        $requeued->whereAdd(
            "act_when < NOW()
            AND (sent < '1970-01-01' OR sent IS NULL)
            AND act_start != act_when"
        );
        $this->requeued_again = $requeued->count();

        // success_30m: sent in last N minutes, msgid set
        $success_30m = clone $cn;
        $success_30m->whereAdd(
            "sent > NOW() - INTERVAL {$minutes} MINUTE
            AND msgid IS NOT NULL
            AND LENGTH(msgid) > 0"
        );
        $this->success_30m = $success_30m->count();

        // failed_30m: query Events for ids, then count notify where event_id IN (ids)
        $events = DB_DataObject::factory('Events');
        $events->whereAdd(
            "action IN ('NOTIFYFAIL', 'NOTIFYBOUNCE')
            AND event_when > NOW() - INTERVAL {$minutes} MINUTE"
        );
        $event_ids = array_keys($events->fetchAll('id'));
        $failed_cn = clone $cn;
        if ($event_ids) {
            $failed_cn->whereAddIn('event_id', $event_ids, 'int');
            $this->failed_30m = $failed_cn->count();
        } else {
            $this->failed_30m = 0;
        }

        // future_queue: act_when < NOW(), unsent, act_start = act_when (last in report)
        $future_queue = clone $cn;
        $future_queue->whereAdd(
            "act_when < NOW()
            AND (sent < '1970-01-01' OR sent IS NULL)
            AND act_start = act_when"
        );
        $this->future_queue = $future_queue->count();

        $this->outputNagiosResults();
    }

    /**
     * Output results in Nagios format with performance data
     */
    function outputNagiosResults()
    {
        $statusStr = array('OK', 'WARNING', 'CRITICAL');
        $overall = 0;

        list($unread_w, $unread_c) = $this->parseThreshold($this->opts['unread']);
        list($tried_w, $tried_c) = $this->parseThreshold($this->opts['tried']);
        list($delivered_w, $delivered_c) = $this->parseThreshold($this->opts['delivered']);
        list($failed_w, $failed_c) = $this->parseThreshold($this->opts['failed']);
        list($notify_arch_w, $notify_arch_c) = $this->parseThreshold($this->opts['notify-archive']);
        list($core_arch_w, $core_arch_c) = $this->parseThreshold($this->opts['event-archive']);

        $reasons = array();
        if ($this->notify_needs_archiving >= $delivered_c) {
            $overall = 2;
            $reasons[] = 'notify_needs_archiving critical';
        } elseif ($this->notify_needs_archiving >= $delivered_w) {
            $overall = max($overall, 1);
            $reasons[] = 'notify_needs_archiving warning';
        }
        if ($this->total_notify_archived >= $notify_arch_c) {
            $overall = 2;
            $reasons[] = 'total_notify_archived critical';
        } elseif ($this->total_notify_archived >= $notify_arch_w) {
            $overall = max($overall, 1);
            $reasons[] = 'total_notify_archived warning';
        }
        if ($this->total_event_archive >= $core_arch_c) {
            $overall = 2;
            $reasons[] = 'total_event_archive critical';
        } elseif ($this->total_event_archive >= $core_arch_w) {
            $overall = max($overall, 1);
            $reasons[] = 'total_event_archive warning';
        }
        if ($this->failed_30m >= $failed_c) {
            $overall = 2;
            $reasons[] = 'failed_30m critical';
        } elseif ($this->failed_30m >= $failed_w) {
            $overall = max($overall, 1);
            $reasons[] = 'failed_30m warning';
        }
        if ($this->requeued_again >= $tried_c) {
            $overall = 2;
            $reasons[] = 'requeued_again critical';
        } elseif ($this->requeued_again >= $tried_w) {
            $overall = max($overall, 1);
            $reasons[] = 'requeued_again warning';
        }
        if ($this->future_queue >= $unread_c) {
            $overall = 2;
            $reasons[] = 'future_queue critical';
        } elseif ($this->future_queue >= $unread_w) {
            $overall = max($overall, 1);
            $reasons[] = 'future_queue warning';
        }

        $msg = $reasons ? implode('; ', $reasons) . ' - ' : '';
        $msg .= sprintf(
            "notify_needs_archiving=%d total_notify_archived=%d total_events=%d total_event_archive=%d success_30m=%d failed_30m=%d requeued_again=%d future_queue=%d",
            $this->notify_needs_archiving,
            $this->total_notify_archived,
            $this->total_events,
            $this->total_event_archive,
            $this->success_30m,
            $this->failed_30m,
            $this->requeued_again,
            $this->future_queue
        );

        printf(
            "%s - %s | notify_needs_archiving=%d;%d;%d;; total_notify_archived=%d;%d;%d;; total_events=%d;;;; total_event_archive=%d;%d;%d;; success_30m=%d;;;; failed_30m=%d;%d;%d;; requeued_again=%d;%d;%d;; future_queue=%d;%d;%d;;\n",
            $statusStr[$overall],
            $msg,
            $this->notify_needs_archiving,
            $delivered_w,
            $delivered_c,
            $this->total_notify_archived,
            $notify_arch_w,
            $notify_arch_c,
            $this->total_events,
            $this->total_event_archive,
            $core_arch_w,
            $core_arch_c,
            $this->success_30m,
            $this->failed_30m,
            $failed_w,
            $failed_c,
            $this->requeued_again,
            $tried_w,
            $tried_c,
            $this->future_queue,
            $unread_w,
            $unread_c
        );
        exit($overall);
    }
}

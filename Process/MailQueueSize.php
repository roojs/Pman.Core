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
        'warning' => array(
            'desc' => 'Warning threshold (due_untried count)',
            'default' => 1000000,
            'short' => 'w',
            'min' => 1,
            'max' => 1,
        ),
        'critical' => array(
            'desc' => 'Critical threshold (due_untried count)',
            'default' => 1000000,
            'short' => 'c',
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
        'warning-tried' => array(
            'desc' => 'Warning threshold for tried_failed_pending',
            'default' => 1000000,
            'min' => 1,
            'max' => 1,
        ),
        'critical-tried' => array(
            'desc' => 'Critical threshold for tried_failed_pending',
            'default' => 1000000,
            'min' => 1,
            'max' => 1,
        ),
        'warning-delivered' => array(
            'desc' => 'Warning threshold for total_delivered (table bloat)',
            'default' => 1000000,
            'min' => 1,
            'max' => 1,
        ),
        'critical-delivered' => array(
            'desc' => 'Critical threshold for total_delivered (table bloat)',
            'default' => 1000000,
            'min' => 1,
            'max' => 1,
        ),
        'warning-failed' => array(
            'desc' => 'Warning threshold for failed_30m',
            'default' => 1000000,
            'min' => 1,
            'max' => 1,
        ),
        'critical-failed' => array(
            'desc' => 'Critical threshold for failed_30m',
            'default' => 1000000,
            'min' => 1,
            'max' => 1,
        ),
    );

    var $opts = array();
    var $notifyTable = 'core_notify';
    var $due_untried;
    var $tried_failed_pending;
    var $success_30m;
    var $failed_30m;
    var $total_delivered;

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
        $minutes = (int) $opts['minutes'];

        $cn = DB_DataObject::factory($this->notifyTable);

        // due_untried: act_when < NOW(), unsent, act_start = act_when
        $due_untried = clone $cn;
        $due_untried->whereAdd(
            "act_when < NOW()
            AND (sent < '1970-01-01' OR sent IS NULL)
            AND act_start = act_when"
        );
        $this->due_untried = $due_untried->count();

        // tried_failed_pending: act_when < NOW(), unsent, act_start != act_when
        $tried_failed = clone $cn;
        $tried_failed->whereAdd(
            "act_when < NOW()
            AND (sent < '1970-01-01' OR sent IS NULL)
            AND act_start != act_when"
        );
        $this->tried_failed_pending = $tried_failed->count();

        // success_30m: sent in last N minutes, msgid set
        $success_30m = clone $cn;
        $success_30m->whereAdd(
            "sent > NOW() - INTERVAL {$minutes} MINUTE
            AND msgid IS NOT NULL
            AND LENGTH(msgid) > 0"
        );
        $this->success_30m = $success_30m->count();

        // failed_30m: notify rows whose event_id points to Event with NOTIFYFAIL/NOTIFYBOUNCE in last N minutes
        $failed_cn = clone $cn;
        $failed_cn->autoJoin();
        $failed_cn->whereAdd(
            "join_event_id_id.action IN ('NOTIFYFAIL', 'NOTIFYBOUNCE')
            AND join_event_id_id.event_when > NOW() - INTERVAL {$minutes} MINUTE"
        );
        $this->failed_30m = $failed_cn->count();

        // total_delivered: delivered rows still in table (sent set, msgid set, event_id > 0)
        $total_delivered = clone $cn;
        $total_delivered->whereAdd(
            "sent > '1970-01-01'
            AND msgid IS NOT NULL
            AND LENGTH(msgid) > 0
            AND event_id > 0"
        );
        $this->total_delivered = $total_delivered->count();

        $this->outputNagiosResults();
    }

    /**
     * Output results in Nagios format with performance data
     */
    function outputNagiosResults()
    {
        $statusStr = array('OK', 'WARNING', 'CRITICAL');
        $overall = 0;

        if ($this->due_untried >= $this->opts['critical'] || $this->tried_failed_pending >= $this->opts['critical-tried'] || $this->total_delivered >= $this->opts['critical-delivered'] || $this->failed_30m >= $this->opts['critical-failed']) {
            $overall = 2;
        } elseif ($this->due_untried >= $this->opts['warning'] || $this->tried_failed_pending >= $this->opts['warning-tried'] || $this->total_delivered >= $this->opts['warning-delivered'] || $this->failed_30m >= $this->opts['warning-failed']) {
            $overall = max($overall, 1);
        }

        printf(
            "%s - due_untried=%d tried_failed=%d success_30m=%d failed_30m=%d total_delivered=%d | due_untried=%d;%d;%d;; tried_failed=%d;%d;%d;; success_30m=%d;;;; failed_30m=%d;%d;%d;; total_delivered=%d;%d;%d;;\n",
            $statusStr[$overall],
            $this->due_untried,
            $this->tried_failed_pending,
            $this->success_30m,
            $this->failed_30m,
            $this->total_delivered,
            $this->due_untried,
            $this->opts['warning'],
            $this->opts['critical'],
            $this->tried_failed_pending,
            $this->opts['warning-tried'],
            $this->opts['critical-tried'],
            $this->success_30m,
            $this->failed_30m,
            $this->opts['warning-failed'],
            $this->opts['critical-failed'],
            $this->total_delivered,
            $this->opts['warning-delivered'],
            $this->opts['critical-delivered']
        );
        exit($overall);
    }
}

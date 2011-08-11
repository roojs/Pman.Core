<?php
require_once 'Pman.php';

/**
 * notification script sender - designed to be run by the Notify script - with many children running
 * in parallel.
 *
 * called with an id of a core_notify element
 *
 * uses core_notify - to find an event to object and person.
 *
 * uses Events table to log failures
 * 
 * 
 * calls $object->toEmail($person,$last_send, $notify) to generate an email struct with
 *  array (
 *      headers =>
 *      recipients =>
 *      body =>
 *  )
 * 
 * 
 */


class Pman_Core_NotifySend extends Pman
{
    static $cli_desc = "Send out single notification email (usually called from  Core/Notify)";
    
    static $cli_opts = array(
        'debug' => array(
            'desc' => 'Turn on debugging (see DataObjects debugLevel )',
            'default' => 0,
            'short' => 'v',
            'min' => 1,
            'max' => 1,
            
        ),
        'force' => array(
            'desc' => 'Force redelivery, even if it has been sent before or not queued...',
            'default' => 0,
            'short' => 'f',
            'min' => 0,
            'max' => 0,
        ),
        'send-to' => array(
            'desc' => 'Send the message to this address, rather than the one listed.',
            'short' => 't',
            'min' => 0,
            'max' => 1,
        )
        
        
        
    );
    var $table = 'core_notify';
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        if (!$ff->cli) {
            die("access denied");
        }
        //HTML_FlexyFramework::ensureSingle(__FILE__, $this);
        return true;
        
    }
   
    function get($id,$opts)
    {
        if ($opts['debug']) {
            DB_DataObject::debugLevel($opts['debug']);
        }
        //DB_DataObject::debugLevel(1);
        //date_default_timezone_set('UTC');
        // phpinfo();exit;
        $force = $opts['force'];
        
        $w = DB_DataObject::factory($this->table);
        
        if (!$w->get($id)) {
            die("invalid id\n");
        }
        if (!$force && strtotime($w->act_when) < strtotime($w->sent)) {
            
            
            die("send repeat to early\n");
        }
        
        if (!$force && !empty($w->msgid)) {
            die("message has been sent already.\n");
        }
        
        
        $o = $w->object();
        $p = $w->person();
        
        // let's work out the last notification sent to this user..
        $l = DB_DataObject::factory($this->table);
        $l->setFrom( array(
                'ontable' => $w->ontable,
                'onid' => $w->onid,
                'person_id' => $w->person_id,
        ));        
        $l->whereAdd('id != '. $w->id);
        $l->orderBy('sent DESC');
        $l->limit(1);
        $ar = $l->fetchAll('sent');
        $last = empty($ar) ? date('Y-m-d H:i:s', 0) : $ar[0];
        
        // find last event..
        $ev = DB_DataObject::factory('Events');
        $ev->on_id = $w->id;                           // int(11)
        $ev->od_table = $this->table;
        $ev->limit(1);
        $ev->orderBy('event_when DESC');
        $ar = $ev->fetchAll('event_when');
        $last_event = empty($ar) ? 0 : $ar[0];
        $next_try_min = 5;
        if ($last_event) {
            $next_try_min = floor((time() - strtotime($last_event)) / 60) * 2;
        }
        $next_try = $next_try_min . ' MINUTES';
        
        $email =  $this->makeEmail($o, $p,$last);
        
        
        
        //$p->email = 'alan@akbkhome.com'; //for testing..
        //print_r($email);exit;
        // should we fetch the watch that caused it.. - which should contain the method to call..
        
        if (!empty($opts['send-to'])) {
            $p->email = $opts['send-to'];
        }
        
        
        $dom = array_pop(explode('@', $p->email));
        
        $mxs = $this->mxs($dom);
        $ww = clone($w);
        
        $fail = false;
        require_once 'Mail.php';
        
        foreach($mxs as $dom) {
            
            $mailer = Mail::factory('smtp', array(
                    'host'    => $dom ,
                  //  'debug' => true
                ));
            $res = $mailer->send($p->email, $email['headers'], $email['body']);
            if ($res === true) {
                // success....
                
                $w->sent = date('Y-m-d H:i:s');
                $w->msgid = $email['headers']['Message-Id'];
                $w->event_id = -1; // sent ok.. - no need to record it..
                $w->update($ww);
                die(date('Y-m-d h:i:s') . " - SENT");
            }
            // what type of error..
            list($code, $response) = $mailer->_smtp->getResponse();
            if ($code < 0) {
                continue; // try next mx... ??? should we wait??? - nope we did not even connect..
            }
            // give up after 2 days..
            if ($code == 421 && $next_try_min < (2*24*60)) {
                // try again later..
                // check last event for this item..
                $this->addEvent('NOTIFY', $w, 'GREYLISTED');
                $w->act_when = date('Y-m-d H:i:s', strtotime('NOW + 5 MINUTES'));
                $w->update($ww);
                die(date('Y-m-d h:i:s') . " - GREYLISTED");
            }
            $fail = true;
            break;
        }
        if ($fail || $next_try_min > (2*24*60)) {
        // fail.. = log and give up..
            $id = $this->addEvent('NOTIFY', $w, 'FAILED - '. ($fail ? $res->toString() : "RETRY TIME EXCEEDED"));
            $w->sent = date('Y-m-d H:i:s');
            $w->msgid = '';
            $w->event_id = $id;
            $w->update($ww);
            die(date('Y-m-d h:i:s') . ' - FAILED - '. ($fail ? $res->toString() : "RETRY TIME EXCEEDED"));
        }
        
        $this->addEvent('NOTIFY', $w, 'NO HOST CAN BE CONTACTED');
        $w->act_when = date('Y-m-d H:i:s', strtotime('NOW + 5 MINUTES'));
        $w->update($ww);
        die(date('Y-m-d h:i:s') ." - NO HOST AVAILABLE");

        
    }
    function mxs($fqdn)
    {
        $mx_records = array();
        $mx_weight = array();
        $mxs = array();
        if (!getmxrr($fqdn, $mx_records, $mx_weight)) {
            return array($fqdn);
        }
        
        asort($mx_weight,SORT_NUMERIC);
        
        foreach($mx_weight as $k => $weight) {
            $mxs[] = $mx_records[$k];
        }
        return $mxs;
    }
    
    /**
     * wrapper to call object->toEmail()
     * 
     **/
    function makeEmail($o, $p, $last)
    {
        return $o->toEmail($p,$last);
    }
}
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
            'min' => 0,
            'max' => 0,
            
        ),
        'DB_DataObject-debug' => array(
            'desc' => 'Turn on debugging (see DataObjects debugLevel )',
            'default' => 0,
            'short' => 'd',
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
            'default' => '',
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
        if ($opts['DB_DataObject-debug']) {
            DB_DataObject::debugLevel($opts['DB_DataObject-debug']);
        }
        //DB_DataObject::debugLevel(1);
        //date_default_timezone_set('UTC');
        // phpinfo();exit;
        $force = empty($opts['force']) ? 0 : 1;
        
        $w = DB_DataObject::factory($this->table);
        
        if (!$w->get($id)) {
            die("invalid id\n");
        }
        if (!$force && strtotime($w->act_when) < strtotime($w->sent)) {
            
            
            die("send repeat to early\n");
        }
        if (!empty($opts['debug'])) {
            print_r($w);
        }
        
        $sent = (empty($w->sent) || preg_match('/^0000/', $w->sent)) ? false : true;
        
        if (!$force && (!empty($w->msgid) || $sent)) {
            $ww = clone($w);
            if (!$sent) { 
                $w->sent = $w->sqlValue("NOW()");
                $w->update($ww);
            }    
            die("message has been sent already.\n");
        }
        
        $o = $w->object();
        
        if ($o === false)  {
            
            $ev = $this->addEvent('NOTIFY', $w,
                            "Notification event cleared (underlying object does not exist)" );;
            $ww = clone($w);
            $w->sent = date('Y-m-d H:i:s');
            $w->msgid = '';
            $w->event_id = $ev->id;
            $w->update($ww);
            die(date('Y-m-d h:i:s ') . 
                     "Notification event cleared (underlying object does not exist)" 
                    ."\n");
        }
     
        
        
        $p = $w->person();
        
        if (isset($p->active) && empty($p->active)) {
            $ev = $this->addEvent('NOTIFY', $w,
                            "Notification event cleared (not user not active any more)" );;
            $ww = clone($w);
            $w->sent = date('Y-m-d H:i:s');
            $w->msgid = '';
            $w->event_id = $ev->id;
            $w->update($ww);
            die(date('Y-m-d h:i:s ') . 
                     "Notification event cleared (not user not active any more)" 
                    ."\n");
            die("message has been sent already.\n");
        }
        
        
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
        $ev->on_table = $this->table;
        $ev->limit(1);
        $ev->orderBy('event_when DESC');
        $ar = $ev->fetchAll('event_when');
        $last_event = empty($ar) ? 0 : $ar[0];
        $next_try_min = 5;
        if ($last_event) {
            $next_try_min = floor((time() - strtotime($last_event)) / 60) * 2;
        }
        $next_try = $next_try_min . ' MINUTES';
         
        // this may modify $p->email. (it will not update it though)
        $email =  $this->makeEmail($o, $p, $last, $w, $force);
        
        if ($email === true)  {
            
            $ev = $this->addEvent('NOTIFY', $w,
                            "Notification event cleared (not required any more)" );;
            $ww = clone($w);
            $w->sent = date('Y-m-d H:i:s');
            $w->msgid = '';
            $w->event_id = $ev->id;
            $w->update($ww);
            die(date('Y-m-d h:i:s ') . 
                     "Notification event cleared (not required any more)" 
                    ."\n");
        }
     
       
        
        if ($email === false || isset($email['error'])) {
            // object returned 'false' - it does not know how to send it..
            $ev = $this->addEvent('NOTIFY', $w, isset($email['error'])  ?
                            $email['error'] : "INTERNAL ERROR  - We can not handle " . $w->ontable); 
            $ww = clone($w);
            $w->sent = date('Y-m-d H:i:s');
            $w->msgid = '';
            $w->event_id = $ev->id;
            $w->update($ww);
            die(date('Y-m-d h:i:s ') . 
                    (isset($email['error'])  ?
                            $email['error'] : "INTERNAL ERROR  - We can not handle " . $w->ontable)
                    ."\n");
        }
        
        
        if (isset($email['later'])) {
            $old = clone($w);
            $w->act_when = $email['later'];
            $w->update($old);
            die(date('Y-m-d h:i:s ') . " Delivery postponed by email creator");
        }
        
         
        if (empty($email['headers']['Message-Id'])) {
            $HOST = gethostname();
            $email['headers']['Message-Id'] = "<{$this->table}-{$id}@{$HOST}>";
            
        }
        //$p->email = 'alan@akbkhome.com'; //for testing..
        //print_r($email);exit;
        // should we fetch the watch that caused it.. - which should contain the method to call..
        // --send-to=test@xxx.com
       
        if (!empty($email['send-to'])) {
            $p->email = $email['send-to'];
        }
         if (!empty($opts['send-to'])) {
            $p->email = $opts['send-to'];
        }
        // since some of them have spaces?!?!
        $p->email = trim($p->email);
        
        
        require_once 'Validate.php';
        if (!Validate::email($p->email, true)) {
            $ev = $this->addEvent('NOTIFY', $w, "INVALID ADDRESS: " . $p->email);
            $ww = clone($w);
            $w->sent = date('Y-m-d H:i:s');
            $w->msgid = '';
            $w->event_id = $ev->id;
            $w->update($ww);
            die(date('Y-m-d h:i:s ') . "INVALID ADDRESS: " . $p->email. "\n");
            
        }
        
        
        $ff = HTML_FlexyFramework::get();
        
        
        $dom = array_pop(explode('@', $p->email));
        
        $mxs = $this->mxs($dom);
        $ww = clone($w);

        // we might fail doing this...
        // need to handle temporary failure..
       
        
          // we try for 3 days..
        $retry = 5;
        if (strtotime($w->act_start) <  strtotime('NOW - 1 HOUR')) {
            // older that 1 hour.
            $retry = 15;
        }
        
        if (strtotime($w->act_start) <  strtotime('NOW - 1 DAY')) {
            // older that 1 day.
            $retry = 60;
        }
        if (strtotime($w->act_start) <  strtotime('NOW - 2 DAY')) {
            // older that 1 day.
            $retry = 120;
        }
        
        if ($mxs === false) {
            // only retry for 2 day son the MX issue..
            if ($retry < 120) {
                $this->addEvent('NOTIFY', $w, 'MX LOOKUP FAILED ' . $dom . ' ' . $res->toString());
                $w->act_when = date('Y-m-d H:i:s', strtotime('NOW + ' . $retry . ' MINUTES'));
                $w->update($ww);
                die(date('Y-m-d h:i:s') . " - GREYLISTED\n");
            }
            
            $ev = $this->addEvent('NOTIFY', $w, "BAD ADDRESS - ". $p->email );
            $w->sent = date('Y-m-d H:i:s');
            $w->msgid = '';
            $w->event_id = $ev->id;
            $w->update($ww);
            die(date('Y-m-d h:i:s') . " - FAILED -  BAD EMAIL - {$p->email} \n");
            
            
        }
        
        
        
        
        if (!$force && strtotime($w->act_start) <  strtotime('NOW - 14 DAY')) {
            $ev = $this->addEvent('NOTIFY', $w, "BAD ADDRESS - ". $p->email );
            $w->sent = date('Y-m-d H:i:s');
            $w->msgid = '';
            $w->event_id = $ev->id;
            $w->update($ww);
            die(date('Y-m-d h:i:s') . " - FAILED -  GAVE UP TO OLD - {$p->email} \n");
        }
        
        
        
        $w->to_email = $p->email; 
        //$this->addEvent('NOTIFY', $w, 'GREYLISTED ' . $p->email . ' ' . $res->toString());
        $w->act_when = date('Y-m-d H:i:s', strtotime('NOW + ' . $retry . ' MINUTES'));
        $w->update($ww);
        
        $ww = clone($w);   
        
        $fail = false;
        require_once 'Mail.php';
        
        foreach($mxs as $dom) {
            
            
            
            if (!isset($ff->Mail['helo'])) {
                die("config Mail[helo] is not set");
            }
            
            $this->debug("Trying SMTP: $dom / HELO {$ff->Mail['helo']}");
            $mailer = Mail::factory('smtp', array(
                    'host'    => $dom ,
                    'localhost' => $ff->Mail['helo'],
                    'timeout' => 15,
                    'socket_options' =>  isset($ff->Mail['socket_options']) ? $ff->Mail['socket_options'] : null
                  //  'debug' => true
                ));
            $res = $mailer->send($p->email, $email['headers'], $email['body']);
             
            
            if ($res === true) {
                // success....
                
                $ev = $this->addEvent('NOTIFYSENT', $w, "{$w->to_email} - {$email['headers']['Subject']}");
               
                
                
                $w->sent = date('Y-m-d H:i:s');
                $w->msgid = $email['headers']['Message-Id'];
                $w->event_id = $ev->id; // sent ok.. - no need to record it..
                $w->update($ww);
                
                // enable cc in notify..
                if (!empty($email['headers']['Cc'])) {
                    $mailer = Mail::factory('smtp', array(
                       //'host'    => $dom ,
                      //  'debug' => true
                    ));
                    $email['headers']['Subject'] = "(CC): " . $email['headers']['Subject'];
                    $mailer->send($email['headers']['Cc'],
                                  $email['headers'], $email['body']);
                    
                }
                
                if (!empty($email['bcc'])) {
                    $mailer = Mail::factory('smtp', array(
                       //'host'    => $dom ,
                      //  'debug' => true
                    ));
                    $email['headers']['Subject'] = "(CC): " . $email['headers']['Subject'];
                    $mailer->send($email['bcc'],
                                  $email['headers'], $email['body']);
                    
                }
                
                
                die(date('Y-m-d h:i:s') . " - SENT\n");
            }
            // what type of error..
            $code = empty($res->userinfo['smtpcode']) ? -1 : $res->userinfo['smtpcode'];
            if (!empty($res->code) && $res->code == 10001) {
                // fake greylist if timed out.
                $code = 421;
            }
            
            if ($code < 0) {
                $this->debug($res->message);
                continue; // try next mx... ??? should we wait??? - nope we did not even connect..
            }
            // give up after 2 days..
            if (in_array($code, array( 421, 450, 451, 452))   && $next_try_min < (2*24*60)) {
                // try again later..
                // check last event for this item..
                $errmsg=  $fail ? ($res->userinfo['smtpcode'] . ': ' .$res->toString()) :  " - UNKNOWN ERROR";
                if (isset($res->userinfo['smtptext'])) {
                    $errmsg=  $res->userinfo['smtpcode'] . ':' . $res->userinfo['smtptext'];
                }
                //print_r($res);
                $this->addEvent('NOTIFY', $w, 'GREYLISTED - ' . $errmsg);
                $w->act_when = date('Y-m-d H:i:s', strtotime('NOW + ' . $retry . ' MINUTES'));
                $w->update($ww);
                die(date('Y-m-d h:i:s') . " - GREYLISTED\n");
            }
            $fail = true;
            break;
        }
        if ($fail || $next_try_min > (2*24*60)) {
        // fail.. = log and give up..
            $errmsg=  $fail ? ($res->userinfo['smtpcode'] . ': ' .$res->toString()) :  " - UNKNOWN ERROR";
            if (isset($res->userinfo['smtptext'])) {
                $errmsg=  $res->userinfo['smtpcode'] . ':' . $res->userinfo['smtptext'];
            }
            
            $ev = $this->addEvent('NOTIFY', $w, ($fail ? "FAILED - " : "RETRY TIME EXCEEDED - ") .
                       $errmsg);
            $w->sent = date('Y-m-d H:i:s');
            $w->msgid = '';
            $w->event_id = $ev->id;
            $w->update($ww);
            die(date('Y-m-d h:i:s') . ' - FAILED - '. ($fail ? $res->toString() : "RETRY TIME EXCEEDED\n"));
        }
        
        // handle no host availalbe forever...
        if (strtotime($w->act_start) < strtotime('NOW - 3 DAYS')) {
            $ev = $this->addEvent('NOTIFY', $w, "RETRY TIME EXCEEDED - ". $p->email);
            $w->sent = date('Y-m-d H:i:s');
            $w->msgid = '';
            $w->event_id = $ev->id;
            $w->update($ww);
            die(date('Y-m-d h:i:s') . " - FAILED - RETRY TIME EXCEEDED\n");
            
            
        }
        
        
        $this->addEvent('NOTIFY', $w, 'NO HOST CAN BE CONTACTED:' . $p->email);
        $w->act_when = date('Y-m-d H:i:s', strtotime('NOW + 5 MINUTES'));
        $w->update($ww);
        die(date('Y-m-d h:i:s') ." - NO HOST AVAILABLE\n");

        
    }
    function mxs($fqdn)
    {
        $mx_records = array();
        $mx_weight = array();
        $mxs = array();
        if (!getmxrr($fqdn, $mx_records, $mx_weight)) {
            if (!checkdnsrr($fqdn)) {
                return false;
            }
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
     * return
     *   {
        headers : {AssocArray},
        body: {String}
        
        // optional..
        error :  {String} // error message in log.
        send-to: {String} // use to override rcpt
         
     }
     **/
    function makeEmail($object, $rcpt, $last_sent_date, $notify, $force =false)
    {
        $m = 'notify'. $notify->evtype;
        //var_dump(get_class($object) . '::' .$m);
        
        if (!empty($notify->evtype) && method_exists($object,$m)) {
            echo "calling :" . get_class($object) . '::' .$m . "\n";
            return $object->$m($rcpt, $last_sent_date, $notify, $force);
        }
        
        if(!empty($notify->evtype) && $notify->evtype == 'campaign_detail::drawWinner'){
            echo "calling :" . $notify->evtype . "\n";
            return $object->drawWinner($rcpt, $last_sent_date, $notify, $force);
        }
                
        if (!method_exists($object, 'toEmail')) {
            //var_Dump($object);
            //exit;
        }
        
        return $object->toEmail($rcpt, $last_sent_date, $notify, $force);
    }
    
    function debug($str)
    {
        if (empty($this->cli_args['debug'])) {
            return;
            
        }
        echo $str . "\n";
    }
    function output()
    {
        die("done\n");
    }
}
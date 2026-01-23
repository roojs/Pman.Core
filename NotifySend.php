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
 * Note uses configuration
 *
 * Pman_Core_NotifySend[host] = 'localhost' << to override direct sending..
 * Mail[helo] << helo host name
 * Mail[socket_options] << any socket option.
 *
 *
 *'Core_Notify' => array(
            'routes' => array(
                'smtp.office365.com' => array(
                    'domains' => array(
                          'XXX' << list of domains..
                    ),
                    'mx' => array(
                        '/outlook\.com$/'   // regex of mx
                    ),
                    'username' => 'USERNAME', 
                    'password' => 'PASSWORD',
                    'port' => 465,
                    'rate' => 100  // how many per hour.
                ),
                
            )
        ),
 *
 *  
 * 
 */
class Pman_Core_NotifySend_Exception_Success extends Exception {}
class Pman_Core_NotifySend_Exception_Fail extends Exception {}


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
    var $error_handler = 'die';
    var $poolname = 'core';
    var $server; // core_notify_server
    var $server_ipv6;
    var $isAnyIpv4Blacklisted = false;
    var $debug;
    
    // Properties set by beforeSend()
    var $notify;        // The notification record (core_notify or pressrelease_notify)
    var $notifyObject;  // The object linked to the notification (ontable/onid)
    var $email;         // The email array (headers, body, etc.)
    var $emailDomain;   // Core_domain instance for the recipient's email domain
    var $mxRecords;     // Array of MX hostnames for the domain
    var $retryWhen;     // Datetime string for next retry attempt
    
    // Properties used during send()
    var $allMxIps = array();  // Array of ALL IP addresses from MX lookup (never reduced)
    var $validIps = array();  // Array of valid IP addresses remaining (reduced during send loop)
    var $failedIp = false;    // The IP address that failed
    var $useIpv6 = false;     // Whether using IPv6 for this send
    var $fail = false;        // Whether send failed
    var $lastSmtpResponse;    // Last SMTP response (PEAR_Error or true)
    var $force = false;       // Force sending even if already sent
    
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        
        if ($ff->cli) {
            return true;
        }
        if (empty($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->errorHandler("access denied");
        }
        //HTML_FlexyFramework::ensureSingle(__FILE__, $this);
        return true;
        
    }
    function post($id, $opts = array())
    {
        $opts = $_REQUEST; // kludgy...
        $this->get($id, $opts); // wrapper to allow it to be called from http.
        
    }
   
    function get($id,$opts=array())
    {   
        // DB_DataObject::debugLevel(5);
        if ($this->database_is_locked()) {
            $this->errorHandler("LATER - DATABASE IS LOCKED\n");
        }

        //print_r($opts);
        if (!empty($opts['DB_DataObject-debug'])) {
            DB_DataObject::debugLevel($opts['DB_DataObject-debug']);
        }
        
        //DB_DataObject::debugLevel(1);
        //date_default_timezone_set('UTC');
        // phpinfo();exit;
        $this->force = empty($opts['force']) ? 0 : 1;
        
        // Pre-processing: validate, load objects, prepare email
        $this->beforeSend($id, $opts);

        // Send the email - tries each MX host
        $this->send();

        // Post-send handling: IPv6 setup, failure handling, retries
        $this->postSend();
    }
    function mxs($fqdn)
    {
        $ff = HTML_FlexyFramework::get();
        if (isset($ff->Pman_Core_NotifySend['host'])) {
            return array($ff->Pman_Core_NotifySend['host']);
        }
        
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
            if (!empty($mx_records[$k])) {
                // Validate that the MX hostname is actually resolvable
                if (checkdnsrr($mx_records[$k], 'A') || checkdnsrr($mx_records[$k], 'AAAA')) {
                    $mxs[] = $mx_records[$k];
                }
            }
        }

        return empty($mxs) ? false : $mxs;
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
            $this->debug("calling :" . get_class($object) . '::' .$m );
            return $object->$m($rcpt, $last_sent_date, $notify, $force);
        }
        
        $type = explode('::', $notify->evtype);
        
        if(!empty($type[1]) && method_exists($object,$type[1])){
            $m = $type[1];
            $this->debug("calling :" . get_class($object) . '::' .$m );
            return $object->$m($rcpt, $last_sent_date, $notify, $force);
        }

        $type = explode(':', $notify->evtype);
        
        if(!empty($type[1]) && method_exists($object,$type[1])){
            $m = $type[1];
            $this->debug("calling :" . get_class($object) . '::' .$m );
            return $object->$m($rcpt, $last_sent_date, $notify, $force);
        }
        
        // fallback if evtype is empty..
        
        if (method_exists($object, 'toMailerData')) {
            return $object->toMailerData(array(
                'msgid' => $notify->tableName() . '-' . $notify->id,
                'rcpts'=>$rcpt,
                'person'=>$rcpt, // added as mediaoutreach used this?
            )); //this is core_email - i think it's only used for testing...
            //var_Dump($object);
            //exit;
        }
        if (method_exists($object, 'toEmail')) {
            return $object->toEmail($rcpt, $last_sent_date, $notify, $force);
        }
        // no way to send this.. - this needs to handle core_notify how we have used it for the approval stuff..
        
        return false;
    }
    
    /**
     * Pre-processing before sending: validates notify record, loads objects, prepares email
     * Sets class properties: $this->notify, $this->notifyObject, $this->email, 
     * $this->emailDomain, $this->mxRecords, $this->retryWhen
     * 
     * @param int $id The notification ID
     * @param array $opts Options array
     */
    function beforeSend($id, $opts)
    {
        $this->notify = DB_DataObject::factory($this->table); // core_notify usually.

        if (!$this->notify->get($id)) {
            $this->errorHandler("invalid id\n");
        }

        if (!$this->force && !empty($this->notify->sent) && strtotime($this->notify->act_when) < strtotime($this->notify->sent)) {
            $this->errorHandler("already sent - repeat to early\n");
        }
        
        $this->server = DB_DataObject::Factory('core_notify_server')->getCurrent($this, $this->force);
        // for testing
        $this->server = DB_DataObject::Factory('core_notify_server');
        $this->server->get($this->notify->server_id);
        

        // Check if server is disabled or not found - exit gracefully (unless force is set)
        // id = 0 means no servers exist, is_active = 0 means server is disabled
        if (!$this->force && (empty($this->server->id) || empty($this->server->is_active))) {
            $this->errorHandler("Server is disabled or not found - exiting gracefully\n");
        }
        
         if (!$this->force &&  $this->notify->server_id != $this->server->id) {
            $this->errorHandler("Server id does not match - message = {$this->notify->server_id} - our id is {$this->server->id} use force to try again\n");
        }
        
        if (!empty($opts['debug'])) {
            print_r($this->notify);
            $ff = HTML_FlexyFramework::get();
            if (!isset($ff->Core_Mailer)) {
                $ff->Core_Mailer = array();
            }
            HTML_FlexyFramework::get()->Core_Mailer['debug'] = true;
            $this->debug = true;
        }
        
        $sent = (empty($this->notify->sent) || strtotime( $this->notify->sent) < 100 ) ? false : true;
        
        if (!$this->force && (!empty($this->notify->msgid) || $sent)) {
            $ww = clone($this->notify);
            if (!$sent) {   // fix sent.
                $this->notify->sent = strtotime( $this->notify->sent) < 100 ? $this->notify->sqlValue('NOW()') :$this->notify->sent; // do not update if sent.....
                $this->notify->update($ww);
            }    
            $this->errorHandler("message has been sent already.\n");
        }
        
        $cev = DB_DataObject::Factory('Events');
        $cev->on_table =  $this->table;
        $cev->on_id =  $this->notify->id;
        // force will override failed. (not not sent.)
        $cev->whereAddIn("action", $force ? array('NOTIFYSENT') : array('NOTIFYSENT', 'NOTIFYFAIL', 'NOTIFYBOUNCE'), 'string');
        $cev->limit(1);
        if (!$this->force && $cev->count()) {
            $cev->find(true);
            $this->notify->flagDone($cev, $cev->action == 'NOTIFYSENT' ? 'alreadysent' : '');
            $this->errorHandler( $cev->action . " (fix old) ".  $cev->remarks);
        }
        
        $this->notifyObject = $this->notify->object();
        if ($this->notifyObject === false)  {
            $ev = $this->addEvent('NOTIFY', $this->notify,   "Notification event cleared (underlying object does not exist)" );
            $this->notify->flagDone($ev, '');
            $this->errorHandler(  $ev->remarks);
        }

        $p = $this->notify->person();
        if (isset($p->active) && empty($p->active)) {
            $ev = $this->addEvent('NOTIFY', $this->notify, "Notification event cleared (not user not active any more)" );;
             $this->notify->flagDone($ev, '');
            $this->errorHandler(  $ev->remarks);
        }

        if($this->notify->person_table == 'mail_imap_actor') {
            $p->email = $p->email();
        }

        // has it failed mutliple times..
        if (!empty($this->notify->field) && isset($p->{$this->notify->field .'_fails'}) && $p->{$this->notify->field .'_fails'} > 9) {
            $notifyTable =  DB_DataObject::factory($this->table);
            $notifyTable->to_email = $this->notify->to_email;
            $notifyTable->selectAdd();
            $notifyTable->selectAdd('MAX(event_id) AS max_event_id');
            $notifyTable->whereAdd('event_id != 0');
            $lastEvent = DB_DataObject::factory('Events');
            if($notifyTable->find(true) && $lastEvent->get($notifyTable->max_event_id)) {
                $ev = $lastEvent;
            } else {
                $ev = $this->addEvent('NOTIFY', $this->notify, "Notification event cleared (user has to many failures)" );;
            }
            $this->notify->flagDone($ev, '');
            $this->errorHandler($ev->remarks);
        }
        
        // let's work out the last notification sent to this user..
        $l = DB_DataObject::factory($this->table);
        $lar = array(
                'ontable' => $this->notify->ontable,
                'onid' => $this->notify->onid,
        );
        // only newer version of the database us this..
        if (isset($this->notify->person_table)) {
            $personid_col = strtolower($this->notify->person_table).'_id';
            if (isset($this->notify->{$personid_col})) {
                $lar[$personid_col] = $this->notify->{$personid_col};
            }
        }
        $l->setFrom( $lar );       
        $l->whereAdd('id != '. $this->notify->id);
        $l->orderBy('sent DESC');
        $l->limit(1);
        $ar = $l->fetchAll('sent');
        $last = empty($ar) ? date('Y-m-d H:i:s', 0) : $ar[0];
         
        // this may modify $p->email. (it will not update it though)
        // may modify $this->notify->email_id
        $this->email =  $this->makeEmail($this->notifyObject, $p, $last, $this->notify, $force);

        if($this->notify->reachEmailLimit()) {
            $ev = $this->addEvent('NOTIFY', $this->notify, "Notification event cleared (reach email limit)" );
            $this->notify->flagDone($ev, '');
            $this->errorHandler($ev->remarks);
        }
         
        if ($this->email === true)  {
            $ev = $this->addEvent('NOTIFY', $this->notify, "Notification event cleared (not required any more) - toEmail=true" );;
            $this->notify->flagDone($ev, '');
            $this->errorHandler( $ev->remarks);
        }

        if (is_a($this->email, 'PEAR_Error')) {
            $this->email = array(
                'error' => $this->email->toString()
            );
        }
        if ((empty($p) || empty($p->id)) && !empty($this->email['recipients'])) {
            // make a fake person..
            $p = (object) array(
                'email' => $this->email['recipients']
            );
        }
        if ($this->email === false || isset($this->email['error']) || empty($p)) {
            // object returned 'false' - it does not know how to send it..
            $ev = $this->addEvent('NOTIFYFAIL', $this->notify, isset($this->email['error'])  ? $this->email['error'] : "INTERNAL ERROR  - We can not handle " . $this->notify->ontable); 
            $this->notify->flagDone($ev, '');
            $this->errorHandler(  $ev->remarks);
        }
        

        if(empty($this->email['headers']['Date'])) {
            $this->email['headers']['Date'] = date('r'); 
        }
         
        
        if (isset($this->email['later'])) {
            $this->server->updateNotifyToNextServer($this->notify, $this->email['later'], true, $this->server_ipv6);
            $this->errorHandler("Delivery postponed by email creator to {$this->email['later']}");
        }
        
         
        if (empty($this->email['headers']['Message-Id'])) {
            $HOST = gethostname();
            $this->email['headers']['Message-Id'] = "<{$this->table}-{$id}@{$HOST}>";
            
        }
        if(empty($this->email['headers']['X-Notify-Id'])) {
            $this->email['headers']['X-Notify-Id'] = $this->notify->id;
        }
        if(empty($this->email['headers']['X-Notify-To-Id']) && !empty($p) && !empty($p->id)) {
            $this->email['headers']['X-Notify-To-Id'] = $p->id;
        }
        if(empty($this->email['headers']['X-Notify-Recur-Id']) && $this->notify->ontable == 'core_notify_recur' && !empty($this->notify->onid)) {
            $this->email['headers']['X-Notify-Recur-Id'] = $this->notify->onid;
        }

        // Populate to_email if empty - use the 'field' column to get correct email from person
        // e.g. if field is 'email2', get $p->email2
        $ww = clone($this->notify);
        if (empty($ww->to_email)) {
            $email_field = !empty($this->notify->field) ? $this->notify->field : 'email';
            $ww->to_email = !empty($p->{$email_field}) ? trim($p->{$email_field}) : '';
        }
        
        // Override with send-to from email content or CLI option
        if (!empty($opts['send-to'])) {
            $this->email['send-to'] = $opts['send-to'];
        }
        if (!empty($this->email['send-to'])) {
            $ww->to_email = trim($this->email['send-to']);
        }
        
        $explode_email = explode('@', $ww->to_email);
        $dom = array_pop($explode_email);
        $this->emailDomain = DB_DataObject::factory('core_domain')->loadOrCreate($dom);
        $ww->domain_id = $this->emailDomain->id;
        // if to_email has not been set!?
        $ww->update($this->notify); // if nothing has changed this will not do anything.
        $this->notify = clone($ww);

        // make sure there is a correct domain_id in the notify record
        // Fetch IPv6 server configuration if available
        $this->server_ipv6 = null;
        $ipv6 = DB_DataObject::factory('core_notify_server_ipv6');
        $ipv6->autoJoin();
        $ipv6->selectAdd('INET6_NTOA(ipv6_addr) as ipv6_addr_str');
        if (!empty($this->notify->ipv6_id) && $ipv6->get($this->notify->ipv6_id)) {
            $this->server_ipv6 = $ipv6;
            $this->debug("IPv6: Loaded existing IPv6 for domain_id={$this->notify->domain_id}, address=" . ($ipv6->ipv6_addr_str ?: 'NOT SET'));
        } else {
            $this->debug("IPv6: domain_id is empty, cannot load IPv6");
        }

        require_once 'Validate.php';
        if (!Validate::email($this->notify->to_email)) {
            $p->updateFails(isset($this->notify->field) ? $this->notify->field : 'email', $p::BAD_EMAIL_FAILS);
            $ev = $this->addEvent('NOTIFYFAIL', $this->notify, "INVALID ADDRESS: " . $this->notify->to_email);
            $this->notify->flagDone($ev, '');
            $this->errorHandler($ev->remarks);
        }

        // the domain DOESN'T HAVE mx record in the recent dns check (within last 5 days)
        // then DON't recheck dns
        if(!$this->emailDomain->has_mx && strtotime($this->emailDomain->mx_updated) > strtotime('now - 5 day')) {
            $ev = $this->addEvent('NOTIFYBADMX', $this->notify, "BAD ADDRESS - BAD DOMAIN - ". $this->notify->to_email );
            $this->notify->flagDone($ev, '');
            $this->errorHandler($ev->remarks);
        }
        
     
        $this->mxRecords = $this->mxs($this->emailDomain->domain);
        if (method_exists($this->notify, 'updateDomainMX')) {
            $this->notify->updateDomainMX(empty($this->mxRecords) ? 0 : 1);
        }
        $ww = clone($this->notify);

        // we might fail doing this...
        // need to handle temporary failure..
        
          // we try for 2 days..
        $retry = 15;
        if (strtotime($this->notify->act_start) <  strtotime('NOW - 1 HOUR')) {
            // older that 1 hour.
            $retry = 60;
        }
        
        if (strtotime($this->notify->act_start) <  strtotime('NOW - 1 DAY')) {
            // older that 1 day.
            $retry = 120;
        }
        if (strtotime($this->notify->act_start) <  strtotime('NOW - 2 DAY')) {
            // older that 1 day.
            $retry = 240;
        }
        
        if (empty($this->mxRecords)) {
            // only retry for 1 day if the MX issue..
            if ($retry < 240) {
                $this->addEvent('NOTIFY', $this->notify, 'MX LOOKUP FAILED ' . $this->emailDomain->domain );
                $this->notify->flagLater(date('Y-m-d H:i:s', strtotime('NOW + ' . $retry . ' MINUTES')));
                $this->errorHandler("MX LOOKUP FAILED " . $this->emailDomain->domain);
            }
            $ev = $this->addEvent('NOTIFYBADMX', $this->notify, "BAD ADDRESS - BAD DOMAIN - ". $this->notify->to_email );
            $this->notify->flagDone($ev, '');
            $this->errorHandler($ev->remarks);
        }

        if (!$this->force && strtotime($this->notify->act_start) <  strtotime('NOW - 8 DAY')) {
            $ev = $this->addEvent('NOTIFYFAIL', $this->notify, "BAD ADDRESS - GIVE UP - ". $this->notify->to_email );
            $this->notify->flagDone($ev, '');
            $this->errorHandler(  $ev->remarks);
        }
        
        $this->retryWhen = date('Y-m-d H:i:s', strtotime('NOW + ' . $retry . ' MINUTES'));
        // we can only update act_when if it has not been sent already (only happens when running in force mode..)
        // set act when if it's empty...
        $this->notify->act_when =  (!$this->notify->act_when || $this->notify->act_when == '0000-00-00 00:00:00') ? $this->retryWhen : $this->notify->act_when;
        $this->notify->update($ww);
        
        $this->server->initHelo($this->server_ipv6);

        $ff = HTML_FlexyFramework::get();

        if (!isset($ff->Mail['helo'])) {
            $this->errorHandler("config Mail[helo] is not set");
        }
        
        // Disabled for now
        /*
        $sender = DB_DataObject::factory('core_notify_sender');
        if(!empty($this->server_ipv6) && $sender->get($this->server->ipv6_sender_id)) {
            $this->email['headers']['From'] = $sender->email;
        }
        */

        $this->email = DB_DataObject::factory('core_notify_sender')->filterEmail($this->email, $this->notify);
    }
    
    /**
     * Send the email - tries each MX host IP until success or failure
     * Uses class properties set by beforeSend()
     */
    function send()
    {
        require_once 'Mail.php';
        $ff = HTML_FlexyFramework::get();
        
        $this->fail = false;
        
        // Convert MX hostnames to map of IP addresses => domain
        $this->useIpv6 = !empty($this->server_ipv6) && !empty($this->server_ipv6->ipv6_addr_str);
        $mx_ip_map = $this->convertMxsToIpMap($this->mxRecords, $this->useIpv6);

        // Note: $this->allMxIps is populated in convertMxsToIpMap() BEFORE filtering
        // Note: $this->validIps is populated in convertMxsToIpMap() AFTER filtering

        // ip address that failed the SMTP check
        $this->failedIp = false;
                        
        foreach($mx_ip_map as $smtp_host => $mx) {
            
           
            $this->debug_str = '';

            require_once 'Pman/Core/NotifyRouter.php';
            // $this->email['headers']['From'] may change when oauth is used and 'Send As' of the From User is used
            $notifyRouter = new Pman_Core_NotifyRouter($this, array(
                'smtpHost' => $smtp_host,
                'mx' => $mx
            ));
            $mailer = $notifyRouter->mailer;

            $emailHeaders = $this->email['headers'];

            if($this->useIpv6 && $this->server_ipv6->is_spam_rejecting) {
                $emailHeaders['From'] = $this->addDomainToEmail($emailHeaders['From'], $this->emailDomain->domain);
              
                if (!empty($emailHeaders['Reply-To'])) {
                    $emailHeaders['Reply-To'] = $this->addDomainToEmail($emailHeaders['Reply-To'], $this->emailDomain->domain);
                }
                $this->debug("IPv6: Spam rejecting, changing from address to {$emailHeaders['From']}");
            }
            
            $res = $mailer->send($this->notify->to_email, $emailHeaders, $this->email['body']);
            $this->lastSmtpResponse = $res;

            if (is_object($res)) {
                $res->backtrace = array(); 
            }
            $this->debug("GOT response to send: ". print_r($res,true));

            if ($res === true) {
                $mx_success = true;
                // success....
                
                $successEventName = (empty($this->email['successEventName'])) ? 'NOTIFYSENT' : $this->email['successEventName'];
                
                $ev = $this->addEvent($successEventName, $this->notify, "{$this->notify->to_email} - {$this->email['headers']['Subject']}");
                
                $ev->writeEventLog($this->debug_str);
                 
                $this->notify->flagDone($ev, $this->email['headers']['Message-Id']);
                 
                // enable cc in notify..
                if (!empty($this->email['headers']['Cc'])) {
                    $cmailer = Mail::factory('smtp',  isset($ff->Mail) ? $ff->Mail : array() );
                    $this->email['headers']['Subject'] = "(CC): " . $this->email['headers']['Subject'];
                    $cmailer->send($this->email['headers']['Cc'],    $this->email['headers'], $this->email['body']);
                    
                }
                
                if (!empty($this->email['bcc'])) {
                    $cmailer = Mail::factory('smtp', isset($ff->Mail) ? $ff->Mail : array() );
                    $this->email['headers']['Subject'] = "(CC): " . $this->email['headers']['Subject'];
                    $res = $cmailer->send($this->email['bcc'],  $this->email['headers'], $this->email['body']);
                    if (!$res || is_a($res, 'PEAR_Error')) {
                        echo "could not send bcc..\n";
                    } else {
                        echo "Sent BCC to {$this->email['bcc']}\n";
                    }
                }

                if($this->notify->ontable == 'mail_imap_message_user' && $this->notify->evtype == 'MAIL') {
                    $this->notifyObject->postSend($this);
                }
                 
                $this->successHandler("Message to {$this->notify->to_email} was successfully sent\n".
                                    "Message Id: {$this->notify->id}\n" .
                                    "Subject: {$this->email['headers']['Subject']}"
                                  );
            }

            // remove the failed ip from the list of valid ip addresses
            if(in_array($smtp_host, $this->validIps)) {
                $this->validIps = array_diff($this->validIps, array($smtp_host));
            }

            // what type of error..
            $code = empty($res->userinfo['smtpcode']) ? -1 : $res->userinfo['smtpcode'];
            if (!empty($res->code) && $res->code == 10001) {
                // fake greylist if timed out.
                $code = -1;
            }
            
            if ($code < 0) {
                $this->debug("Connection error with $smtp_host: " . $res->message);
                continue; // try next IP address
            }
            // give up after 2 days..
            if (in_array($code, array( 421, 450, 451, 452)) && strtotime($this->notify->act_start) > strtotime('NOW - 2 DAYS')) {
                // try again later..
                // check last event for this item..
                $errmsg=  $res->userinfo['smtpcode'] . ': ' .$res->message ;
                if (!empty($res->userinfo['smtptext'])) {
                    $errmsg=  $res->userinfo['smtpcode'] . ':' . $res->userinfo['smtptext'];
                }
                $ev = $this->addEvent('NOTIFY', $this->notify, 'GREYLISTED - ' . $errmsg);
                
                // For 452 "out of storage" errors, wait 12 hours before retrying
                $actual_retry_when = $this->retryWhen;
                if ($code == 452 && stripos($errmsg, 'out of storage') !== false) {
                    $actual_retry_when = date('Y-m-d H:i:s', strtotime('NOW + 12 HOURS'));
                    $this->debug("Mailbox full - delaying retry to {$actual_retry_when}");
                }
                
                $this->server->updateNotifyToNextServer($this->notify, $actual_retry_when, true, $this->server_ipv6);
                
                $this->errorHandler(  $ev->remarks);
            }
            
            $this->fail = true;
            $this->failedIp = $smtp_host;
            break;
        }
    }
    
    /**
     * Post-send handling: IPv6 setup, failure handling, retries
     * Uses class properties set by beforeSend() and send()
     */
    function postSend()
    {
        // Not using IPv6 AND no valid ipv4 addresses left AND some ipv4 addresses are blacklisted
        if(!$this->fail && !$this->useIpv6 && empty($this->validIps) && $this->isAnyIpv4Blacklisted) {
            $this->setUpIpv6("No more valid ipv4 address left for server (id: {$this->server->id})");
        }
        
        // after trying all mxs - could not connect...
        if  (!$this->force && !$this->fail && strtotime($this->notify->act_start) < strtotime('NOW - 2 DAYS')) {
            
            $errmsg=  " - UNKNOWN ERROR";
            if (isset($this->lastSmtpResponse->userinfo['smtptext'])) {
                $errmsg=  $this->lastSmtpResponse->userinfo['smtpcode'] . ':' . $this->lastSmtpResponse->userinfo['smtptext'];
            }
            
            $ev = $this->addEvent('NOTIFYFAIL', $this->notify,  "RETRY TIME EXCEEDED - " .  $errmsg);
            $this->notify->flagDone($ev, '');
            $this->errorHandler( $ev->remarks);
        }
        
        if ($this->fail) { //// !!!!<<< BLACKLIST DETECT?
        // fail.. = log and give up..
            $errmsg=   $this->lastSmtpResponse->userinfo['smtpcode'] . ': ' .$this->lastSmtpResponse->toString();
            if (isset($this->lastSmtpResponse->userinfo['smtptext'])) {
                $errmsg=  $this->lastSmtpResponse->userinfo['smtpcode'] . ':' . $this->lastSmtpResponse->userinfo['smtptext'];
            }

            
            // Check if error message contains spamhaus (case-insensitive)
            // If spamhaus is found, continue current behavior (don't pass to next server)
            $is_spamhaus = stripos($errmsg, 'spam') !== false 
                || stripos($errmsg, 'in rbl') !== false 
                || stripos($errmsg, 'reputation') !== false ;

            $shouldRetry = false;

            // smtpcode > 500 (permanent failure)
            $smtpcode = isset($this->lastSmtpResponse->userinfo['smtpcode']) ? $this->lastSmtpResponse->userinfo['smtpcode'] : 0;
            if(!empty($smtpcode) && $smtpcode > 500) {
                // spamhaus - not using ipv6 -> try setting up ipv6
                if($is_spamhaus && empty($this->server_ipv6)) {
                    $shouldRetry = true;
                    $this->debug("IPv6: Spamhaus detected (code: $smtpcode)");
                    // Build allocation reason with error details
                    $allocation_reason = "SMTP Code: " . $smtpcode;
                    if (!empty($this->lastSmtpResponse->userinfo['smtptext'])) {
                        $allocation_reason .= "; Error: " . $this->lastSmtpResponse->userinfo['smtptext'];
                    }

                    // blacklist the ipv4 host which return spamhaus
                    if($this->server->checkSmtpResponse($errmsg, $this->emailDomain, $this->failedIp)) {
                        $this->debug("Server (id: {$this->server->id}) is blacklisted by the ipv4 host: {$this->failedIp}");
                        // if there is no more valid ipv4 hosts left
                        if(empty($this->validIps)) {
                            $this->setUpIpv6($allocation_reason);
                            return;
                        }
                    }
                }
                // not spamhaus OR IPv6 already exists
                else {
                    $reason = array();
                    if (!$is_spamhaus) $reason[] = "not spamhaus";
                    if (!empty($this->server_ipv6)) {
                        $reason[] = "IPv6 already exists (" . ($this->server_ipv6->ipv6_addr_str ?: 'no address') . ")";

                        // is spamhaus AND 
                        // IPv6 already exists AND 
                        // this ip mapping is not already spam rejecting AND 
                        // this ip mapping does not have a reverse pointer
                        if($is_spamhaus && !$this->server_ipv6->is_spam_rejecting && !$this->server_ipv6->has_reverse_ptr) {
                            $old = clone($this->server_ipv6);
                            $this->server_ipv6->is_spam_rejecting = 1;
                            $this->server_ipv6->update($old);
                            $this->debug("IPv6: Set spam rejecting for " . $this->server_ipv6->ipv6_addr_str);

                            // Retry after setting spam rejecting
                            $shouldRetry = true;
                        }
                        
                    }
                    $this->debug("IPv6: Skipping setup - " . implode(", ", $reason));

                    // blacklist detection only if not using IPv6
                    if(empty($this->server_ipv6)) {
                        DB_DataObject::factory('core_notify_sender')->checkSmtpResponse($this->email, $this->notify, $errmsg);

                        // blacklisted
                        if($this->server->checkSmtpResponse($errmsg, $this->emailDomain)) {
                            $shouldRetry = true;
                        }
                    }
                }
            }

            // try next server
            if($shouldRetry) {
                var_dump($this->allMxIps);
                die('test');
                $ev = $this->addEvent('NOTIFY', $this->notify, 'GREYLISTED - ' . $errmsg);
                // Pass ALL MX IPs (not just validIps) so other servers can be properly checked
                // An IP that blocks server X might not block server Y
                $this->server->updateNotifyToNextServer($this->notify,  $this->retryWhen ,true, $this->server_ipv6, 
                    ($is_spamhaus ? $this->allMxIps : array())
                );
                $this->errorHandler("Retry in next server at {$this->retryWhen} - Error: $errmsg");
                // Successfully passed to next server, exit
                return;
            }
            
            // mark as failed
            $ev = $this->addEvent('NOTIFYBOUNCE', $this->notify, ($this->fail ? "FAILED - " : "RETRY TIME EXCEEDED - ") .  $errmsg);
            $this->notify->flagDone($ev, '');
            if (method_exists($this->notify, 'matchReject')) {
                $this->notify->matchReject($errmsg);
            }
             
            $this->errorHandler( $ev->remarks);
        }
        
        // at this point we just could not find any MX records..
        
        
        // try again.
        
        $ev = $this->addEvent('NOTIFY', $this->notify, 'GREYLIST - NO HOST CAN BE CONTACTED:' . $this->notify->to_email);
        
        $this->server->updateNotifyToNextServer($this->notify,  $this->retryWhen ,true, $this->server_ipv6);

        
         
        $this->errorHandler($ev->remarks);
    }
    
    function debug($str)
    {
        if (empty($this->cli_args['debug'])) {
            return;
            
        }
        echo $str . "\n";
    }
    function output() // framework output caller..
    {
        $this->errorHandler("done\n");
    }
    var $debug_str = '';
    
    function debugHandler ($smtp, $message)
    {
        $this->debug_str .= strlen($this->debug_str) ? "\n" : '';
        $this->debug_str .= $message;
        if ($this->debug) { 
            echo $message ."\n";
        }
    }
    
    /**
     * Convert array of MX hostnames to map of IP addresses => domain
     * Prioritizes IPv6 addresses if use_ipv6 is true
     * 
     * @param array $mxs Array of MX hostnames
     * @param bool $use_ipv6 Whether to perform IPv6 DNS lookups
     * @return array Map of IP address => domain name
     */
    function convertMxsToIpMap($mxs, $use_ipv6 = false)
    {
        $mx_ip_map = array();
        
        foreach ($mxs as $mx) {
            // Resolve IPv6 addresses if and only if using IPv6
            if($use_ipv6) {
                // Resolve IPv6 addresses (AAAA records)
                $ipv6_records = @dns_get_record($mx, DNS_AAAA);
                if (!empty($ipv6_records)) {
                    foreach ($ipv6_records as $record) {
                        if (empty($record['ipv6'])) {
                            continue;
                        }

                        $mx_ip_map[$record['ipv6']] = $mx;
                        
                    }
                }
                continue;
            }
            
            // Resolve IPv4 addresses (A records)
            $ipv4_records = @dns_get_record($mx, DNS_A);
            if (!empty($ipv4_records)) {
                foreach ($ipv4_records as $record) {
                    if (empty($record['ip'])) {
                        continue;
                    }
                    $mx_ip_map[$record['ip']] = $mx;
                }
            }
            
            // Also check hostname lookup (gethostbyname) as hosts file might override A record
            $hostname_ip = @gethostbyname($mx);
            if (!empty($hostname_ip) && filter_var($hostname_ip, FILTER_VALIDATE_IP)) {
                $mx_ip_map[$hostname_ip] = $mx;
                $this->debug("DNS: Found hosts file override for $mx: $hostname_ip");
            }
            
        }

        // If no IPs resolved, fall back to hostnames
        if (empty($mx_ip_map)) {
            foreach ($mxs as $mx) {
                $mx_ip_map[$mx] = $mx;
            }
            $this->debug("DNS: No IP addresses resolved for any MX, using hostnames");
        }
        
        // Store ALL IPs BEFORE any filtering (for passing to next server)
        // An IP that blocks server X might not block server Y
        // This includes both IPv4 and IPv6 addresses
        $this->allMxIps = array_keys($mx_ip_map);
        
        // If not using IPv6, use IPv4 addresses and skip blacklisted IPs
        if(!$use_ipv6) {
            // skip any blacklisted ip for this server
            $bl = DB_DataObject::factory('core_notify_blacklist');
            $bl->server_id = $this->server->id;
            $bl->whereAdd('ip IS NOT NULL');
            $bl->whereAdd('ip != 0x0');
            $bl->selectAdd();
            $bl->selectAdd('INET6_NTOA(ip) as ip_str');
            $blacklistedIps = $bl->fetchAll('ip_str');
            foreach($mx_ip_map as $ip => $mx) {
                if(in_array($ip, $blacklistedIps)) {
                    $this->debug("DNS: Blacklisted IP: $ip");
                    $this->isAnyIpv4Blacklisted = true;
                    unset($mx_ip_map[$ip]);
                }
            }
        }
        // If the ipv6 mapping has a reverse pointer and the domain of the ipv6 mapping does not match the suffix of the mx host,
        // skip the mx host
        // e.g. 
        // mx host: aspmx.l.google.com
        // domain the of existing ipv6 mapping with a reverse pointer: outlook.com
        // -> skip this mx host
        else {
            if($this->server_ipv6->has_reverse_ptr) {
                foreach($mx_ip_map as $ip => $mx) {
                    if(!str_ends_with($mx, $this->server_ipv6->domain_id_domain)) {
                        $this->debug("DNS: Skipping host $mx because it's suffix does not match the domain of the ipv6 mapping with a reverse pointer: " . $this->server_ipv6->domain_id_domain);
                        unset($mx_ip_map[$ip]);
                    }
                }
            }
        }
        
        // Set validIps AFTER filtering (these are the IPs we'll actually try)
        $this->validIps = array_keys($mx_ip_map);
        
        return $mx_ip_map;
    }
    
    /**
     * Add domain identifier to email address for spam rejection tracking
     * 
     * Takes an email address and domain, and modifies the local part by adding
     * a "+" suffix with the domain parts (excluding TLD) joined by "-"
     * 
     * @param string $str The email address (e.g., "user@example.com")
     * @param string $domain The domain to extract parts from (e.g., "example.com")
     * @return string Modified email address (e.g., "user+example@example.com")
     */
    function addDomainToEmail($str, $domain)
    {
        $fromArr = explode("@", $str);
        $parts = explode(".", $domain);
        if(count($parts) > 1) {
            array_pop($parts);
        }
        $fromArr[0] .=  '+' . implode("-", $parts);
        return implode("@", $fromArr);
    }

    /**
     * Set up ipv6 for the domain
     * 
     * @param string $errmsg The error message from the SMTP server
     * @return void
     */
    function setUpIpv6($errmsg)
    {
        $this->debug("No valid ipv4 address left for server (id: {$this->server->id}), trying to set up ipv6");

        // Build allocation reason with error details
        $allocation_reason = $errmsg;
        $allocation_reason .= "; Email: " . $this->notify->to_email;
        $allocation_reason .= "; Spamhaus detected: yes";

        // try to set up ipv6
        if($this->server_ipv6 = $this->emailDomain->setUpIpv6($allocation_reason, $this->mxRecords)) {
            // IPv6 set up successfully
            $this->debug("IPv6: Setup successful, will retry");

            $ev = $this->addEvent('NOTIFY', $this->notify, "GREYLISTED - {$errmsg}");
            $this->server->updateNotifyToNextServer($this->notify,  $this->retryWhen ,true, $this->server_ipv6);
            $this->errorHandler("Retry in next server at {$this->retryWhen} - Error: {$errmsg}");
            // Successfully passed to next server, exit
            return;
        }

        // no IPv6 can be set up -> don't retry
        $this->debug("IPv6: Setup failed");

        $ev = $this->addEvent('NOTIFYFAIL', $this->notify, "IPv6 SETUP FAILED - {$errmsg}");
        $this->notify->flagDone($ev, '');
        $this->errorHandler( $ev->remarks);
        return;
    }
    
    function errorHandler($msg)
    {
        if($this->error_handler == 'exception'){
            throw new Pman_Core_NotifySend_Exception_Fail($msg);
        }
        if (!$this->cli) {
            $this->jnotice("SENDFAIL", $msg );
        }
        die(date('Y-m-d h:i:s') . ' ' . $msg ."\n");
        
        
    }
    function successHandler($msg)
    {
        if (!$this->cli) {
            $this->jok(str_replace("\n", "<br/>", $msg));
        }
        die(date('Y-m-d h:i:s') . ' ' . $msg ."\n");
    }
    function updateServer($w)
    {
        $ff = HTML_FlexyFramework::get();
         
        if (empty($ff->Core_Notify['servers'])) {
            return;
        }
        // some classes dont support server routing
        if (!property_exists($w, 'server_id')) {
            return;
        }
        // next server..
        $w->server_id = ($w->server_id + 1) % count(array_keys($ff->Core_Notify['servers']));
         
    }
}
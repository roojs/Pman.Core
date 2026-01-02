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
    var $debug;
    
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
        $cnsi = DB_DataObject::factory('core_notify_server_ipv6');
        $cnsi->getLeastUsedIpv6ForMx('roojs.mail.protection.outlook.com');
        die('test');
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
        $force = empty($opts['force']) ? 0 : 1;
        
        $w = DB_DataObject::factory($this->table); // core_notify usually.

        if (!$w->get($id)) {
            $this->errorHandler("invalid id\n");
        }

        if (!$force && !empty($w->sent) && strtotime($w->act_when) < strtotime($w->sent)) {
             
            $this->errorHandler("already sent - repeat to early\n");
        }
        
        $this->server = DB_DataObject::Factory('core_notify_server')->getCurrent($this, $force);

        // Check if server is disabled or not found - exit gracefully (unless force is set)
        // id = 0 means no servers exist, is_active = 0 means server is disabled
        if (!$force && (empty($this->server->id) || empty($this->server->is_active))) {
            $this->errorHandler("Server is disabled or not found - exiting gracefully\n");
        }
        
         if (!$force &&  $w->server_id != $this->server->id) {
            $this->errorHandler("Server id does not match - message = {$w->server_id} - our id is {$this->server->id} use force to try again\n");
        }
        
        if (!empty($opts['debug'])) {
            print_r($w);
            $ff = HTML_FlexyFramework::get();
            if (!isset($ff->Core_Mailer)) {
                $ff->Core_Mailer = array();
            }
            HTML_FlexyFramework::get()->Core_Mailer['debug'] = true;
            $this->debug = true;
        }
        
        $sent = (empty($w->sent) || strtotime( $w->sent) < 100 ) ? false : true;
        
        if (!$force && (!empty($w->msgid) || $sent)) {
            $ww = clone($w);
            if (!$sent) {   // fix sent.
                $w->sent = strtotime( $w->sent) < 100 ? $w->sqlValue('NOW()') :$w->sent; // do not update if sent.....
                $w->update($ww);
            }    
            $this->errorHandler("message has been sent already.\n");
        }
        
         $cev = DB_DataObject::Factory('Events');
        $cev->on_table =  $this->table;
        $cev->on_id =  $w->id;
        // force will override failed. (not not sent.)
        $cev->whereAddIn("action", $force ? array('NOTIFYSENT') : array('NOTIFYSENT', 'NOTIFYFAIL'), 'string');
        $cev->limit(1);
        if (!$force && $cev->count()) {
            $cev->find(true);
            $w->flagDone($cev, $cev->action == 'NOTIFYSENT' ? 'alreadysent' : '');
            $this->errorHandler( $cev->action . " (fix old) ".  $cev->remarks);
        }
        
        $o = $w->object();
        
        if ($o === false)  {
             
            $ev = $this->addEvent('NOTIFY', $w,   "Notification event cleared (underlying object does not exist)" );
            $w->flagDone($ev, '');
            $this->errorHandler(  $ev->remarks);
        }
     
        
        
        $p = $w->person();
        
        if (isset($p->active) && empty($p->active)) {
            $ev = $this->addEvent('NOTIFY', $w, "Notification event cleared (not user not active any more)" );;
             $w->flagDone($ev, '');
            $this->errorHandler(  $ev->remarks);
        }

        if($w->person_table == 'mail_imap_actor') {
            $p->email = $p->email();
        }

        // has it failed mutliple times..
        
        if (!empty($w->field) && isset($p->{$w->field .'_fails'}) && $p->{$w->field .'_fails'} > 9) {
            $notifyTable =  DB_DataObject::factory($this->table);
            $notifyTable->to_email = $w->to_email;
            $notifyTable->selectAdd();
            $notifyTable->selectAdd('MAX(event_id) AS max_event_id');
            $notifyTable->whereAdd('event_id != 0');
            $lastEvent = DB_DataObject::factory('Events');
            if($notifyTable->find(true) && $lastEvent->get($notifyTable->max_event_id)) {
                $ev = $lastEvent;
            } else {
                $ev = $this->addEvent('NOTIFY', $w, "Notification event cleared (user has to many failures)" );;
            }
            $w->flagDone($ev, '');
            $this->errorHandler($ev->remarks);
        }
        
        // let's work out the last notification sent to this user..
        $l = DB_DataObject::factory($this->table);
        
        $lar = array(
                'ontable' => $w->ontable,
                'onid' => $w->onid,
        );
        // only newer version of the database us this..
        if (isset($w->person_table)) {
            $personid_col = strtolower($w->person_table).'_id';
            if (isset($w->{$personid_col})) {
                $lar[$personid_col] = $w->{$personid_col};
            }
        }
        
        
        $l->setFrom( $lar );       
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
        // may modify $w->email_id
        $email =  $this->makeEmail($o, $p, $last, $w, $force);

        if($w->reachEmailLimit()) {
            $ev = $this->addEvent('NOTIFY', $w, "Notification event cleared (reach email limit)" );
            $w->flagDone($ev, '');
            $this->errorHandler($ev->remarks);
        }
         
        if ($email === true)  {
            $ev = $this->addEvent('NOTIFY', $w, "Notification event cleared (not required any more) - toEmail=true" );;
            $w->flagDone($ev, '');
            $this->errorHandler( $ev->remarks);
        }
        if (is_a($email, 'PEAR_Error')) {
            $email =array(
                'error' => $email->toString()
            );
        }
      
        if ((empty($p) || empty($p->id)) && !empty($email['recipients'])) {
            // make a fake person..
            $p = (object) array(
                'email' => $email['recipients']
            );
        }
         
        if ($email === false || isset($email['error']) || empty($p)) {
            // object returned 'false' - it does not know how to send it..
            $ev = $this->addEvent('NOTIFYFAIL', $w, isset($email['error'])  ? $email['error'] : "INTERNAL ERROR  - We can not handle " . $w->ontable); 
            $w->flagDone($ev, '');
            $this->errorHandler(  $ev->remarks);
        }
        

        if(empty($email['headers']['Date'])) {
            $email['headers']['Date'] = date('r'); 
        }
         
        
        if (isset($email['later'])) {
             
            $this->server->updateNotifyToNextServer($w, $email['later'],true);
             
            $this->errorHandler("Delivery postponed by email creator to {$email['later']}");
        }
        
         
        if (empty($email['headers']['Message-Id'])) {
            $HOST = gethostname();
            $email['headers']['Message-Id'] = "<{$this->table}-{$id}@{$HOST}>";
            
        }

        if(empty($email['headers']['X-Notify-Id'])) {
            $email['headers']['X-Notify-Id'] = $w->id;
        }

        if(empty($email['headers']['X-Notify-To-Id']) && !empty($p) && !empty($p->id)) {
            $email['headers']['X-Notify-To-Id'] = $p->id;
        }

        if(empty($email['headers']['X-Notify-Recur-Id']) && $w->ontable == 'core_notify_recur' && !empty($w->onid)) {
            $email['headers']['X-Notify-Recur-Id'] = $w->onid;
        }


        
        
            
        
        //$p->email = 'alan@akbkhome.com'; //for testing..
        //print_r($email);exit;
        // should we fetch the watch that caused it.. - which should contain the method to call..
        // --send-to=test@xxx.com
       
       
        if (!empty($opts['send-to'])) {
            $email['send-to'] = $opts['send-to'];
        }
       
        if (!empty($email['send-to'])) {
            $p->email = $email['send-to'];
        }
       
        
            // since some of them have spaces?!?!
        $p->email = empty($p->email) ? '' : trim($p->email);
        $ww = clone($w);
        $ww->to_email = empty($ww->to_email) ? $p->email : $ww->to_email;
        
        if (!empty($opts['send-to'])) {
            $ww->to_email = $opts['send-to']; // override send to
        }
        
        $explode_email = explode('@', $ww->to_email);
        $dom = array_pop($explode_email);
         
        $core_domain = DB_DataObject::factory('core_domain')->loadOrCreate($dom);

        
        $ww->domain_id = $core_domain->id;
        // if to_email has not been set!?
        $ww->update($w); // if nothing has changed this will not do anything.
        $w = clone($ww);

        // make sure there is a correct domain_id in the notify record
        // Fetch IPv6 server configuration if available
        $this->server_ipv6 = null;
        $ipv6 = DB_DataObject::factory('core_notify_server_ipv6');
        if (!empty($w->ipv6_id) && $ipv6->get($w->ipv6_id)) {
            $this->server_ipv6 = $ipv6;
            $this->debug("IPv6: Loaded existing IPv6 for domain_id={$w->domain_id}, address=" . ($ipv6->getIpv6Addr() ?: 'NOT SET'));
        } else {
            $this->debug("IPv6: domain_id is empty, cannot load IPv6");
        }
      
        
        require_once 'Validate.php';
        if (!Validate::email($p->email)) {
            $p->updateFails(isset($w->field) ? $w->field : 'email', $p::BAD_EMAIL_FAILS);
            $ev = $this->addEvent('NOTIFYFAIL', $w, "INVALID ADDRESS: " . $p->email);
            $w->flagDone($ev, '');
            $this->errorHandler($ev->remarks);
            
        }
        
        
        $ff = HTML_FlexyFramework::get();

        // the domain DOESN'T HAVE mx record in the recent dns check (within last 5 days)
        // then DON't recheck dns
        if(!$core_domain->has_mx && strtotime($core_domain->mx_updated) > strtotime('now - 5 day')) {
            $ev = $this->addEvent('NOTIFYBADMX', $w, "BAD ADDRESS - BAD DOMAIN - ". $p->email );
            $w->flagDone($ev, '');
            $this->errorHandler($ev->remarks);
        }
        
     
        $mxs = $this->mxs($dom);
        if (method_exists($w, 'updateDomainMX')) {
            $w->updateDomainMX(empty($mxs) ? 0 : 1);
        }
        $ww = clone($w);

        // we might fail doing this...
        // need to handle temporary failure..
       
        
          // we try for 2 days..
        $retry = 15;
        if (strtotime($w->act_start) <  strtotime('NOW - 1 HOUR')) {
            // older that 1 hour.
            $retry = 60;
        }
        
        if (strtotime($w->act_start) <  strtotime('NOW - 1 DAY')) {
            // older that 1 day.
            $retry = 120;
        }
        if (strtotime($w->act_start) <  strtotime('NOW - 2 DAY')) {
            // older that 1 day.
            $retry = 240;
        }
        
        if (empty($mxs)) {
            
            // only retry for 1 day if the MX issue..
            if ($retry < 240) {
                $this->addEvent('NOTIFY', $w, 'MX LOOKUP FAILED ' . $dom );
                $w->flagLater(date('Y-m-d H:i:s', strtotime('NOW + ' . $retry . ' MINUTES')));
                $this->errorHandler($ev->remarks);
            }
            
            $ev = $this->addEvent('NOTIFYBADMX', $w, "BAD ADDRESS - BAD DOMAIN - ". $p->email );
            $w->flagDone($ev, '');
            $this->errorHandler($ev->remarks);
            
            
        }
        
        
        
        
        if (!$force && strtotime($w->act_start) <  strtotime('NOW - 8 DAY')) {
            $ev = $this->addEvent('NOTIFYFAIL', $w, "BAD ADDRESS - GIVE UP - ". $p->email );
            $w->flagDone($ev, '');
            $this->errorHandler(  $ev->remarks);
        }
        
        $retry_when = date('Y-m-d H:i:s', strtotime('NOW + ' . $retry . ' MINUTES'));
        
        //$this->addEvent('NOTIFY', $w, 'GREYLISTED ' . $p->email . ' ' . $res->toString());
        // we can only update act_when if it has not been sent already (only happens when running in force mode..)
        // set act when if it's empty...
        $w->act_when =  (!$w->act_when || $w->act_when == '0000-00-00 00:00:00') ? $retry_when : $w->act_when;
        
        $w->update($ww);
        
        $ww = clone($w);   
        
        $fail = false;
        require_once 'Mail.php';
        
        
        $this->server->initHelo($this->server_ipv6);

        if (!isset($ff->Mail['helo'])) {
            $this->errorHandler("config Mail[helo] is not set");
        }
        
        // Disabled for now
        /*
        $sender = DB_DataObject::factory('core_notify_sender');
        if(!empty($this->server_ipv6) && $sender->get($this->server->ipv6_sender_id)) {
            $email['headers']['From'] = $sender->email;
        }
        */

        $email = DB_DataObject::factory('core_notify_sender')->filterEmail($email, $w);
        
        // Convert MX hostnames to map of IP addresses => domain
        $use_ipv6 = !empty($this->server_ipv6) && !empty($this->server_ipv6->getIpv6Addr());
        $mx_ip_map = $this->convertMxsToIpMap($mxs, $use_ipv6);
                        
        foreach($mx_ip_map as $smtp_host => $mx) {
            
           
            $this->debug_str = '';
            $this->debug("Trying SMTP: $mx / HELO {$ff->Mail['helo']} (IP: $smtp_host)");
            
            // Prepare socket options with IPv6 binding if available
            $base_socket_options = isset($ff->Mail['socket_options']) ? $ff->Mail['socket_options'] : array(
                'ssl' => array(
                    'verify_peer_name' => false,
                    'verify_peer' => false, 
                    'allow_self_signed' => true,
                    'security_level' => 1
                )
            );
            
            // Check if we're using IPv6 and prepare HELO hostname
            $is_ipv6 = filter_var($smtp_host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
            $helo_hostname = $ff->Mail['helo'];
            
            if ($is_ipv6) {
                // Extract last hex segment from IPv6 address (e.g., 2400:8901:e001:52a::22a -> 22a)
                // Handle compressed zeros (::) by splitting and taking the rightmost part
                $ipv6_parts = explode('::', $smtp_host);
                $right_part = end($ipv6_parts);
                if (empty($right_part)) {
                    // Address ends with ::, get last segment from left part
                    $left_part = $ipv6_parts[0];
                    $segments = explode(':', $left_part);
                    $last_segment = end($segments);
                } else {
                    $segments = explode(':', $right_part);
                    $last_segment = end($segments);
                }
                
                // Remove leading zeros from last segment
                $last_segment = ltrim($last_segment, '0');
                if (empty($last_segment)) {
                    $last_segment = '0';
                }
                
                // Modify HELO hostname: sgfs1.media-outreach.com -> sgfs1-22a.media-outreach.com
                $helo_hostname = preg_replace('/^([^.]+)\./', '$1-' . $last_segment . '.', $ff->Mail['helo']);
                $this->debug("IPv6: Modified HELO hostname: {$ff->Mail['helo']} -> $helo_hostname");
            }
            
            $socket_options = $this->prepareSocketOptionsWithIPv6($base_socket_options, $smtp_host);
            
            // Format IPv6 address with brackets for PEAR Mail compatibility
            $mailer_host = $smtp_host;
            if ($is_ipv6) {
                $mailer_host = '[' . $smtp_host . ']';
            }
            
            $mailer = Mail::factory('smtp', array(
                'host'    => $mailer_host,
                'localhost' => $helo_hostname,
                'timeout' => 15,
                'socket_options' => $socket_options,
                
                 
                //'debug' => isset($opts['debug']) ?  1 : 0,
                'debug' => 1,
                'debug_handler' => array($this, 'debugHandler'),
                'dkim' => true
            ));
            
            // if the host is the mail host + it's authenticated add auth details
            // this normally will happen if you sent  Pman_Core_NotifySend['host']
             
            
            if (isset($ff->Mail['host']) && $ff->Mail['host'] == $mx && !empty($ff->Mail['auth'] )) {
                
                $mailer->auth = true;
                $mailer->username = $ff->Mail['username'];
                $mailer->password = $ff->Mail['password'];        
            }
            if (isset($ff->Core_Notify['tls'])) {
                // you can set Core_Notify:tls to true to force it to use tls on all connections (where available)
                $mailer->tls = $ff->Core_Notify['tls'];
            }
            if (isset($ff->Core_Notify['tls_exclude']) && in_array($mx, $ff->Core_Notify['tls_exclude'])) {
                $mailer->tls = false;
            }
             
            if(!empty($ff->Core_Notify) && !empty($ff->Core_Notify['routes'])){
                
                // we might want to regex 'office365 as a mx host 
                foreach ($ff->Core_Notify['routes'] as $server => $settings){
                    
                    $match = false;

                    if(in_array($dom, $settings['domains'])){
                        $match = true;
                    }

                    if (!$match && !empty($settings['mx'])) {
                        foreach($settings['mx'] as $mmx) {
                            if (preg_match($mmx, $mx)) {
                                $match = true;
                            }
                        }
                    }

                    if (!$match) {
                        continue;
                    }

                    $host = $server;

                    // check if there is a mail_imap_user for the 'From' email before using oauth
                    if(!empty($settings['auth']) && $settings['auth'] == 'XOAUTH2') {
                        // extract sender's email from 'From'
                        preg_match('/<([^>]+)>|^([^<>]+)$/', $email['headers']['From'], $matches);
                        $from = end($matches);

                        $fromUser = DB_DataObject::factory('mail_imap_user');
                        $fromUser->setFrom(array(
                            'is_active' => 1
                        ));
                        if(!$fromUser->get('email', $from)) {
                            continue;
                        }

                        if($fromUser->is_reply_to_only) {
                            $sendAsUser = DB_DataObject::factory('mail_imap_user');
                            // reply only and not send_as_id
                            if(!$sendAsUser->get($fromUser->send_as_id)) {
                                continue;
                            }


                            $fromUser = $sendAsUser;
                            require_once 'Mail/RFC822.php';
                            $rfc822 = new Mail_RFC822(array('name' => $fromUser->name, 'address' => $fromUser->email));
                            $email['headers']['From'] = $rfc822->toMime();
                        }
            
                        $s = $fromUser->server();
            
                        if($s === false) {
                            continue;
                        }
            
                        // server is set up correctly?
                        $sv = $s->is_valid();
                        if ($sv !== true) {
                            continue;
                        }
            
                        // server is oauth?
                        if(!$s->is_oauth) {
                            continue;
                        }
            
                        // has the token expired or does not exist
                        if (empty($fromUser->token) || empty($fromUser->id_token) || empty($fromUser->code)) {
                            continue;
                        }

                        $host = $s->smtp_host;
                        $settings['port'] = $s->smtp_port;
                        $settings['username'] = $fromUser->email;
                        $settings['password'] = $s->requestToken($fromUser);;
                    }
                     
                    // what's the minimum timespan.. - if we have 60/hour.. that's 1 every minute.
                    // if it's newer that '1' minute...
                    // then shunt it..
                    
                    $settings['rate'] = isset( $settings['rate']) ?  $settings['rate']  : 360;
                    
                    $seconds = floor((60 * 60) / $settings['rate']);
                    
                    $core_notify = DB_DataObject::factory($this->table);
                    $core_notify->domain_id = $core_domain->id;
                    $core_notify->server_id = $this->server->id;
                    $core_notify->whereAdd("
                        sent >= NOW() - INTERVAL $seconds SECOND
                    ");
                    
                    if($core_notify->count()){
                        $this->server->updateNotifyToNextServer( $w , date("Y-m-d H:i:s", time() + $seconds), true, $this->server_ipv6);
                        $this->errorHandler( " Too many emails sent by {$dom} - requeing");
                    }
                     
                    
                    
                    $mailer->host = $host;
                    $mailer->auth = isset($settings['auth']) ? $settings['auth'] : true;
                    $mailer->username = $settings['username'];
                    $mailer->password = $settings['password'];
                    if (isset($settings['port'])) {
                        $mailer->port = $settings['port'];
                    }
                    // Prepare socket options with IPv6 binding if available
                    $base_route_socket_options = isset($settings['socket_options']) ? $settings['socket_options'] : array(
                        'ssl' => array(
                            'verify_peer_name' => false,
                            'verify_peer' => false, 
                            'allow_self_signed' => true,
                            'security_level' => 1
                        )
                    );
                    
                    $mailer->socket_options = $this->prepareSocketOptionsWithIPv6($base_route_socket_options);
                    $mailer->tls = isset($settings['tls']) ? $settings['tls'] : true;
                    $this->debug("Got Core_Notify route match - " . print_R($mailer,true));

                    break;
                }
                
            }
            
            $res = $mailer->send($p->email, $email['headers'], $email['body']);
            
            if (is_object($res)) {
                $res->backtrace = array(); 
            }
            $this->debug("GOT response to send: ". print_r($res,true));

            if ($res === true) {
                $mx_success = true;
                // success....
                
                $successEventName = (empty($email['successEventName'])) ? 'NOTIFYSENT' : $email['successEventName'];
                
                $ev = $this->addEvent($successEventName, $w, "{$w->to_email} - {$email['headers']['Subject']}");
                
                $ev->writeEventLog($this->debug_str);
                 
                $w->flagDone($ev, $email['headers']['Message-Id']);
                 
                // enable cc in notify..
                if (!empty($email['headers']['Cc'])) {
                    $cmailer = Mail::factory('smtp',  isset($ff->Mail) ? $ff->Mail : array() );
                    $email['headers']['Subject'] = "(CC): " . $email['headers']['Subject'];
                    $cmailer->send($email['headers']['Cc'],    $email['headers'], $email['body']);
                    
                }
                
                if (!empty($email['bcc'])) {
                    $cmailer = Mail::factory('smtp', isset($ff->Mail) ? $ff->Mail : array() );
                    $email['headers']['Subject'] = "(CC): " . $email['headers']['Subject'];
                    $res = $cmailer->send($email['bcc'],  $email['headers'], $email['body']);
                    if (!$res || is_a($res, 'PEAR_Error')) {
                        echo "could not send bcc..\n";
                    } else {
                        echo "Sent BCC to {$email['bcc']}\n";
                    }
                }

                if($w->ontable == 'mail_imap_message_user' && $w->evtype == 'MAIL') {
                    $o->postSend($this);
                }
                 
                $this->successHandler("Message to {$w->to_email} was successfully sent\n".
                                    "Message Id: {$w->id}\n" .
                                    "Subject: {$email['headers']['Subject']}"
                                  );
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
            if (in_array($code, array( 421, 450, 451, 452))   && $next_try_min < (2*24*60)) {
                // try again later..
                // check last event for this item..
                //$errmsg=  $fail ? ($res->userinfo['smtpcode'] . ': ' .$res->toString()) :  " - UNKNOWN ERROR";
                $errmsg=  $res->userinfo['smtpcode'] . ': ' .$res->message ;
                if (!empty($res->userinfo['smtptext'])) {
                    $errmsg=  $res->userinfo['smtpcode'] . ':' . $res->userinfo['smtptext'];
                }
                //print_r($res);
                $ev = $this->addEvent('NOTIFY', $w, 'GREYLISTED - ' . $errmsg);
                
                // For 452 "out of storage" errors, wait 12 hours before retrying
                $actual_retry_when = $retry_when;
                if ($code == 452 && stripos($errmsg, 'out of storage') !== false) {
                    $actual_retry_when = date('Y-m-d H:i:s', strtotime('NOW + 12 HOURS'));
                    $this->debug("Mailbox full - delaying retry to {$actual_retry_when}");
                }
                
                $this->server->updateNotifyToNextServer($w, $actual_retry_when, true, $this->server_ipv6);
                
                $this->errorHandler(  $ev->remarks);
            }
            
            $fail = true;
            break;
        }
        
        // after trying all mxs - could not connect...
        if  (!$force && !$fail && ($next_try_min > (2*24*60) || strtotime($w->act_start) < strtotime('NOW - 3 DAYS'))) {
            
            $errmsg=  " - UNKNOWN ERROR";
            if (isset($res->userinfo['smtptext'])) {
                $errmsg=  $res->userinfo['smtpcode'] . ':' . $res->userinfo['smtptext'];
            }
            
            $ev = $this->addEvent('NOTIFYFAIL', $w,  "RETRY TIME EXCEEDED - " .  $errmsg);
            $w->flagDone($ev, '');
            $this->errorHandler( $ev->remarks);
        }
        
        if ($fail) { //// !!!!<<< BLACKLIST DETECT?
        // fail.. = log and give up..
            $errmsg=   $res->userinfo['smtpcode'] . ': ' .$res->toString();
            if (isset($res->userinfo['smtptext'])) {
                $errmsg=  $res->userinfo['smtpcode'] . ':' . $res->userinfo['smtptext'];
            }

            
            // Check if error message contains spamhaus (case-insensitive)
            // If spamhaus is found, continue current behavior (don't pass to next server)
            $is_spamhaus = stripos($errmsg, 'spam') !== false 
                || stripos($errmsg, 'in rbl') !== false 
                || stripos($errmsg, 'reputation') !== false ;

            $shouldRetry = false;

            // smtpcode > 500 (permanent failure)
            $smtpcode = isset($res->userinfo['smtpcode']) ? $res->userinfo['smtpcode'] : 0;
            if(!empty($smtpcode) && $smtpcode > 500) {
                // spamhaus - not using ipv6 -> try setting up ipv6
                if($is_spamhaus && empty($this->server_ipv6)) {
                    $this->debug("IPv6: Spamhaus detected (code: $smtpcode), attempting IPv6 setup");
                    // Build allocation reason with error details
                    $allocation_reason = "SMTP Code: " . $smtpcode;
                    if (!empty($res->userinfo['smtptext'])) {
                        $allocation_reason .= "; Error: " . $res->userinfo['smtptext'];
                    }
                    $allocation_reason .= "; Email: " . $w->to_email;
                    $allocation_reason .= "; Spamhaus detected: yes";
                    
                    // no IPv6 can be set up -> don't retry
                    // IPv6 set up successfully
                    if($this->server_ipv6 = $core_domain->setUpIpv6($allocation_reason, $mxs)) {
                        $this->debug("IPv6: Setup successful, will retry");
                        $shouldRetry = true;
                    } else {
                        $this->debug("IPv6: Setup failed");
                    }
                }
                // not spamhaus OR IPv6 already exists
                else {
                    $reason = array();
                    if (!$is_spamhaus) $reason[] = "not spamhaus";
                    if (!empty($this->server_ipv6)) $reason[] = "IPv6 already exists (" . ($this->server_ipv6->getIpv6Addr() ?: 'no address') . ")";
                    $this->debug("IPv6: Skipping setup - " . implode(", ", $reason));
                    DB_DataObject::factory('core_notify_sender')->checkSmtpResponse($email, $w, $errmsg);
                    // blacklisted
                    if($this->server->checkSmtpResponse($errmsg, $core_domain)) {
                        $shouldRetry = true;
                    }
                }
            }

            // try next server
            if($shouldRetry) {
                $this->server->updateNotifyToNextServer($w,  $retry_when ,true, $this->server_ipv6);
                $this->errorHandler("Retry in next server at {$retry_when} - Error: $errmsg");
                // Successfully passed to next server, exit
                return;
            }
            
            // mark as failed
            $ev = $this->addEvent('NOTIFYBOUNCE', $w, ($fail ? "FAILED - " : "RETRY TIME EXCEEDED - ") .  $errmsg);
            $w->flagDone($ev, '');
            if (method_exists($w, 'matchReject')) {
                $w->matchReject($errmsg);
            }
             
            $this->errorHandler( $ev->remarks);
        }
        
        // at this point we just could not find any MX records..
        
        
        // try again.
        
        $ev = $this->addEvent('NOTIFY', $w, 'GREYLIST - NO HOST CAN BE CONTACTED:' . $p->email);
        
        $this->server->updateNotifyToNextServer($w,  $retry_when ,true, $this->server_ipv6);

        
         
        $this->errorHandler($ev->remarks);
        
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
            $mx_use_ipv6 = $use_ipv6;
            
            // Resolve IPv6 addresses (AAAA records) only if mx_use_ipv6 is true
            $ipv6_records = @dns_get_record($mx, DNS_AAAA);
            if ($mx_use_ipv6 && !empty($ipv6_records)) {
                foreach ($ipv6_records as $record) {
                    if (empty($record['ipv6'])) {
                        continue;
                    }

                    $mx_ip_map[$record['ipv6']] = $mx;
                    
                }
            }
            
            // Resolve IPv4 addresses (A records)
            $ipv4_records = @dns_get_record($mx, DNS_A);
            $dns_ips = array();
            if (!empty($ipv4_records)) {
                foreach ($ipv4_records as $record) {
                    if (empty($record['ip'])) {
                        continue;
                    }
                    $dns_ips[] = $record['ip'];
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
        
        return $mx_ip_map;
    }
    
    /**
     * Prepare socket options with IPv6 binding if available
     * 
     * @param array $base_options Base socket options
     * @param string $smtp_host The SMTP host (IP address or hostname)
     * @return array Enhanced socket options with IPv6 binding
     */
    function prepareSocketOptionsWithIPv6($base_options = array(), $smtp_host = null)
    {
        $socket_options = $base_options;
        
        // Return early if not using IPv6
        if (empty($smtp_host) || !filter_var($smtp_host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $socket_options;
        }
        
        // Add IPv6 binding if server_ipv6 is configured
        $ipv6_addr_str = !empty($this->server_ipv6) ? $this->server_ipv6->getIpv6Addr() : false;
        if ($ipv6_addr_str) {
            $socket_options['socket'] = array(
                'bindto' => '[' . $ipv6_addr_str . ']:0'
            );
            $this->debug("IPv6: Binding SMTP connection to IPv6 address: " . $ipv6_addr_str);
        } else {
            $this->debug("IPv6: Not binding to IPv6 (server_ipv6=" . (empty($this->server_ipv6) ? 'empty' : 'set') . ", ipv6_addr=" . ($ipv6_addr_str ?: 'empty') . ")");
        }
        
        return $socket_options;
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
<?php

class Pman_Core_NotifyRouter
{
    // Pman_Core_NotifySend instance
    var $notifySend;

    var $mailer;

    // Setting for the Mail_smtp object
    var $host = '';
    var $localhost = '';
    var $socket_options = array();
    
    // Domain
    var $dom = '';
    // Core_notify_server instance
    var $server = null;
    // Core_notify_server_ipv6 instance
    var $server_ipv6 = null;
    // Whether to use IPv6
    var $use_ipv6 = false;
    
    function __construct($notifySend, $smtp_host, $mx, $dom)
    {
        $this->notifySend = $notifySend;
        $this->server = $notifySend->server;
        $this->server_ipv6 = $notifySend->server_ipv6;
        $this->use_ipv6 = !empty($this->server_ipv6) && !empty($this->server_ipv6->ipv6_addr_str) && filter_var($smtp_host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);

        $ff = HTML_FlexyFramework::get();

        $this->debug("Trying SMTP: $mx / HELO {$ff->Mail['helo']} (IP: $smtp_host)");

        $this->setHost($smtp_host);
        $this->setLocalhost();
        $this->setSocketOptions($smtp_host);
        $this->initMailer();
    }

    /**
     * Debug a message
     * @param string $str The message to debug
     * @return void
     */
    function debug($str)
    {
        $this->notifySend->debug($str);
    }


    /**
     * Error handler
     * @param string $msg The message to error
     * @return void
     */
    function errorHandler($msg)
    {
        $this->notifySend->errorHandler($msg);
    }

    /**
     * Set the host for the Mail_smtp object
     * @param string $smtp_host The SMTP host (IP address or hostname)
     * @return void
     */
    function setHost($smtp_host)
    {
        // Format IPv6 address with brackets for PEAR Mail compatibility
        $mailer_host = $smtp_host;
        if ($this->use_ipv6) {
            $mailer_host = '[' . $smtp_host . ']';
        }

        $this->host = $mailer_host;
    }

    /**
     * Set the localhost for the Mail_smtp object
     * @return void
     */
    function setLocalhost()
    {
        $ff = HTML_FlexyFramework::get();
        $helo_hostname = $ff->Mail['helo'];

        if ($this->use_ipv6) {
            // Extract last hex segment from IPv6 address (e.g., 2400:8901:e001:52a::22a -> 22a)
            // Handle compressed zeros (::) by splitting and taking the rightmost part
            $ipv6_parts = explode('::', $this->server_ipv6->ipv6_addr_str);
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

        $this->localhost = $helo_hostname;
    }

    /**
     * Set the socket options for the Mail_smtp object
     * @return void
     */
    function setSocketOptions($smtp_host)
    {
        $ff = HTML_FlexyFramework::get();
        // Prepare socket options with IPv6 binding if available
        $base_socket_options = isset($ff->Mail['socket_options']) ? $ff->Mail['socket_options'] : array(
            'ssl' => array(
                'verify_peer_name' => false,
                'verify_peer' => false, 
                'allow_self_signed' => true,
                'security_level' => 1
            )
        );

        $this->socket_options = $this->prepareSocketOptionsWithIPv6($base_socket_options, $smtp_host);
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
        if (empty($smtp_host) || !$this->use_ipv6) {
            $ipv6_addr_str = !empty($this->server_ipv6) ? $this->server_ipv6->ipv6_addr_str : false;
            $this->debug("IPv6: Not binding to IPv6 (server_ipv6=" . (empty($this->server_ipv6) ? 'empty' : 'set') . ", ipv6_addr=" . ($ipv6_addr_str ?: 'empty') . ")");
            return $socket_options;
        }
        
        // Add IPv6 binding if server_ipv6 is configured
        $socket_options['socket'] = array(
            'bindto' => '[' . $ipv6_addr_str . ']:0'
        );
        $this->debug("IPv6: Binding SMTP connection to IPv6 address: " . $ipv6_addr_str);
        
        return $socket_options;
    }
    
    /**
     * Initialize the Mail_smtp object and set $this->mailer
     * @return void
     */
    function initMailer()
    {
        $mailer = Mail::factory('smtp', array(
            'host'          => $this->host,
            'localhost'     => $this->localhost,
            'timeout'       => 15,
            'socket_options'=> $this->socket_options,
            'debug'         => 1,
            'debug_handler' => array($this->notifySend, 'debugHandler'),
            'dkim'          => true
        ));
        $this->mailer = $mailer;

        $this->setMailerOptionsFromConfig();
    }

    /**
     * Set the options for $this->mailer based on the config
     * @return void
     */
    function setMailerOptionsFromConfig()
    {
        $mailer = $this->mailer;

        $ff = HTML_FlexyFramework::get();
            
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
    }
}

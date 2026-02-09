<?php

/**
 * Initialize the Mail_smtp object and set $this->mailer
 * so that it can be used to send emails.
 * 
 * Usage example in NotifySend.php:
 * 
 * $notifyRouter = new Pman_Core_NotifyRouter($this, array(
 *  'smtpHost' => $smtp_host, // the IP address of the server
 *  'mx' => $mx, // the MX host
 * ));
 * $mailer = $notifyRouter->mailer;
 * 
 * Access domain/email/notify via $this->notifySend->emailDomain, $this->notifySend->email, $this->notifySend->notify
 */
class Pman_Core_NotifyRouter
{
    var $mailer;

    // Pman_Core_NotifySend instance
    var $notifySend;

    // SMTP host (usually the IP address of the server)
    var $smtpHost = '';
    // MX host
    var $mx = '';
    // Whether to use IPv6
    var $useIpv6 = false;
    
    /**
     * Constructor
     * @param Pman_Core_NotifySend $notifySend The NotifySend instance
     * @param array $options The options for the NotifyRouter
     * @return void
     */
    function __construct($notifySend, $options = array())
    {
        $this->notifySend = $notifySend;
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        $this->useIpv6 = !empty($this->notifySend->server_ipv6) && !empty($this->notifySend->server_ipv6->ipv6_addr_str) && filter_var($this->smtpHost, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);

        $ff = HTML_FlexyFramework::get();

        $this->debug("Trying SMTP: $this->mx / HELO {$ff->Mail['helo']} (IP: $this->smtpHost)");
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
     * Get the host for the Mail_smtp object
     * @return string The host for the Mail_smtp object
     */
    function getHost()
    {
        // Format IPv6 address with brackets for PEAR Mail compatibility
        $mailer_host = $this->smtpHost;
        if ($this->useIpv6) {
            $mailer_host = '[' . $this->smtpHost . ']';
        }

        return $mailer_host;
    }

    /**
     * Get the localhost for the Mail_smtp object
     * @return string The localhost for the Mail_smtp object
     */
    function getLocalhost()
    {
        $ff = HTML_FlexyFramework::get();
        $helo_hostname = $ff->Mail['helo'];

        if ($this->useIpv6) {
            // Extract last hex segment from IPv6 address (e.g., 2400:8901:e001:52a::22a -> 22a)
            // Handle compressed zeros (::) by splitting and taking the rightmost part
            $ipv6_parts = explode('::', $this->notifySend->server_ipv6->ipv6_addr_str);
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

        return $helo_hostname;
    }

    /**
     * Get the socket options for the Mail_smtp object
     * @return array The socket options for the Mail_smtp object
     */
    function getSocketOptions()
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

        return $this->prepareSocketOptionsWithIPv6($base_socket_options);
    }

    /**
     * Prepare socket options with IPv6 binding if available
     * 
     * @param array $base_options Base socket options
     * @return array Enhanced socket options with IPv6 binding
     */
    function prepareSocketOptionsWithIPv6($base_options = array())
    {
        $socket_options = $base_options;
        
        // Return early if not using IPv6
        if (empty($this->smtpHost) || !$this->useIpv6) {
            $ipv6_addr_str = !empty($this->notifySend->server_ipv6) ? $this->notifySend->server_ipv6->ipv6_addr_str : false;
            $this->debug("IPv6: Not binding to IPv6 (server_ipv6=" . (empty($this->notifySend->server_ipv6) ? 'empty' : 'set') . ", ipv6_addr=" . ($ipv6_addr_str ?: 'empty') . ")");
            return $socket_options;
        }
        
        // Add IPv6 binding if serverIpv6 is configured
        $socket_options['socket'] = array(
            'bindto' => '[' . $this->notifySend->server_ipv6->ipv6_addr_str . ']:0'
        );
        $this->debug("IPv6: Binding SMTP connection to IPv6 address: " . $this->notifySend->server_ipv6->ipv6_addr_str);
        
        return $socket_options;
    }
    
    /**
     * Initialize the Mail_smtp object and set $this->mailer
     * @return void
     */
    function initMailer()
    {
        $mailer = Mail::factory('smtp', array(
            'host'          => $this->getHost(),
            'localhost'     => $this->getLocalhost(),
            'timeout'       => 15,
            'socket_options'=> $this->getSocketOptions(),
            'debug'         => 1,
            'debug_handler' => array($this->notifySend, 'debugHandler'),
            'dkim'          => true
        ));
        $this->mailer = $mailer;

        $this->setMailerOptionsBasedOnConfig();
    }

    /**
     * Set the options for $this->mailer based on the config
     * @return void
     */
    function setMailerOptionsBasedOnConfig()
    {
        $mailer = $this->mailer;

        $ff = HTML_FlexyFramework::get();
            
        // if the host is the mail host + it's authenticated add auth details
        // this normally will happen if you sent  Pman_Core_NotifySend['host']

        if (isset($ff->Mail['host']) && $ff->Mail['host'] == $this->mx && !empty($ff->Mail['auth'] ) && !empty($ff->Mail['username']) && !empty($ff->Mail['password'])) {
            
            $mailer->auth = true;
            $mailer->username = $ff->Mail['username'];
            $mailer->password = $ff->Mail['password'];
        }
        if (isset($ff->Core_Notify['tls'])) {
            // you can set Core_Notify:tls to true to force it to use tls on all connections (where available)
            $mailer->tls = $ff->Core_Notify['tls'];
        }
        if (isset($ff->Core_Notify['tls_exclude']) && in_array($this->mx, $ff->Core_Notify['tls_exclude'])) {
            $mailer->tls = false;
        }

        $this->setMailerOptionsBasedOnRoute();
    }

    function setMailerOptionsBasedOnRoute()
    {
        $mailer = $this->mailer;

        $ff = HTML_FlexyFramework::get();

        if(!empty($ff->Core_Notify) && !empty($ff->Core_Notify['routes'])){
                
            // we might want to regex 'office365 as a mx host 
            foreach ($ff->Core_Notify['routes'] as $server => $settings){
                
                $match = false;

                if(!empty($settings['domains']) && in_array($this->notifySend->emailDomain->domain, $settings['domains'])){
                    $match = true;
                }

                if (!$match && !empty($settings['mx'])) {
                    foreach($settings['mx'] as $mmx) {
                        if (preg_match($mmx, $this->mx)) {
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
                    preg_match('/<([^>]+)>|^([^<>]+)$/', $this->notifySend->email['headers']['From'], $matches);
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
                        $this->notifySend->email['headers']['From'] = $rfc822->toMime();
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
                
                $core_notify = DB_DataObject::factory($this->notifySend->table);
                $core_notify->domain_id = $this->notifySend->emailDomain->id;
                $core_notify->server_id = $this->notifySend->server->id;
                $core_notify->whereAdd("
                    sent >= NOW() - INTERVAL $seconds SECOND
                ");
                
                if($core_notify->count()){
                    $this->notifySend->server->updateNotifyToNextServer(
                        $this->notifySend->notify , date("Y-m-d H:i:s", time() + $seconds), 
                        true, $this->notifySend->server_ipv6, $this->notifySend->allMxIpv4s);
                    $this->errorHandler( " Too many emails sent by {$this->notifySend->emailDomain->domain} - requeing");
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

    /**
     * Convert array of MX hostnames to map of IP addresses => domain (static).
     * Prioritizes IPv6 if notifySend->useIpv6. Updates notifySend: allMxIpv4s, validIps, isAnyIpv4Blacklisted, useIpv6.
     *
     * @param Pman_Core_NotifySend $notifySend
     * @param array $mxs Array of MX hostnames
     * @return array Map of IP address => domain name
     */
    static function convertMxsToIpMap($notifySend, $mxs)
    {
        $mx_ip_map = array();
        $mx_ipv6_map = array();
        $mx_ipv4_map = array();

        foreach ($mxs as $mx) {
            $ipv6_records = @dns_get_record($mx, DNS_AAAA);
            if (!empty($ipv6_records)) {
                foreach ($ipv6_records as $record) {
                    if (empty($record['ipv6'])) {
                        continue;
                    }
                    $mx_ipv6_map[$record['ipv6']] = $mx;
                }
            }
            $ipv4_records = @dns_get_record($mx, DNS_A);
            if (!empty($ipv4_records)) {
                foreach ($ipv4_records as $record) {
                    if (empty($record['ip'])) {
                        continue;
                    }
                    $mx_ipv4_map[$record['ip']] = $mx;
                }
            }
            $hostname_ip = @gethostbyname($mx);
            if (!empty($hostname_ip) && filter_var($hostname_ip, FILTER_VALIDATE_IP)) {
                $mx_ipv4_map[$hostname_ip] = $mx;
                $notifySend->debug("DNS: Found hosts file override for $mx: $hostname_ip");
            }
        }

        $notifySend->allMxIpv4s = array_keys($mx_ipv4_map);
        $mx_ip_map = $notifySend->useIpv6 ? $mx_ipv6_map : $mx_ipv4_map;

        if (empty($mx_ip_map)) {
            foreach ($mxs as $mx) {
                $mx_ip_map[$mx] = $mx;
            }
            $notifySend->debug("DNS: No IP addresses resolved for any MX, using hostnames");
            $notifySend->validIps = array_keys($mx_ip_map);
            return $mx_ip_map;
        }

        if (!$notifySend->useIpv6) {
            $mx_ip_map = self::filterBlacklistedIps($notifySend, $mx_ip_map, $mx_ipv4_map);
            $notifySend->validIps = array_keys($mx_ip_map);
            return $mx_ip_map;
        }

        $mx_ip_map = self::filterIpv6ByReversePtr($notifySend, $mx_ip_map);
        if (empty($mx_ip_map)) {
            $notifySend->debug("DNS: No IPv6 addresses resolved, fallback to use IPv4 addresses");
            $notifySend->useIpv6 = false;
            $mx_ip_map = self::filterBlacklistedIps($notifySend, $mx_ipv4_map, $mx_ipv4_map);
        }
        $notifySend->validIps = array_keys($mx_ip_map);
        return $mx_ip_map;
    }

    /**
     * Filter IP map by blacklisted IPs for current server (static). Updates notifySend->isAnyIpv4Blacklisted.
     *
     * @param Pman_Core_NotifySend $notifySend
     * @param array $mx_ip_map Map to filter (ip => mx)
     * @param array $full_ipv4_map Unused, for signature clarity
     * @return array Filtered map
     */
    static function filterBlacklistedIps($notifySend, $mx_ip_map, $full_ipv4_map = array())
    {
        $ns = $notifySend;
        $bl = DB_DataObject::factory('core_notify_blacklist');
        $bl->server_id = $ns->server->id;
        $bl->whereAdd('ip != 0x0');
        $bl->selectAdd();
        $bl->selectAdd('INET6_NTOA(ip) as ip_str');
        $blacklistedIps = $bl->fetchAll('ip_str');
        foreach ($mx_ip_map as $ip => $mx) {
            if (in_array($ip, $blacklistedIps)) {
                $ns->debug("DNS: Blacklisted IP: $ip");
                $ns->isAnyIpv4Blacklisted = true;
                unset($mx_ip_map[$ip]);
            }
        }
        return $mx_ip_map;
    }

    /**
     * Filter IPv6 map by reverse PTR / domain suffix match (static). Uses notifySend->server_ipv6.
     *
     * @param Pman_Core_NotifySend $notifySend
     * @param array $mx_ip_map Map ip => mx
     * @return array Filtered map
     */
    static function filterIpv6ByReversePtr($notifySend, $mx_ip_map)
    {
        $ns = $notifySend;
        if (empty($ns->server_ipv6) || !empty($ns->server_ipv6->has_reverse_ptr)) {
            return $mx_ip_map;
        }
        $cnsi = DB_DataObject::factory('core_notify_server_ipv6');
        $cnsi->autoJoin();
        $cnsi->ipv6_addr = $ns->server_ipv6->ipv6_addr;
        $domainsMappedToCurrentIpv6 = $cnsi->fetchAll('domain_id_domain');
        foreach ($mx_ip_map as $ip => $mx) {
            $match = false;
            foreach ($domainsMappedToCurrentIpv6 as $domain) {
                if (str_ends_with($mx, $domain)) {
                    $match = true;
                    break;
                }
            }
            if (!$match) {
                $ns->debug("DNS: Skipping host $mx because no domain mapped to the current server's IPv6 address (" . $ns->server_ipv6->ipv6_addr_str . ") matches the suffix of the mx host");
                unset($mx_ip_map[$ip]);
            }
        }
        return $mx_ip_map;
    }

    /**
     * Try to set up IPv6 for the domain; on success requeue to next server, on failure flag done and error.
     *
     * @param string $errmsg Error message from SMTP
     * @return void
     */
    function setUpIpv6($errmsg)
    {
        $ns = $this->notifySend;
        $this->debug("No valid ipv4 address left for server (id: {$ns->server->id}), trying to set up ipv6");

        $allocation_reason = $errmsg;
        $allocation_reason .= "; Email: " . $ns->notify->to_email;
        $allocation_reason .= "; Spamhaus detected: yes";

        $server_ipv6 = $ns->emailDomain->setUpIpv6($allocation_reason, $ns->mxRecords);
        if (empty($server_ipv6)) {
            $this->debug("IPv6: Setup failed");
            $ev = $ns->addEvent('NOTIFYFAIL', $ns->notify, "IPv6 SETUP FAILED - {$errmsg}");
            $ns->notify->flagDone($ev, '');
            $this->errorHandler($ev->remarks);
            return;
        }

        $ns->server_ipv6 = $server_ipv6;
        $this->debug("IPv6: Setup successful, will retry");
        $ns->addEvent('NOTIFY', $ns->notify, "GREYLISTED - {$errmsg}");
        $ns->server->updateNotifyToNextServer($ns->notify, $ns->retryWhen, true, $ns->server_ipv6, $ns->allMxIpv4s);
        $this->errorHandler("Retry in next server at {$ns->retryWhen} - Error: {$errmsg}");
    }
}

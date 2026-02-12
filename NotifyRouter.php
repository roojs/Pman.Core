<?php

/**
 * Builds SMTP mailer for a given host/mx; obtain it via mailer().
 * 
 * Usage example in NotifySend.php:
 * 
 * $notifyRouter = new Pman_Core_NotifyRouter($this, array(
 *  'smtpHost' => $smtp_host, // the IP address of the server
 *  'mx' => $mx, // the MX host
 * ));
 * $mailer = $notifyRouter->mailer();
 * 
 * Access domain/email/notify via $this->notifySend->emailDomain, $this->notifySend->email, $this->notifySend->notify
 */
class Pman_Core_NotifyRouter
{
    /** Result of last convertMxsToIpMap; caller uses these directly (no copying into NotifySend) */
    static $all_mx_ipv4s = array();
    static $valid_ips = array();
    static $use_ipv6 = false;
    static $is_any_ipv4_blacklisted = false;

    var $mailer;

    /** Pman_Core_NotifySend instance.
     *  Uses: debug, cli_args['debug'], server_ipv6, emailDomain, email, table, server, notify;
     *  methods: errorHandler(), debugHandler() (callback for Mail).
     */
    var $notifySend;

    // SMTP host (usually the IP address of the server)
    var $smtpHost = '';
    // MX host
    var $mx = '';
    // Whether to use IPv6
    var $useIpv6 = false;

    /** Base socket options from config (set in ctor) */
    var $base_socket_options;

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
        $this->useIpv6 = !empty($this->notifySend->server_ipv6) 
            && !empty($this->notifySend->server_ipv6->ipv6_addr_str) 
            && filter_var($this->smtpHost, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);

        $ff = HTML_FlexyFramework::get();
        $this->base_socket_options = isset($ff->Mail['socket_options']) ? $ff->Mail['socket_options'] : array(
            'ssl' => array(
                'verify_peer_name' => false,
                'verify_peer' => false,
                'allow_self_signed' => true,
                'security_level' => 1
            )
        );

        $this->debug("Trying SMTP: $this->mx / HELO {$ff->Mail['helo']} (IP: $this->smtpHost)");
    }

    /**
     * Debug a message (echo when notifySend debug boolean is set)
     */
    private function debug($str)
    {
        if (!empty($this->notifySend->debug) || !empty($this->notifySend->cli_args['debug'])) {
            echo $str . "\n";
        }
    }


    /**
     * HELO hostname (optionally IPv6-suffixed from server_ipv6)
     * @return string
     */
    private function heloName()
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
     * Socket options from $this->base_socket_options, with IPv6 binding if applicable.
     * @return array
     */
    private function socketOptions()
    {
        $socket_options = $this->base_socket_options;
        
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
     * Create (if needed) and return the Mail_smtp instance for this router.
     * @return object Mail_smtp mailer
     */
    function mailer()
    {
        if (!isset($this->mailer)) {
            $this->mailer = Mail::factory('smtp', array(
                'host'          => $this->useIpv6 ? '[' . $this->smtpHost . ']' : $this->smtpHost,
                'localhost'     => $this->heloName(),
                'timeout'       => 15,
                'socket_options'=> $this->socketOptions(),
                'debug'         => 1,
                'debug_handler' => array($this->notifySend, 'debugHandler'),
                'dkim'          => true
            ));
            $this->applyConfig();
        }
        return $this->mailer;
    }

    /**
     * Set the options for $this->mailer based on the config
     * @return void
     */
    private function applyConfig()
    {
        $ff = HTML_FlexyFramework::get();
            
        // if the host is the mail host + it's authenticated add auth details
        // this normally will happen if you sent  Pman_Core_NotifySend['host']

        if (isset($ff->Mail['host']) && $ff->Mail['host'] == $this->mx && !empty($ff->Mail['auth'] ) && !empty($ff->Mail['username']) && !empty($ff->Mail['password'])) {
            $this->mailer->auth = true;
            $this->mailer->username = $ff->Mail['username'];
            $this->mailer->password = $ff->Mail['password'];
        }
        if (isset($ff->Core_Notify['tls'])) {
            // you can set Core_Notify:tls to true to force it to use tls on all connections (where available)
            $this->mailer->tls = $ff->Core_Notify['tls'];
        }
        if (isset($ff->Core_Notify['tls_exclude']) && in_array($this->mx, $ff->Core_Notify['tls_exclude'])) {
            $this->mailer->tls = false;
        }

        $this->applyRoute();
    }

    private function applyRoute()
    {
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
                        true, $this->notifySend->server_ipv6, self::$all_mx_ipv4s);
                    $this->notifySend->errorHandler(" Too many emails sent by {$this->notifySend->emailDomain->domain} - requeing");
                }
                
                
                $this->mailer->host = $host;
                $this->mailer->auth = isset($settings['auth']) ? $settings['auth'] : true;
                $this->mailer->username = $settings['username'];
                $this->mailer->password = $settings['password'];
                if (isset($settings['port'])) {
                    $this->mailer->port = $settings['port'];
                }
                // Route is final: overwrite base_socket_options then use socketOptions()
                $this->base_socket_options = isset($settings['socket_options']) ? $settings['socket_options'] : array(
                    'ssl' => array(
                        'verify_peer_name' => false,
                        'verify_peer' => false,
                        'allow_self_signed' => true,
                        'security_level' => 1
                    )
                );
                $this->mailer->socket_options = $this->socketOptions();
                $this->mailer->tls = isset($settings['tls']) ? $settings['tls'] : true;
                $this->debug("Got Core_Notify route match - " . print_R($this->mailer, true));

                break;
            }
            
        }
    }

    /**
     * Return hostname(s) to use for sending to the given domain.
     * Checks routing config first; if the domain matches a Core_Notify route (by 'domains'),
     * returns the configured route host. Otherwise runs MX lookup for the domain.
     *
     * @param string $fqdn Recipient email domain (e.g. media-outreach.com)
     * @return array|false Array of hostnames to try (e.g. array('smtp.office365.com') or MX list), or false if none
     */
    static function mxs($fqdn)
    {
        $ff = HTML_FlexyFramework::get();
        if (isset($ff->Pman_Core_NotifySend['host'])) {
            return array($ff->Pman_Core_NotifySend['host']);
        }
        if (!empty($ff->Core_Notify['routes'])) {
            foreach ($ff->Core_Notify['routes'] as $server => $settings) {
                if (!empty($settings['domains']) && in_array($fqdn, $settings['domains'])) {
                    return array($server);
                }
            }
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
        asort($mx_weight, SORT_NUMERIC);
        foreach ($mx_weight as $k => $weight) {
            if (!empty($mx_records[$k])) {
                if (checkdnsrr($mx_records[$k], 'A') || checkdnsrr($mx_records[$k], 'AAAA')) {
                    $mxs[] = $mx_records[$k];
                }
            }
        }
        if (empty($mxs)) {
            return false;
        }
        // If any MX hostname matches a route's mx regex, use that route's server (and its IPs) instead of the MX hostnames
        if (!empty($ff->Core_Notify['routes'])) {
            $pattern_to_server = array();
            foreach ($ff->Core_Notify['routes'] as $server => $settings) {
                if (empty($settings['mx'])) {
                    continue;
                }
                foreach ($settings['mx'] as $mmx) {
                    $pattern_to_server[$mmx] = $server;
                }
            }
            foreach ($mxs as $mx_host) {
                foreach ($pattern_to_server as $mmx => $server) {
                    if (preg_match($mmx, $mx_host)) {
                        return array($server);
                    }
                }
            }
        }
        return $mxs;
    }

    /**
     * Collect MX → IP map and related data (static, data only). Stores result in self::$all_mx_ipv4s, $valid_ips, $use_ipv6, $is_any_ipv4_blacklisted.
     * Caller uses those static properties directly.
     *
     * @param array $mxs MX hostnames
     * @param bool $useIpv6 Whether to prefer IPv6
     * @param int $server_id Current server id (for blacklist)
     * @param object|false $server_ipv6 Core_notify_server_ipv6 or false
     * @param bool $debug If true, echo debug messages
     * @return array Map of IP (or hostname) => domain name
     */
    static function convertMxsToIpMap($mxs, $useIpv6, $server_id, $server_ipv6 = false, $debug = false)
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
                if ($debug) {
                    echo "DNS: Found hosts file override for $mx: $hostname_ip\n";
                }
            }
        }

        self::$all_mx_ipv4s = array_keys($mx_ipv4_map);
        $mx_ip_map = $useIpv6 ? $mx_ipv6_map : $mx_ipv4_map;

        if (empty($mx_ip_map)) {
            foreach ($mxs as $mx) {
                $mx_ip_map[$mx] = $mx;
            }
            if ($debug) {
                echo "DNS: No IP addresses resolved for any MX, using hostnames\n";
            }
            self::$valid_ips = array_keys($mx_ip_map);
            self::$use_ipv6 = $useIpv6;
            self::$is_any_ipv4_blacklisted = false;
            return $mx_ip_map;
        }

        if (!$useIpv6) {
            list($mx_ip_map, $is_blacklisted) = self::filterBlacklistedIps($server_id, $mx_ip_map, $debug);
            self::$valid_ips = array_keys($mx_ip_map);
            self::$use_ipv6 = $useIpv6;
            self::$is_any_ipv4_blacklisted = $is_blacklisted;
            return $mx_ip_map;
        }

        $mx_ip_map = self::filterIpv6ByReversePtr($server_ipv6, $mx_ip_map, $debug);
        if (empty($mx_ip_map)) {
            if ($debug) {
                echo "DNS: No IPv6 addresses resolved, fallback to use IPv4 addresses\n";
            }
            self::$use_ipv6 = false;
            list($mx_ip_map, $is_blacklisted) = self::filterBlacklistedIps($server_id, $mx_ipv4_map, $debug);
            self::$valid_ips = array_keys($mx_ip_map);
            self::$is_any_ipv4_blacklisted = $is_blacklisted;
            return $mx_ip_map;
        }
        self::$valid_ips = array_keys($mx_ip_map);
        self::$use_ipv6 = $useIpv6;
        self::$is_any_ipv4_blacklisted = false;
        return $mx_ip_map;
    }

    /**
     * Filter IP map by blacklisted IPs for server (static, data only).
     *
     * @param int $server_id
     * @param array $mx_ip_map Map to filter (ip => mx)
     * @param bool $debug
     * @return array [filtered_map, is_any_blacklisted]
     */
    private static function filterBlacklistedIps($server_id, $mx_ip_map, $debug = false)
    {
        $bl = DB_DataObject::factory('core_notify_blacklist');
        $bl->server_id = $server_id;
        $bl->whereAdd('ip != 0x0');
        $bl->selectAdd();
        $bl->selectAdd('INET6_NTOA(ip) as ip_str');
        $blacklistedIps = $bl->fetchAll('ip_str');
        $is_any_blacklisted = false;
        foreach ($mx_ip_map as $ip => $mx) {
            if (in_array($ip, $blacklistedIps)) {
                if ($debug) {
                    echo "DNS: Blacklisted IP: $ip\n";
                }
                $is_any_blacklisted = true;
                unset($mx_ip_map[$ip]);
            }
        }
        return array($mx_ip_map, $is_any_blacklisted);
    }

    /**
     * Filter IPv6 map by reverse PTR / domain suffix match (static, data only).
     *
     * @param object|false $server_ipv6 Core_notify_server_ipv6 or false
     * @param array $mx_ip_map Map ip => mx
     * @param bool $debug
     * @return array Filtered map
     */
    private static function filterIpv6ByReversePtr($server_ipv6, $mx_ip_map, $debug = false)
    {
        if (empty($server_ipv6) || !empty($server_ipv6->has_reverse_ptr)) {
            return $mx_ip_map;
        }
        $cnsi = DB_DataObject::factory('core_notify_server_ipv6');
        $cnsi->autoJoin();
        $cnsi->ipv6_addr = $server_ipv6->ipv6_addr;
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
                if ($debug) {
                    echo "DNS: Skipping host $mx because no domain mapped to the current server's IPv6 address (" . $server_ipv6->ipv6_addr_str . ") matches the suffix of the mx host\n";
                }
                unset($mx_ip_map[$ip]);
            }
        }
        return $mx_ip_map;
    }

}

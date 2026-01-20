<?php

class Pman_Core_NotifyRouter
{
    var $host;
    var $localhost;
    var $timeout = 15;
    var $socket_options = array();
    var $debug_handler;
    var $dkim = true;

    var $debug = 0;
    var $server_ipv6 = null;
    
    function __construct($debug_handler = null, $debug = 0, $smtp_host, $mx, $server_ipv6)
    {
        $this->debug_handler = $debug_handler;
        $this->debug = $debug;
        $this->server_ipv6 = $server_ipv6;


        $ff = HTML_FlexyFramework::get();

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

        if ($is_ipv6 && !empty($this->server_ipv6)) {
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

        $socket_options = $this->prepareSocketOptionsWithIPv6($base_socket_options, $smtp_host);
        
        // Format IPv6 address with brackets for PEAR Mail compatibility
        $mailer_host = $smtp_host;
        if ($is_ipv6) {
            $mailer_host = '[' . $smtp_host . ']';
        }

        $this->host = $mailer_host;
        $this->localhost = $helo_hostname;

        var_dump($this);
        die('test');
    }
    
    /**
     * Return a Mail_smtp object
     * @return Mail_smtp
     */
    function toMailer()
    {
        return Mail::factory('smtp', array(
            'host'          => $this->host,
            'localhost'     => $this->localhost,
            'timeout'       => $this->timeout,
            'socket_options'=> $this->socket_options,
            'debug'         => 1,
            'debug_handler' => $this->debug_handler,
            'dkim'          => $this->dkim
        ));
    }

    function debug($str)
    {
        if (empty($this->cli_args['debug'])) {
            return;
            
        }
        echo $str . "\n";
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
        $ipv6_addr_str = !empty($this->server_ipv6) ? $this->server_ipv6->ipv6_addr_str : false;
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
}

<?php
/**
 * Table Definition for core_domain
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_domain_DummyReporter
{
    function report($type, $message, $exit = false)
    {
    }
}

class Pman_Core_DataObjects_Core_domain extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */
  
    var $__table = 'core_domain';
    var $id;
    var $domain;
    var $mx_updated;
    var $has_mx;
    var $server_id; // mail_imap_server

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    function loadOrCreate($dom)
    {
        // should we validate domain?
        $dom = preg_replace('/^www./i', '', $dom);
        
        
        static $cache = array();
        if (isset($cache[$dom])) {
            return $cache[$dom];
        }
        
        $cd = DB_DataObject::Factory($this->tableName());
        if ($cd->get('domain', $dom)) {
            $cache[$dom] = $cd;
            return $cd;
        }
        $cd->domain = $dom;
        $cd->insert();
        $cache[$dom] = $cd;
        return $cd;
    }

    /**
     * Get or create domain with validation
     * 
     * @param string $dom domain name
     * @return object|string returns domain object on success, error string if validation fails
     */
    function getOrCreate($dom)
    {
        static $dom_cache = array();
        
        // Normalize domain
        $dom = trim(strtolower($dom));
        $dom = preg_replace('/^www\./i', '', $dom);
        
        if (empty($dom)) {
            return "domain is empty";
        }
         
        // DNS validation - check if domain exists (but not MX)
        
        $needsMxUpdate = false;
        // Get or create domain object
        if (!$this->get('domain', $dom)) {

            if (!dns_get_record($dom, DNS_A + DNS_AAAA + DNS_CNAME + DNS_MX + DNS_NS)) {
                return "Email domain @{$dom} does not exist. The company may not have paid to renew the domain or the company does not exist anymore.";
            }


            $this->domain = $dom;
            $this->has_mx = 0;
            $this->mx_updated = '1000-01-01 00:00:00';
            $this->insert();
            $needsMxUpdate = true;
        } elseif (
            strtotime($this->mx_updated) < strtotime('NOW - 30 day')
        ) {
            $needsMxUpdate = true;
        }
        
        if (!$needsMxUpdate) {
            return $this;
        }
        $old = clone($this);
        $this->has_mx = $this->hasValidMx($dom) ? 1 : 0;
        $this->mx_updated = date('Y-m-d H:i:s');
        if (!$this->has_mx) {
            $this->no_mx_dt = date('Y-m-d H:i:s');
        } else {
            $this->no_mx_dt = '1000-01-01 00:00:00';
        }
        $this->update($old);
        
        
        return $this;
    }
    function server()
    {
        $mid = DB_DataObject::factory('mail_imap_domain');
        if(!$mid->get('domain', $this->domain)) {
            return false;
        }
        return $mid->server();
    }

    function beforeUpdate($old, $q, $roo)
    {
        if(!empty($q['_update_mx'])) {
            $this->updateMx();
            $roo->jok('DONE');
        }

        if(isset($q['is_mx_valid'])) {
            $isMxValid = $this->no_mx_dt == '1000-01-01 00:00:00' ? 1 : 0;

            // update mx manually
            if($q['is_mx_valid'] != $isMxValid) {
                $this->mx_updated = date('Y-m-d H:i:s');
                // invalid to valid
                if($q['is_mx_valid']) {
                    $this->has_ns = 1;
                    $this->no_mx_dt = '1000-01-01 00:00:00';
                }
                // valid to invalid
                else {
                    $this->has_ns = 0;
                    $this->no_mx_dt = date('Y-m-d H:i:s');
                }
            }
        }
    }

    function hasValidMx($domain)
    {
        if(!checkdnsrr($domain, 'MX')) {
            return false;
        }

        $mx_records = array();
        $mx_weight = array();
        if(getmxrr($domain, $mx_records, $mx_weight)) {
            foreach($mx_records as $mx_record) {
                // Skip empty MX records (e.g., null MX per RFC 7505)
                if(empty($mx_record) || $mx_record === '.') {
                    continue;
                }
                if(checkdnsrr($mx_record, 'A') || checkdnsrr($mx_record, 'AAAA')) {
                    return true;
                }
            }
        }

        // MX records exist but none of the mail servers are reachable
        return false;
    }

    function updateMx()
    {
        $old = clone($this);

        $this->has_mx = $this->hasValidMx($this->domain);
        $this->mx_updated = date('Y-m-d H:i:s');
        $this->no_mx_dt = '1000-01-01 00:00:00';
        
        // expired
        if(!$this->has_mx) {
            $this->no_mx_dt = date('Y-m-d H:i:s');
        }
        $this->update($old);
    }

    function toRooSingleArray($authUser, $request)
    {
        $ret = $this->toArray();

        $ret['is_mx_valid'] = $ret['has_mx'] == 0 && $ret['mx_updated'] != '1000-01-01 00:00:00' ? 0 : 1;
        
        return $ret;
    }
    
    function applyFilters($q, $au, $roo)
    {
        if (!empty($q['query']['domain'])) {
            $this->whereAdd("core_domain.domain like '%{$this->escape($q['query']['domain'])}%'");
        }

        if(!empty($q['_status'])) {
            $badCond = "
                (
                    core_domain.has_mx = 0 
                AND 
                    core_domain.mx_updated != '1000-01-01 00:00:00'
                )
            ";

            switch($q['_status']) {
                case 'invalid_mx':
                    $this->whereAdd("{$badCond}");
                    break;
                case 'valid_mx':
                    $this->whereAdd("NOT({$badCond})");
                    break;
            }
        }

        if(!empty($q['_with_reference_count'])) {
            $this->selectAddPersonReferenceCount();
            if(!empty($q['sort']) && $q['sort'] == 'person_reference_count' && !empty($q['dir'])) {
                $dir = $q['dir'] == 'DESC' ? 'DESC' : 'ASC';
                $this->orderBy("{$q['sort']} $dir");
            }
    
            if(!empty($q['_reference_status'])) {
                switch($q['_reference_status']) {
                    case 'with_references':
                        $this->whereAddWithPersonRefernceCount();
                        break;
                    case 'without_reference':
                        $this->whereAddWithoutPersonRefenceCount();
                        break;
                }
            }
        }
    }

    function selectAddPersonReferenceCount()
    {
        $this->selectAdd("0 as person_reference_count");
    }

    function whereAddWithPersonRefernceCount()
    {
        // all domains have no person reference count
        $this->whereAdd("1 = 0");
    }

    function whereAddWithoutPersonRefenceCount()
    {
        // all domains have no person reference count
    }

    /**
     * Check if the domain has an AAAA record
     * 
     * @return bool
     */
    function hasAAAARecord()
    {
        if (empty($this->domain)) {
            return;
        }

        if (getmxrr($this->domain, $mx_records)) {
            // Check if any MX record has AAAA record
            foreach ($mx_records as $mx) {
                $aaaa_records = dns_get_record($mx, DNS_AAAA);
                if (!empty($aaaa_records)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Set up ipv6 for the domain
     * If the domain has an AAAA record, find the smallest unused ipv6 address in the range and set it up
     * 
     * @param string $allocation_reason Reason why IPv6 was allocated (e.g., bounce message, error details)
     * @param array $mxs Array of MX hostnames
     * @return core_notify_server_ipv6|false
     */
    function setUpIpv6($allocation_reason = '', $mxs = array())
    {
        if(!$this->hasAAAARecord()) {
            return false;
        }

        $hasAnyAAAA = false;

        foreach($mxs as $mx) {
            // skip if the mx has no AAAA record
            $aaaa_records = dns_get_record($mx, DNS_AAAA);
            if(empty($aaaa_records)) {
                continue;
            }
            $hasAnyAAAA = true;
            // try to use pre-configured IPv6 addresses
            $cnsi = DB_DataObject::factory('core_notify_server_ipv6');
            if($ipv6 = $cnsi->findOrCreateIpv6ForMx($mx, $this->id, $allocation_reason)) {
                return $ipv6;
            }
        }

        if(!$hasAnyAAAA) {
            return false;
        }

        $server = DB_DataObject::factory('core_notify_server')->findServerWithIpv6();
        if(!$server) {
            return false;
        }

        $ipv6_addr = $server->findSmallestUnusedIpv6();
        if(!$ipv6_addr) {
            return false;
        }

        $cnsi = DB_DataObject::factory('core_notify_server_ipv6');
        $cnsi->domain_id = $this->id;
        $cnsi->ipv6_addr = $ipv6_addr;
        if(!$cnsi->find(true)) {
            $cnsi->allocation_reason = $allocation_reason;
            $cnsi->insert();
        }
        
        // make sure the ipv6_addr_str is available
        $cnsi2 = DB_DataObject::factory('core_notify_server_ipv6');
        $cnsi2->selectAdd("INET6_NTOA(ipv6_addr) as ipv6_addr_str");
        $cnsi2->get($cnsi->id);

        return $cnsi2;
    }

    function createMailer($roo, $mx, $validUser = false, $opts = array())
    {
        $ff = HTML_FlexyFramework::get();

        $socket_options = isset($ff->Mail_Validate['socket_options']) 
            ? $ff->Mail_Validate['socket_options'] 
            : array(
                'ssl' => array(
                    'verify_peer_name' => false,
                    'verify_peer' => false, 
                    'allow_self_signed' => true
                )
        );

        $currentServer = DB_DataObject::Factory('core_notify_server')->getCurrent($roo, true, 'core');
        if (!empty($opts['bind_notify_interface']) && $currentServer->interface == '') {
            $srv = DB_DataObject::factory('core_notify_server');
            $srv->setFrom(array(
                'poolname' => $currentServer->poolname,
                'hostname' => $currentServer->hostname,
                'is_active' => 1,
            ));
            $srv->whereAdd("interface != ''");
            $srv->limit(1);
            if ($srv->find(true)) {
                $currentServer = $srv;
            }
        }

        $ipv6Map = isset($ff->Mail_Validate['ipv6']) ? $ff->Mail_Validate['ipv6'] : array();
        $ipv6Bound = false;

        // current server has ipv6 address
        if(!empty($currentServer->id) && !empty($currentServer->hostname) && !empty($ipv6Map[$currentServer->hostname])) {
            $aaaa_records = dns_get_record($mx, DNS_AAAA);
            // target mx has aaaa record
            if (!empty($aaaa_records)) {
                $socket_options['socket'] = array(
                    'bindto' => '[' . $ipv6Map[$currentServer->hostname] . ']:0'
                );
                $ipv6Bound = true;
            }
        }



        if (!$ipv6Bound && !empty($opts['bind_notify_interface']) && $currentServer->interface != '') {
            $ifaces = net_get_interfaces();

            if (array_key_exists($currentServer->interface, $ifaces)
                && !empty($ifaces[$currentServer->interface]['unicast'][1]['address'])) {
                $ipv4_bind_ip = $ifaces[$currentServer->interface]['unicast'][1]['address'];
                $socket_options['socket'] = array(
                    'bindto' => $ipv4_bind_ip . ':0'
                );
                if (is_object($roo) && method_exists($roo, 'out')) {
                    $roo->out('error_log', "ValidateEmail retry: IPv4 bind {$currentServer->interface} ({$ipv4_bind_ip})");
                }
            }
        }

        $mailer = Mail::factory('smtp', array(
            'host'    => $mx,
            'localhost' => $ff->Mail['helo'],
            'timeout' => 90,
            'socket_options' => $socket_options,
            'test' => true
        ));

        if ($validUser === false || empty($ff->Mail_Validate) || empty($ff->Mail_Validate['routes'])) {
            return $mailer;
        }

         

        foreach ($ff->Mail_Validate['routes'] as $server => $settings) {
            $matches = in_array($this->domain, $settings['domains']);
            if (!$matches && !empty($settings['mx'])) {
                foreach($settings['mx'] as $mmx) {
                    if (preg_match($mmx, $mx)) {
                        $matches = true;
                        break;
                    }
                }
            }
            if (!$matches) {
                continue;
            }

            if (!empty($settings['auth']) && $settings['auth'] == 'XOAUTH2') {
                $s = $validUser->server();
                $mailer->host = $s->smtp_host;
                $mailer->port = $s->smtp_port;
                $mailer->username = $validUser->email;
                $mailer->password = $s->requestToken($validUser);
                $mailer->auth = 'XOAUTH2';
                $mailer->tls = true;
                return $mailer;
            } 
            
            $mailer->host = $server;
            $mailer->auth = isset($settings['auth']) ? $settings['auth'] : true;
            $mailer->username = $settings['username'];
            $mailer->password = $settings['password'];
            if (isset($settings['port'])) {
                $mailer->port = $settings['port'];
            }
            $mailer->socket_options = isset($settings['socket_options']) 
                ? $settings['socket_options'] 
                : $mailer->socket_options;
            $mailer->tls = isset($settings['tls']) ? $settings['tls'] : true;
        
            return $mailer;
        }

        return $mailer;
    }

    /**
     * MX hostnames in priority order for SMTP probe, or empty if domain cannot receive mail
     * (same preconditions as validateEmail before the MX loop).
     *
     * @return array list of MX host strings
     */
    function mxHostsForValidation()
    {
        if (!(($this->mx_updated && strtotime($this->mx_updated) >= strtotime('NOW - 30 day')) 
            ? $this->has_mx : $this->hasValidMx($this->domain))) {
            return array();
        }

        $mx_records = array();
        $mx_weight = array();
        if (!getmxrr($this->domain, $mx_records, $mx_weight)) {
            return array();
        }
        asort($mx_weight, SORT_NUMERIC);

        $mxs = array();
        foreach ($mx_weight as $k => $weight) {
            if (!empty($mx_records[$k])) {
                $mxs[] = $mx_records[$k];
            }
        }

        return $mxs;
    }

    /**
     * SMTP probe for an email on this domain.
     *
     * @param object $roo page or CLI context
     * @param string $email normalized address
     * @param callable|false $reporter optional callback(type, message, exit) for SSE worker NDJSON
     * @return true|string true on success, error message otherwise (reporter may exit on failure)
     */
    function validateEmail($roo, $email, $reporter = false)
    {
        $reporter = $reporter === false ? function () {} : $reporter;

        $dom = $this->domain;
        if (empty($dom)) {
            throw new Exception('Domain not set on core_domain object');
        }

        $mxs = $this->mxHostsForValidation();
        if (empty($mxs)) {
            $msg = "{$email} {$dom} is not a valid domain (cant deliver email to it)";
            $reporter('email_fail', $msg, true);
            return $msg;
        }

        require_once 'Mail.php';
        $ff = HTML_FlexyFramework::get();
        if (!isset($ff->Mail['helo'])) {
            $reporter('error_log', 'config Mail[helo] is not set', true);
            throw new Exception('config Mail[helo] is not set');
        }

        $validUser = false;
        if (!empty($ff->Mail_Validate['routes'])) {
            $authUser = false;
            if (is_object($roo) && !empty($roo->authUser)) {
                $authUser = $roo->authUser;
            } elseif (!empty($ff->page) && method_exists($ff->page, 'getAuthUser')) {
                $authUser = $ff->page->getAuthUser();
            }
            if ($authUser) {
                $fromUser = DB_DataObject::factory('mail_imap_user');
                if ($fromUser->get('email', $authUser->email)) {
                    $validUser = $fromUser->validateAsOAuth();
                }
            }
            if ($validUser === false && !empty($ff->Mail_Validate['test_user'])) {
                $fromUser = DB_DataObject::factory('mail_imap_user');
                if ($fromUser->get('email', $ff->Mail_Validate['test_user'])) {
                    $validUser = $fromUser->validateAsOAuth();
                }
            }
        }

        PEAR::setErrorHandling(PEAR_ERROR_RETURN);

        $mxOk = false;
        $lastErr = '';

        for ($pass = 0; $pass < 2 && !$mxOk; $pass++) {
            $fromAddr = 'newswire-reply@media-outreach.com';
            if ($pass > 0) {
                $fromAddr = $dom . '@media-outreach.com';
                $reporter('error_log', "ValidateEmail retry: From {$fromAddr}", false);
            }

            foreach ($mxs as $mx) {
                $mailer = $this->createMailer($roo, $mx, $validUser, array(
                    'bind_notify_interface' => $pass > 0,
                ));
                if ($mailer === false) {
                    continue;
                }

                $res = $mailer->send($email, array(
                    'To' => $email,
                    'From' => '"Media OutReach Newswire" <' . $fromAddr . '>',
                ), '');

                if (!is_object($res)) {
                    $mxOk = true;
                    break;
                }

                $errorMessage = $res->getMessage();

                if ($res->code == 421) {
                    if ($dom != 'yahoo.com') {
                        $reporter(
                            'error_log',
                            "WARNING: Email test failed for {$email} - returned code {$res->code} (Service unavailable), however we accepted it as valid. Error: {$errorMessage}",
                            false
                        );
                    }
                    $mxOk = true;
                    break;
                }

                if ($res->code == 451) {
                    $reporter(
                        'error_log',
                        "WARNING: Email test failed for {$email} - returned code {$res->code} (Greylisting), however we accepted it as valid. Error: {$errorMessage}",
                        false
                    );
                    $mxOk = true;
                    break;
                }

                if (in_array($res->code, array(452, 555)) && preg_match('/out of storage/i', $errorMessage)) {
                    $msg = 'The email address is over quota - which probably means its a dead email address - '
                        . 'we do not add these as we would just get rejections - you should contact this user before adding '
                        . 'and see if they have another email address';
                    $reporter('email_fail', $msg, true);
                    return $msg;
                }

                if ($res->code == 550 && preg_match('/spamhaus/i', $errorMessage)) {
                    $mxOk = true;
                    break;
                }
                if ($res->code == 554 && preg_match('/spam/i', $errorMessage)) {
                    $mxOk = true;
                    break;
                }
                if ($res->code == 554 && preg_match('/Recipient address rejected: Access denied/i', $errorMessage)) {
                    $reporter(
                        'error_log',
                        "WARNING: Email test failed for {$email} - returned code {$res->code} (Access denied), however we accepted it as valid. Error: {$errorMessage}",
                        false
                    );
                    $mxOk = true;
                    break;
                }

                if (
                    $res->code == 553 && preg_match('/User unknown/i', $errorMessage)
                    || $res->code == 550 && preg_match('/does not exist|no mailbox here|User unknown|user not exist/i', $errorMessage)
                ) {
                    $msg = 'Email ' . $email . ' does not work - we checked it - nothing can be delivered to them.';
                    $reporter('email_fail', $msg, true);
                    return $msg;
                }

                $reporter(
                    'error_log',
                    "SMTP Validate Rejected Email $mx {$res->code} Email: {$email} - Error: " . $errorMessage,
                    false
                );
                $lastErr = $res->getMessage();
            }
        }

        if (!$mxOk) {
            $msg = 'cannot send to ' . $email . ($lastErr ? " ({$lastErr})" : ' (connection failed to all MX servers)');
            $reporter('email_fail', $msg, true);
            return $msg;
        }

        return true;
    }
}

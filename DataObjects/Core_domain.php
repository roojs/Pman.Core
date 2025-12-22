<?php
/**
 * Table Definition for core_domain
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

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

            if (!checkdnsrr($dom, 'ANY')) {
                return "Domain {$dom} does not exist (no dns records found)";
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
     * @return core_notify_server_ipv6|false
     */
    function setUpIpv6()
    {
        if(!$this->hasAAAARecord()) {
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
        $cnsi->server_id = $server->id;
        $cnsi->domain_id = $this->id;
        $cnsi->ipv6_addr = $ipv6_addr;
        if(!$cnsi->find(true)) {
            $cnsi->insert();
        }

        return $cnsi;
    }

    /**
     * validate email
     * 
     * @param object $roo Roo object
     * @param string $email email address to validate
     * @return bool|string true on success, error message string on failure
     * @throws Exception if configuration is invalid
     */
    function validateEmail($roo, $email)
    {
        $dom = $this->domain;
        if (empty($dom)) {
            throw new Exception("Domain not set on core_domain object");
        }

        // Check MX records - use cache if updated within last 30 days
        if (!(($this->mx_updated && strtotime($this->mx_updated) >= strtotime('NOW - 30 day')) ? $this->has_mx : $this->hasValidMx($dom))) {
            return "{$email} {$dom} is not a valid domain (cant deliver email to it)";
        }

        require_once 'Mail.php';
        $ff = HTML_FlexyFramework::get();
        
        if (!isset($ff->Mail['helo'])) {
            throw new Exception("config Mail[helo] is not set");
        }

        // Get MX records for the domain
        $mx_records = array();
        $mx_weight = array();
        getmxrr($dom, $mx_records, $mx_weight);
        asort($mx_weight, SORT_NUMERIC);
        
        $mxs = array();
        foreach($mx_weight as $k => $weight) {
            if (!empty($mx_records[$k])) {
                $mxs[] = $mx_records[$k];
            }
        }

        if (empty($mxs)) {
            return "cannot send to {$email} (no MX records found)";
        }

        PEAR::setErrorHandling(PEAR_ERROR_RETURN);

        $validUser = false;
        if (!empty($ff->Mail_Validate['routes'])) {
            $authUser = $ff->page->getAuthUser();
            $fromUser = DB_DataObject::factory('mail_imap_user');
            if ($fromUser->get('email', $authUser->email)) {
                $validUser = $fromUser->validateAsOAuth();
            }
            
            if ($validUser === false && !empty($ff->Mail_Validate['test_user'])) {
                $fromUser = DB_DataObject::factory('mail_imap_user');
                if ($fromUser->get('email', $ff->Mail_Validate['test_user'])) {
                    $validUser = $fromUser->validateAsOAuth();
                }
            }
        }

        $lastError = '';
        foreach($mxs as $mx) {
            $mailer = $this->createMailer($roo, $mx, $validUser);
            if ($mailer === false) {
                continue;
            }

            $res = $mailer->send($email, array(
                'To'   => $email,  
                'From'   => '"Media OutReach Newswire" <newswire-reply@media-outreach.com>'
            ), '');

            if (!is_object($res)) {
                return true; // Success
            }
            
            // Check for known false positives BEFORE logging
            // These are temporary errors or false positives we can't fix, so treat as valid
            $errorMessage = $res->getMessage();
            
            // Check for SMTP error 421 (Service unavailable - server busy)
            // This is a temporary error we can't fix, so treat it as a valid check
            if ($res->code == 421) {
                $roo->errorlog(
                    "WARNING: Email test failed for {$email} - returned code {$res->code} (Service unavailable), however we accepted it as valid"
                );
                return true; // Treat 421 as success
            }
            
            // Check for SMTP error 451 (Greylisting - temporary failure)
            // This is a temporary error indicating greylisting, so treat it as a valid check
            if ($res->code == 451) {
                $roo->errorlog(
                    "WARNING: Email test failed for {$email} - returned code {$res->code} (Greylisting), however we accepted it as valid"
                );
                return true; // Treat 451 as success
            }
            
            // Check for SMTP error 550 with Spamhaus failure
            // Spamhaus failures are false positives we can't fix, so treat as valid
            // Also check for Mimecast which uses Spamhaus (zen.mimecast.org)
            if ($res->code == 550 && (
                preg_match('/spamhaus/i', $errorMessage)  
            )) {
                $roo->errorlog(
                    "WARNING: Email test failed for {$email} - returned code {$res->code} and contained Spamhaus, 
						however we accepted it as valid"
                );
                return true; // Treat 550 Spamhaus/Mimecast as success
            }
            if ($res->code == 554 && (
                preg_match('/spam/i', $errorMessage)  
            )) {
                $roo->errorlog(
                    "WARNING: Email test failed for {$email} - returned code {$res->code} and contained Spam, 
						however we accepted it as valid"
                );
                return true; // Treat 550 Spamhaus/Mimecast as success
            }
            // Only log errors that aren't known false positives
            // PEAR_Error objects have both ->message property and getMessage() method
            // Using getMessage() method is the standard approach
            $roo->errorlog(
                "SMTP Validate Rejected Email {$res->code} Email: {$email} - Error: " . $errorMessage
            );
              
            
            $lastError = $res->getMessage();
        }

        return "cannot send to {$email}" . ($lastError ? " ({$lastError})" : " (connection failed to all MX servers)");
    }

    function createMailer($roo, $mx, $validUser = false)
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
        $ipv6Map = isset($ff->Mail_Validate['ipv6']) ? $ff->Mail_Validate['ipv6'] : array();

        // current server has ipv6 address
        if(!empty($currentServer->id) && !empty($currentServer->hostname) && !empty($ipv6Map[$currentServer->hostname])) {
            $aaaa_records = dns_get_record($mx, DNS_AAAA);
            // target mx has aaaa record
            if (!empty($aaaa_records)) {
                $socket_options['socket'] = array(
                    'bindto' => '[' . $ipv6Map[$currentServer->hostname] . ']:0'
                ); 
            }
        }
        
        $mailer = Mail::factory('smtp', array(
            'host'    => $mx,
            'localhost' => $ff->Mail['helo'],
            'timeout' => 15,
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

    function shouldEmailBeDeferred($email, $patterns)
    {

    }

    /**
     * Get domain id from email
     * 
     * @param string $email email address
     * @return int domain id (0 if not found)
     */
    function getDomainIdFromEmail($email)
    {
        $parts = explode('@', $email);
        $dom = array_pop($parts);
        $domain = DB_DataObject::factory('core_domain');
        if($domain->get('domain', $dom)) {
            return $domain->id;
        }
        return 0;
    }

    /**
     * Get domain ids by pattern
     * 
     * @param string $pattern pattern to search for
     * @return array array of domain ids
     */
    function getDomainIdsFromPattern($pattern)
    {
        $domain_ids = array();
        $domains = DB_DataObject::factory('core_domain');
        $domains->whereAdd("domain LIKE '%{$pattern}%'");
        $domains->find();
        while($domains->fetch()) {
            $domain_ids[] = $domains->id;
        }
        return $domain_ids;
    }
}

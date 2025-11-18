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
     * validate email
     * 
     * @param string $email email address to validate
     * @return bool|string true on success, error message string on failure
     * @throws Exception if configuration is invalid
     */
    function validateEmail($email)
    {
        $dom = $this->domain;
        if (empty($dom)) {
            throw new Exception("Domain not set on core_domain object");
        }

        // Check MX records - use cache if updated within last 30 days
        if ($this->mx_updated && strtotime($this->mx_updated) >= strtotime('NOW - 30 day')) {
            $hasMX = $this->has_mx;
        } else {
            $hasMX = $this->hasValidMx($dom);
        }

        if (!$hasMX) {
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
        $defaultSocketOptions = array(
            'ssl' => array(
                'verify_peer_name' => false,
                'verify_peer' => false, 
                'allow_self_signed' => true
            )
        );

        $lastError = '';
        foreach($mxs as $mx) {
            $mailer = $this->createMailer($mx, $ff, $defaultSocketOptions);
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
            
            $lastError = $res->message;
        }

        $errorMsg = "cannot send to {$email}";
        if ($lastError) {
            $errorMsg .= " ({$lastError})";
        } else {
            $errorMsg .= " (connection failed to all MX servers)";
        }
        return $errorMsg;
    }

    function createMailer($mx, $ff, $defaultSocketOptions)
    {
        $socketOptions = isset($ff->Mail['socket_options']) ? $ff->Mail['socket_options'] : $defaultSocketOptions;
        
        $mailer = Mail::factory('smtp', array(
            'host'    => $mx,
            'localhost' => $ff->Mail['helo'],
            'timeout' => 15,
            'socket_options' => $socketOptions,
            'test' => true
        ));

        if (empty($ff->Mail_Validate) || empty($ff->Mail_Validate['routes'])) {
            return $mailer;
        }

        $dom = $this->domain;
        foreach ($ff->Mail_Validate['routes'] as $server => $settings) {
            if (!$this->matchesRoute($dom, $mx, $settings)) {
                continue;
            }

            $host = $server;
            if (!empty($settings['auth']) && $settings['auth'] == 'XOAUTH2') {
                $oauthResult = $this->configureOAuth($settings);
                if ($oauthResult === false) {
                    continue;
                }
                $host = $oauthResult['host'];
                $settings = array_merge($settings, $oauthResult);
            }

            $mailer->host = $host;
            $mailer->auth = isset($settings['auth']) ? $settings['auth'] : true;
            $mailer->username = $settings['username'];
            $mailer->password = $settings['password'];
            if (isset($settings['port'])) {
                $mailer->port = $settings['port'];
            }
            $mailer->socket_options = isset($settings['socket_options']) ? $settings['socket_options'] : $defaultSocketOptions;
            $mailer->tls = isset($settings['tls']) ? $settings['tls'] : true;

            return $mailer;
        }

        return $mailer;
    }

    function matchesRoute($dom, $mx, $settings)
    {
        if (in_array($dom, $settings['domains'])) {
            return true;
        }

        if (empty($settings['mx'])) {
            return false;
        }

        foreach($settings['mx'] as $mmx) {
            if (preg_match($mmx, $mx)) {
                return true;
            }
        }

        return false;
    }

    function configureOAuth($settings)
    {
        $from = 'newswire-reply@media-outreach.com';
        preg_match('/<([^>]+)>|^([^<>]+)$/', $from, $matches);
        $from = end($matches);

        $fromUser = DB_DataObject::factory('mail_imap_user');
        $fromUser->setFrom(array('is_active' => 1));
        if (!$fromUser->get('email', $from)) {
            return false;
        }

        if ($fromUser->is_reply_to_only) {
            $sendAsUser = DB_DataObject::factory('mail_imap_user');
            if (!$sendAsUser->get($fromUser->send_as_id)) {
                return false;
            }
            $fromUser = $sendAsUser;
        }

        $s = $fromUser->server();
        if ($s === false || $s->is_valid() !== true || !$s->is_oauth) {
            return false;
        }

        if (empty($fromUser->token) || empty($fromUser->id_token) || empty($fromUser->code)) {
            return false;
        }

        return array(
            'host' => $s->smtp_host,
            'port' => $s->smtp_port,
            'username' => $fromUser->email,
            'password' => $s->requestToken($fromUser)
        );
    }
}

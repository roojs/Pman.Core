<?php
/**
 * Internal HTTP worker: one email SMTP validation (POST from Core/ValidateEmail).
 * POST: email, auth_user_id.
 *
 * Ops: php-fpm request_terminate_timeout and nginx fastcgi_read_timeout should be >= 90s
 * for this route (see ValidateEmail parent).
 */

require_once 'Pman.php';

class Pman_Core_Process_ValidateEmailWorker extends Pman
{
    function getAuth()
    {
        if (empty($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->jerr('access denied');
        }

        if (empty($_SERVER['REMOTE_ADDR'])) {
            $this->jerr('access denied');
        }

        $remote = $_SERVER['REMOTE_ADDR'];
        if ($remote == '127.0.0.1' || $remote == '::1') {
            // return true;
        }

        $ns = DB_DataObject::factory('core_notify_server');
        $ns->poolname = 'core';
        foreach ($ns->availableServers() as $s) {
            if (empty($s->helo)) {
                continue;
            }
            $ip = gethostbyname($s->helo);
            var_dump('ip: ' . $ip . ' helo: ' . $s->helo . ' remote: ' . $remote);
            if ($ip != $s->helo && $ip == $remote) {
                die('a');
                // return true;
            }
        }
        $this->jerr('access denied');
    }

    function get($request = '', $opts = array(), $isRedirect = false)
    {
        $this->jerr('Invalid get');
    }

    function post($base = '')
    {
        set_time_limit(90);

        $au = DB_DataObject::factory('core_person');
        if($au->get($_POST['auth_user_id'])) {
            $this->authUser = $au;
        }

        $dar = explode('@', $_POST['email']);
        $dom = strtolower(array_pop($dar));
        $dar[] = $dom;
        $emailNorm = implode('@', $dar);

        $cd = DB_DataObject::factory('core_domain');
        $cdResult = $cd->getOrCreate($dom);
        if (!is_object($cdResult)) {
            $this->jdata(array(
                'type' => 'email_fail',
                'message' => is_string($cdResult) ? $cdResult : 'Invalid domain',
            ));
        }

        $result = false;
        for ($pass = 0; $pass < 2; $pass++) {
            $result = $cd->validateEmail($this, $emailNorm, $pass);
            if ($result === true) {
                break;
            }
            if ($result !== false) {
                break;
            }
        }
        if (is_string($result)) {
            $this->jdata(array(
                'type' => 'email_fail',
                'message' => $result,
            ));
        }

        $this->jdata(array(
            'type' => 'email_ok',
            'domain_id' => $cd->id,
            'token' => md5($emailNorm . $cd->id),
        ));
    }
}

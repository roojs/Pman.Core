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
        return true;
    }

    function get($request = '', $opts = array(), $isRedirect = false)
    {
        $this->jerr('Invalid get');
    }

    function post($base = '')
    {
        $this->lol();
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

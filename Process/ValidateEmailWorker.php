<?php
/**
 * Internal HTTP worker: one email SMTP validation (POST from Core/ValidateEmail).
 * Loopback only. POST: email, auth_user_id.
 *
 * Ops: php-fpm request_terminate_timeout and nginx fastcgi_read_timeout should be >= 90s
 * for this route (see ValidateEmail parent).
 */

require_once 'Pman.php';

class Pman_Core_Process_ValidateEmailWorker extends Pman
{
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->cli)) {
            return true;
        }
        return $this->authRequired();
    }

    function get($request = '', $opts = array(), $isRedirect = false)
    {
        $this->jerr('Invalid get');
    }

    function post($base = '')
    {
        set_time_limit(90);
        header('Content-Type: application/json; charset=utf-8');

        $authUserId = isset($_POST['auth_user_id']) ? $_POST['auth_user_id'] : '';
        $this->respondJson($this->runJob($email, $authUserId));
    }

    function respondJson($row)
    {
        echo json_encode($row, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * @return array{type: string, domain_id?: int, token?: string, message?: string}
     */
    function runJob($email, $authUserId)
    {
        $ff = HTML_FlexyFramework::get();
        if (!isset($ff->Mail['helo'])) {
            $this->errorlog('config Mail[helo] is not set');
            return array(
                'type' => 'email_fail',
                'message' => 'Mail configuration error',
            );
        }

        if ($authUserId !== '' && $authUserId !== null) {
            $au = DB_DataObject::factory('core_person');
            if ($au->get((int) $authUserId)) {
                $this->authUser = $au;
            }
        }

        $dar = explode('@', $email);
        $dom = strtolower(array_pop($dar));
        $dar[] = $dom;
        $emailNorm = implode('@', $dar);

        $cd = DB_DataObject::factory('core_domain');
        $cdResult = $cd->getOrCreate($dom);
        if (!is_object($cdResult)) {
            return array(
                'type' => 'email_fail',
                'message' => is_string($cdResult) ? $cdResult : 'Invalid domain',
            );
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
            return array(
                'type' => 'email_fail',
                'message' => $result,
            );
        }

        $domainId = (int) $cd->id;
        return array(
            'type' => 'email_ok',
            'domain_id' => $domainId,
            'token' => md5($emailNorm . $domainId),
        );
    }
}

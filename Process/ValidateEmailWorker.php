<?php
/**
 * CLI: one email SMTP validation (NDJSON on stdout).
 *   php /path/to/press.local.php Core/Process/ValidateEmailWorker -f /path/to/job.json
 * (job JSON: email, auth_user_id).
 */

require_once 'Pman.php';

class Pman_Core_Process_ValidateEmailWorker extends Pman
{
    static $cli_desc = 'Validate one email via SMTP (used by Core/ValidateEmail SSE parent).';

    static $cli_opts = array(
        'file' => array(
            'desc' => 'Job JSON file (email, auth_user_id)',
            'short' => 'f',
            'min' => 1,
            'max' => 1,
        ),
    );

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
        $jobPath = !empty($opts['file']) ? $opts['file'] : '';
        if ($jobPath === '') {
            echo "Usage: ... Core/Process/ValidateEmailWorker -f /path/to/job.json\n";
            exit(1);
        }

        $raw = @file_get_contents($jobPath);
        if ($raw === false || $raw === '') {
            echo 'Cannot read job file: ' . $jobPath . "\n";
            exit(1);
        }

        $job = json_decode($raw, true);
        if (!is_array($job) || empty($job['email'])) {
            echo "Invalid job JSON (need email)\n";
            exit(1);
        }

        if (!empty($job['auth_user_id'])) {
            $au = DB_DataObject::factory('core_person');
            if ($au->get((int) $job['auth_user_id'])) {
                $this->authUser = $au;
            }
        }

        $dar = explode('@', $job['email']);
        $dom = strtolower(array_pop($dar));
        $dar[] = $dom;
        $emailNorm = implode('@', $dar);

        $cd = DB_DataObject::factory('core_domain');
        $cdResult = $cd->getOrCreate($dom);
        if (!is_object($cdResult)) {
            $this->debuglog('email_fail', is_string($cdResult) ? $cdResult : 'Invalid domain');
            exit(1);
        }

        // true = RCPT accepted; false = soft/inconclusive (retry pass 1); string = hard fail
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
            $this->debuglog('email_fail', $result);
            exit(1);
        }

        echo json_encode(array(
            'type' => 'email_ok',
            'domain_id' => (int) $cd->id,
            'token' => md5($emailNorm . (int) $cd->id),
        ), JSON_UNESCAPED_UNICODE) . "\n";
        fflush(STDOUT);
        exit(0);
    }

    function errorlog($msg)
    {
        parent::errorlog($msg);
        $this->debuglog('error_log', $msg);
    }

    function debuglog($type, $message)
    {
        echo json_encode(array(
            'type' => $type,
            'message' => $message,
        ), JSON_UNESCAPED_UNICODE) . "\n";
        fflush(STDOUT);
    }
}

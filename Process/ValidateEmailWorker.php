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
            $this->out('error_log', 'Usage: ... Core/Process/ValidateEmailWorker -f /path/to/job.json', true);
        }

        $raw = @file_get_contents($jobPath);
        if ($raw === false || $raw === '') {
            $this->out('error_log', 'Cannot read job file', true);
        }

        $job = json_decode($raw, true);
        if (!is_array($job) || empty($job['email'])) {
            $this->out('error_log', 'Invalid job JSON (need email)', true);
        }

        if (!empty($job['auth_user_id'])) {
            $au = DB_DataObject::factory('core_person');
            if ($au->get((int) $job['auth_user_id'])) {
                $this->authUser = $au;
            }
        }
        $ff = HTML_FlexyFramework::get();
        $ff->page = $this;

        $dar = explode('@', $job['email']);
        $dom = strtolower(array_pop($dar));
        $dar[] = $dom;
        $emailNorm = implode('@', $dar);

        $cd = DB_DataObject::factory('core_domain');
        $cdResult = $cd->getOrCreate($dom);
        if (!is_object($cdResult)) {
            $this->out('email_fail', is_string($cdResult) ? $cdResult : 'Invalid domain', true);
        }

        $worker = $this;
        $cd->validateEmail($this, $emailNorm, 
            function ($type, $message, $exit = false) use ($worker) {
            $worker->out($type, $message, $exit);
        });

        echo json_encode(array(
            'type' => 'email_ok',
            'domain_id' => (int) $cd->id,
            'token' => md5($emailNorm . (int) $cd->id),
        ), JSON_UNESCAPED_UNICODE) . "\n";
        fflush(STDOUT);
        exit(0);
    }

    function out($type, $message, $exit = false)
    {
        $res = array(
            'type' => $type,
            'message' => $message,
        );
        if ($type == 'error_log' && $exit) {
            $res['isHardFailure'] = true;
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE) . "\n";
        fflush(STDOUT);
        if ($exit) {
            exit(1);
        }
    }
}

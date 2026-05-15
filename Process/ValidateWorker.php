<?php
/**
 * CLI: one email SMTP validation (NDJSON on stdout).
 *   php /path/to/press.local.php Core/Process/ValidateWorker -f /path/to/job.json
 * (job JSON: email, field, auth_user_id).
 */
class Pman_Core_Process_ValidateWorker extends Pman
{
    static $cli_desc = 'Validate one email via SMTP (used by Core/ValidateEmail SSE parent).';

    static $cli_opts = array(
        'file' => array(
            'desc' => 'Job JSON file (email, field, auth_user_id)',
            'short' => 'f',
            'min' => 1,
            'max' => 1,
        ),
    );

    var $stepOf = 5;

    var $phaseStep = 0;

    var $field = '';

    var $emailNorm = '';

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
            fwrite(STDERR, "Usage: ... Core/Process/ValidateWorker -f /path/to/job.json\n");
            exit(1);
        }

        $raw = @file_get_contents($jobPath);
        if ($raw === false || $raw === '') {
            fwrite(STDERR, "Cannot read job file\n");
            exit(1);
        }

        $job = json_decode($raw, true);
        if (!is_array($job) || empty($job['email']) || empty($job['field'])) {
            fwrite(STDERR, "Invalid job JSON (need email, field)\n");
            exit(1);
        }

        if (!empty($job['auth_user_id'])) {
            $au = DB_DataObject::factory('core_person');
            if ($au->get((int) $job['auth_user_id'])) {
                $this->authUser = $au;
            }
        }

        $this->field = $job['field'];
        $dar = explode('@', $job['email']);
        $dom = strtolower(array_pop($dar));
        $dar[] = $dom;
        $this->emailNorm = implode('@', $dar);

        $cd = DB_DataObject::factory('core_domain');
        $cdResult = $cd->getOrCreate($dom);
        if (!is_object($cdResult)) {
            $this->vewOut(array(
                'type' => 'email_fail',
                'field' => $this->field,
                'email' => $this->emailNorm,
                'message' => is_string($cdResult) ? $cdResult : 'Invalid domain',
            ));
            exit(1);
        }

        $mxs = $cd->mxHostsForValidation();
        if (empty($mxs)) {
            $this->vewOut(array(
                'type' => 'email_fail',
                'field' => $this->field,
                'email' => $this->emailNorm,
                'message' => "{$this->emailNorm} {$dom} is not a valid domain (cant deliver email to it)",
            ));
            exit(1);
        }

        $this->phaseStep = 0;
        $this->emitStep('connect', 'Connecting');

        $mxOk = false;
        $lastErr = '';

        require_once 'Mail.php';
        $ffw = HTML_FlexyFramework::get();
        if (!isset($ffw->Mail['helo'])) {
            $this->vewOut(array(
                'type' => 'email_fail',
                'field' => $this->field,
                'email' => $this->emailNorm,
                'message' => 'config Mail[helo] is not set',
            ));
            exit(1);
        }

        foreach ($mxs as $mx) {
            $mailer = $cd->createMailer($this, $mx, false);
            if ($mailer === false) {
                continue;
            }
            $mailer->debug = true;
            $mailer->debug_handler = array($this, 'mailerDebugLine');

            PEAR::setErrorHandling(PEAR_ERROR_RETURN);
            $res = $mailer->send($this->emailNorm, array(
                'To' => $this->emailNorm,
                'From' => '"Media OutReach Newswire" <newswire-reply@media-outreach.com>',
            ), '');

            if (!is_object($res)) {
                $mxOk = true;
                break;
            }

            $errorMessage = $res->getMessage();
            if ($res->code == 421 || $res->code == 451) {
                $mxOk = true;
                break;
            }
            if (in_array($res->code, array(452, 555)) && preg_match('/out of storage/i', $errorMessage)) {
                $this->vewOut(array(
                    'type' => 'email_fail',
                    'field' => $this->field,
                    'email' => $this->emailNorm,
                    'message' => 'The email address is over quota - which probably means its a dead email address - '
                        . 'we do not add these as we would just get rejections - you should contact this user before adding '
                        . 'and see if they have another email address',
                ));
                exit(1);
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
                $mxOk = true;
                break;
            }
            if (
                $res->code == 553 && preg_match('/User unknown/i', $errorMessage)
                || $res->code == 550 && preg_match('/does not exist|no mailbox here|User unknown/i', $errorMessage)
            ) {
                $this->vewOut(array(
                    'type' => 'email_fail',
                    'field' => $this->field,
                    'email' => $this->emailNorm,
                    'message' => 'This is email <B>does not work</B> - we checked it - nothing can be delivered to them.',
                ));
                exit(1);
            }
            $lastErr = $res->getMessage();
        }

        if (!$mxOk) {
            $this->vewOut(array(
                'type' => 'email_fail',
                'field' => $this->field,
                'email' => $this->emailNorm,
                'message' => 'cannot send to ' . $this->emailNorm . ($lastErr ? " ({$lastErr})" : ' (connection failed to all MX servers)'),
            ));
            exit(1);
        }

        $token = md5($this->emailNorm . (int) $cd->id);
        $this->vewOut(array(
            'type' => 'email_ok',
            'field' => $this->field,
            'email' => $this->emailNorm,
            'domain_id' => (int) $cd->id,
            'token' => $token,
        ));

        exit(0);
    }

    function vewOut($ar)
    {
        echo json_encode($ar, JSON_UNESCAPED_UNICODE) . "\n";
        fflush(STDOUT);
    }

    function emitStep($phase, $label)
    {
        $this->phaseStep++;
        $this->vewOut(array(
            'type' => 'step',
            'field' => $this->field,
            'email' => $this->emailNorm,
            'phase' => $phase,
            'step' => $this->phaseStep,
            'of' => $this->stepOf,
            'message' => $label,
        ));
    }

    function mailerDebugLine($smtp, $message)
    {
        $msg = (string) $message;
        if (strpos($msg, 'Send:') !== 0) {
            return;
        }
        if (preg_match('/^Send:\\s*AUTH\\b/i', $msg)) {
            $this->emitStep('auth', 'Authenticating');
            return;
        }
        if (stripos($msg, 'Send: STARTTLS') === 0) {
            $this->emitStep('starttls', 'Upgrading to a TLS connection');
            return;
        }
        if (stripos($msg, 'Send: MAIL FROM:') === 0) {
            $this->emitStep('mailFrom', 'Setting email sender');
            return;
        }
        if (stripos($msg, 'Send: RCPT TO:') === 0) {
            $this->emitStep('rcptTo', 'Setting email recipients');
            return;
        }
        if (stripos($msg, 'Send: EHLO') === 0 || stripos($msg, 'Send: HELO') === 0) {
            $this->emitStep('ehlo', 'EHLO');
        }
    }
}

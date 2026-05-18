<?php
/**
 * CLI: one email SMTP validation (NDJSON on stdout).
 *   php /path/to/press.local.php Core/Process/ValidateEmailWorker -f /path/to/job.json
 * (job JSON: email, field, auth_user_id).
 */

require_once 'Pman.php';

class Pman_Core_Process_ValidateEmailWorker extends Pman
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

    var $stepOf = 6;

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
        // if ($jobPath === '') {
            $this->systemError('Usage: ... Core/Process/ValidateEmailWorker -f /path/to/job.json');
        // }

        $raw = @file_get_contents($jobPath);
        if ($raw === false || $raw === '') {
            $this->systemError('Cannot read job file');
        }

        $job = json_decode($raw, true);
        if (!is_array($job) || empty($job['email']) || empty($job['field'])) {
            $this->systemError('Invalid job JSON (need email, field)');
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
            // Check for SMTP error 421 (Service unavailable - server busy)
            // This is a temporary error we can't fix, so treat it as a valid check
            if ($res->code == 421) {
                // no error log for 421 on yahoo.com as its a known issue
                if($dom != 'yahoo.com') {
                    $this->vewOut(array(
                        'type' => 'error_log',
                        'message' => "WARNING: Email test failed for {$this->emailNorm} - returned code {$res->code} (Service unavailable), however we accepted it as valid. Error: {$errorMessage}"
                    ));
                }
                $mxOk = true; // Treat 421 as success
                break;
            }

            // Check for SMTP error 451 (Greylisting - temporary failure)
            // This is a temporary error indicating greylisting, so treat it as a valid check
            if ($res->code == 451) {
                $this->vewOut(array(
                    'type' => 'error_log',
                    'message' => "WARNING: Email test failed for {$this->emailNorm} - returned code {$res->code} (Greylisting), however we accepted it as valid. Error: {$errorMessage}"
                ));
                $mxOk = true;
                break;
            }

            // Check for SMTP error 452 (out of storage space)
            if (in_array($res->code, array(452, 555)) && preg_match('/out of storage/i', $errorMessage)) {
                // Don't need to log error for out of storage space
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

            // Check for SMTP error 550 with Spamhaus failure
            // Spamhaus failures are false positives we can't fix, so treat as valid
            // Also check for Mimecast which uses Spamhaus (zen.mimecast.org)
            if ($res->code == 550 && preg_match('/spamhaus/i', $errorMessage)) {
                // Don't need to log error for spamhaus failures
                $mxOk = true;
                break;
            }
            if ($res->code == 554 && preg_match('/spam/i', $errorMessage)) {
                // Don't need to log error for spam failures
                $mxOk = true;
                break;
            }
            if ($res->code == 554 && preg_match('/Recipient address rejected: Access denied/i', $errorMessage)) {
                $this->vewOut(array(
                    'type' => 'error_log',
                    'message' => "WARNING: Email test failed for {$this->emailNorm} - returned code {$res->code} (Access denied), however we accepted it as valid. Error: {$errorMessage}"
                ));
                $mxOk = true;
                break;
            }

            // We don't need to log these errors and don't need to show these errors to the user
            if (
                $res->code == 553 && preg_match('/User unknown/i', $errorMessage)
                || $res->code == 550 && preg_match('/does not exist|no mailbox here|User unknown|user not exist/i', $errorMessage)
            ) {
                $this->vewOut(array(
                    'type' => 'email_fail',
                    'field' => $this->field,
                    'email' => $this->emailNorm,
                    'message' => 'This is email <B>does not work</B> - we checked it - nothing can be delivered to them.',
                ));
                exit(1);
            }

            // Only log errors that aren't known false positives
            // PEAR_Error objects have both ->message property and getMessage() method
            // Using getMessage() method is the standard approach
            $this->vewOut(array(
                'type' => 'error_log',
                'message' => "SMTP Validate Rejected Email $mx {$res->code} Email: {$this->emailNorm} - Error: " . $errorMessage
            ));
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

    function systemError($msg) {
        echo json_encode(array(
            'type' => 'error_log',
            'message' => $msg,
            'isHardFailure' => true,
        ), JSON_UNESCAPED_UNICODE) . "\n";
        fflush(STDOUT);
        exit(1);
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

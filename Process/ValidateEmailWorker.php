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

        $dar = explode('@', $job['email']);
        $dom = strtolower(array_pop($dar));
        $dar[] = $dom;
        $this->emailNorm = implode('@', $dar);

        $cd = DB_DataObject::factory('core_domain');
        $cdResult = $cd->getOrCreate($dom);
        if (!is_object($cdResult)) {
            $this->out('email_fail', is_string($cdResult) ? $cdResult : 'Invalid domain', true);
        }

        $mxs = $cd->mxHostsForValidation();
        if (empty($mxs)) {
            $this->out('email_fail', "{$this->emailNorm} {$dom} is not a valid domain (cant deliver email to it)", true);
        }

        $mxOk = false;
        $lastErr = '';

        require_once 'Mail.php';
        $ffw = HTML_FlexyFramework::get();
        if (!isset($ffw->Mail['helo'])) {
            $this->out('error_log', 'config Mail[helo] is not set', true);
        }

        $validUser = false;
        if (!empty($ffw->Mail_Validate['routes'])) {
            $authUser = $this->authUser;
            if ($authUser) {
                $fromUser = DB_DataObject::factory('mail_imap_user');
                if ($fromUser->get('email', $authUser->email)) {
                    $validUser = $fromUser->validateAsOAuth();
                }
            }

            if ($validUser === false && !empty($ffw->Mail_Validate['test_user'])) {
                $fromUser = DB_DataObject::factory('mail_imap_user');
                if ($fromUser->get('email', $ffw->Mail_Validate['test_user'])) {
                    $validUser = $fromUser->validateAsOAuth();
                }
            }
        }
        
        
        for($pass = 0; $pass < 2 && !$mxOk; $pass++) {
            foreach ($mxs as $mx) {
                $mailer = $cd->createMailer($this, $mx, $validUser);
                if ($mailer === false) {
                    continue;
                }

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
                        $this->out('error_log', "WARNING: Email test failed for {$this->emailNorm} - returned code {$res->code} (Service unavailable), however we accepted it as valid. Error: {$errorMessage}");
                    }
                    $mxOk = true; // Treat 421 as success
                    break;
                }

                // Check for SMTP error 451 (Greylisting - temporary failure)
                // This is a temporary error indicating greylisting, so treat it as a valid check
                if ($res->code == 451) {
                    $this->out('error_log', "WARNING: Email test failed for {$this->emailNorm} - returned code {$res->code} (Greylisting), however we accepted it as valid. Error: {$errorMessage}");
                    $mxOk = true;
                    break;
                }

                // Check for SMTP error 452 (out of storage space)
                if (in_array($res->code, array(452, 555)) && preg_match('/out of storage/i', $errorMessage)) {
                    // Don't need to log error for out of storage space
                    $this->out('email_fail', 'The email address is over quota - which probably means its a dead email address - '
                            . 'we do not add these as we would just get rejections - you should contact this user before adding '
                            . 'and see if they have another email address', true);
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
                    $this->out('error_log', "WARNING: Email test failed for {$this->emailNorm} - returned code {$res->code} (Access denied), however we accepted it as valid. Error: {$errorMessage}");
                    $mxOk = true;
                    break;
                }

                // We don't need to log these errors and don't need to show these errors to the user
                if (
                    $res->code == 553 && preg_match('/User unknown/i', $errorMessage)
                    || $res->code == 550 && preg_match('/does not exist|no mailbox here|User unknown|user not exist/i', $errorMessage)
                ) {
                    $this->out('email_fail', 'Email ' . $this->emailNorm . ' does not work - we checked it - nothing can be delivered to them.', true);
                }

                // Only log errors that aren't known false positives
                // PEAR_Error objects have both ->message property and getMessage() method
                // Using getMessage() method is the standard approach
                $this->out('error_log', "SMTP Validate Rejected Email $mx {$res->code} Email: {$this->emailNorm} - Error: " . $errorMessage);
                $lastErr = $res->getMessage();
            }
        }

        // fails after multiple passes
        if(!$mxOk) {
            $this->out('email_fail', 'cannot send to ' . $this->emailNorm . ($lastErr ? " ({$lastErr})" : ' (connection failed to all MX servers)'), true);
        }

        $token = md5($this->emailNorm . (int) $cd->id);
        echo json_encode(array(
            'type' => 'email_ok',
            'domain_id' => (int) $cd->id,
            'token' => $token,
        ), JSON_UNESCAPED_UNICODE) . "\n";
        fflush(STDOUT);

        exit(0);
    }

    /**
     * Output a message to the standard output.
     *
     * @param string $type The type of message (error_log, email_fail).
     * @param string $message The message to output.
     * @param bool $exit Whether to exit the script after outputting the message.
     */
    function out($type, $message, $exit = false) 
    {
        $res = array(
            'type' => $type,
            'message' => $message
        );

        if($type == 'error_log' && $exit) {
            $res['isHardFailure'] = true;
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE) . "\n";
        fflush(STDOUT);

        if($exit) {
            exit(1);
        }
    }
}

<?php

require_once 'Pman.php';

/**
 * SSE multi-email SMTP validation (one child process per address).
 * URL: Core/ValidateEmail (POST, FormData; use Roo.form.Action.Sse).
 */
class Pman_Core_ValidateEmail extends Pman
{
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->cli)) {
            return true;
        }
        return $this->authRequired();
    }

    function sendSSE($event, $data)
    {
        echo "\n";
        echo "event: {$event}\n";
        echo "data: " . json_encode($data) . "\n";
        if (ob_get_level()) {
            ob_flush();
        }
        flush();

        if ($event === 'error') {
            exit;
        }
    }

    /**
     * Show an error message
     *
     * @param string $message The error message
     */
    function error($message)
    {
        $this->sendSSE('error', array(
            'success' => false,
            'errorMsg' => $message
        ));
    }

    function post($base = '')
    {
        set_time_limit(0);

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        while (ob_get_level()) {
            ob_end_flush();
        }

        $au = $this->getAuthUser();
        if (!$au) {
            $this->error('Not authenticated');
        }

        $jobsRaw = isset($_POST['validate_email_jobs']) ? $_POST['validate_email_jobs'] : '';
        $jobs = json_decode($jobsRaw, true);
        if (!is_array($jobs) || empty($jobs)) {
            $this->errorlog('Missing or invalid validate_email_jobs JSON');
            $this->error('An error occurred, please contact the website owner.');
        }

        $entryScript = realpath($_SERVER['SCRIPT_FILENAME']);
        if ($entryScript === false || !is_file($entryScript)) {
            $this->errorlog('Cannot resolve PHP entry script for worker (SCRIPT_FILENAME)');
            $this->error('An error occurred, please contact the website owner.');
        }
        $childCwd = dirname($entryScript);

        $total = count($jobs);
        $results = array();
        $phpBin = defined('PHP_BINARY') && PHP_BINARY ? PHP_BINARY : 'php';

        foreach ($jobs as $idx => $jobRow) {
            if (empty($jobRow['field']) || !isset($jobRow['email'])) {
                $this->errorlog('Each job needs field and email');
                $this->error('An error occurred, please contact the website owner.');
            }

            $field = $jobRow['field'];
            $email = $jobRow['email'];
            if ($email === '' || $email === null) {
                continue;
            }

            $jobFile = tempnam(sys_get_temp_dir(), 'vew_');
            if ($jobFile === false) {
                $this->errorlog('Cannot create temp file');
                $this->error('An error occurred, please contact the website owner.');
            }

            $payload = array(
                'email' => $email,
                'field' => $field,
                'auth_user_id' => (int) $au->id,
            );
            file_put_contents($jobFile, json_encode($payload, JSON_UNESCAPED_UNICODE));
            @chmod($jobFile, 0600);

            $cmd = escapeshellarg($phpBin) . ' '
                . escapeshellarg($entryScript) . ' '
                . escapeshellarg('Core/Process/ValidateEmailWorker')
                . ' -f ' . escapeshellarg($jobFile);
            $descriptors = array(
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w'),
            );
            $proc = proc_open($cmd, $descriptors, $pipes, $childCwd);
            if (!is_resource($proc)) {
                @unlink($jobFile);
                $this->errorlog('Could not start validation subprocess');
                $this->error('An error occurred, please contact the website owner.');
            }
            fclose($pipes[0]);
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);

            $bufOut = '';
            $bufErr = '';
            $childStarted = microtime(true);
            $lastHeartbeat = microtime(true);
            $heartbeatEvery = 1.0;
            $childTimeout = 90.0;
            $jobError = false;
            $okRow = null;

            $this->sendSSE('progress', array(
                'total' => 1, //$total * $childTimeout,
                'progress' => $idx / $total * 100,
                'message' => 'Validating email (' . $email . ') - ' . round($childTimeout) . ' seconds left',
            ));

            while (true) {
                $st = proc_get_status($proc);
                if (empty($st['running'])) {
                    break;
                }
                if (microtime(true) - $childStarted > $childTimeout) {
                    proc_terminate($proc, 9);
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    proc_close($proc);
                    @unlink($jobFile);
                    $this->error('Validation timed out for ' . $email, true);
                }

                $r = array($pipes[1], $pipes[2]);
                $w = null;
                $e = null;
                $tv = 1;
                $n = @stream_select($r, $w, $e, $tv);
                if ($n > 0) {
                    foreach ($r as $pipe) {
                        $chunk = fread($pipe, 8192);
                        if ($chunk !== false && $chunk !== '') {
                            if ($pipe === $pipes[1]) {
                                $bufOut .= $chunk;
                            }
                            if ($pipe === $pipes[2]) {
                                $bufErr .= $chunk;
                            }
                        }
                    }
                    while (($p = strpos($bufOut, "\n")) !== false) {
                        $line = trim(substr($bufOut, 0, $p));
                        $bufOut = substr($bufOut, $p + 1);
                        if ($line === '') {
                            continue;
                        }
                        $row = json_decode($line, true);
                        if (!is_array($row)) {
                            $jobError = 'Invalid JSON from worker: ' . substr($line, 0, 200);
                            break;
                        }
                        if (!empty($row['type']) && $row['type'] === 'error_log') {
                            $this->errorlog($row['message']);
                            if(!empty($row['isHardFailure'])) {
                                $jobError = 'An error occurred, please contact the website owner.';
                                break;
                            }
                            continue;
                        }
                        if (!empty($row['type']) && $row['type'] === 'email_fail') {
                            $jobError = !empty($row['message']) ? $row['message'] : 'Email validation failed';
                            break;
                        }
                        if (!empty($row['type']) && $row['type'] === 'email_ok') {
                            $okRow = $row;
                            continue;
                        }
                    }

                    if($jobError) {
                        break;
                    }
                }

                if (microtime(true) - $lastHeartbeat >= $heartbeatEvery) {
                    $lastHeartbeat = microtime(true);
                    $this->sendSSE('progress', array(
                        'total' => 1, // $total * $childTimeout,
                        'progress' => (microtime(true) - $childStarted + $idx * $childTimeout) / ($total * $childTimeout) * 100,
                        'message' => 'Validating email (' . $email . ') - ' . round($childTimeout - (microtime(true) - $childStarted)) ." seconds left",
                    ));
                }
            }

            stream_set_blocking($pipes[1], true);
            stream_set_blocking($pipes[2], true);
            $bufOut .= stream_get_contents($pipes[1]);
            $bufErr .= stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exitCode = proc_close($proc);
            @unlink($jobFile);

            if(empty($jobError) && $okRow === null) {
                $jobError = 'No success result from worker for ' . $email;
            }

            if($jobError) {
                $results[$field] = array(
                    'email' => $email,
                    'error' => $jobError,
                );
                continue;
            }

            $results[$field] = array(
                'email' => $okRow['email'],
                'domain_id' => $okRow['domain_id'],
                'token' => $okRow['token'],
            );
        }

        $this->sendSSE('progress', array(
            'total' => 1, // $total,
            'progress' => 100,
            'message' => 'Validation complete',
        ));

        $this->sendSSE('complete', array(
            'success' => true,
            'data' => $results,
        ));
        exit;
    }
}

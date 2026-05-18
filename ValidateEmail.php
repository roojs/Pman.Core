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
     * @param bool $allowRetry Whether to allow the user to retry (maps to allowRetry 1/0 for Roo.form.Action.Sse)
     */
    function error($message, $allowRetry = true)
    {
        $this->sendSSE('error', array(
            'success' => false,
            'errorMsg' => $message,
            'allowRetry' => $allowRetry ? 1 : 0,
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
            $this->error('Not authenticated', false);
        }

        $jobsRaw = isset($_POST['validate_email_jobs']) ? $_POST['validate_email_jobs'] : '';
        $jobs = json_decode($jobsRaw, true);
        if (!is_array($jobs) || empty($jobs)) {
            $this->error('Missing or invalid validate_email_jobs JSON', true);
        }

        $entryScript = realpath($_SERVER['SCRIPT_FILENAME']);
        if ($entryScript === false || !is_file($entryScript)) {
            $this->error('Cannot resolve PHP entry script for worker (SCRIPT_FILENAME)', false);
        }
        $childCwd = dirname($entryScript);

        $total = count($jobs);
        $results = array();
        $phpBin = defined('PHP_BINARY') && PHP_BINARY ? PHP_BINARY : 'php';

        foreach ($jobs as $idx => $jobRow) {
            if (empty($jobRow['field']) || !isset($jobRow['email'])) {
                $this->error('Each job needs field and email', true);
            }

            $field = $jobRow['field'];
            $email = $jobRow['email'];
            if ($email === '' || $email === null) {
                continue;
            }

            $jobFile = tempnam(sys_get_temp_dir(), 'vew_');
            if ($jobFile === false) {
                $this->error('Cannot create temp file', true);
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
                $this->error('Could not start validation subprocess', true);
            }
            fclose($pipes[0]);
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);

            $bufOut = '';
            $bufErr = '';
            $childStarted = microtime();
            $lastHeartbeat = microtime();
            $heartbeatEvery = 1;
            $childTimeout = 90;
            $jobError = false;
            $okRow = null;

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
                    $this->error('Validation timed out for ' . $field, true);
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
                            $jobError = array(
                                'message' => 'Invalid JSON from worker: ' . substr($line, 0, 200),
                                'allowRetry' => false,
                            );
                            break;
                        }
                        if (!empty($row['type']) && $row['type'] === 'error_log') {
                            $this->errorlog($row['message']);
                            if(!empty($row['isHardFailure'])) {
                                $jobError = array(
                                    'message' => 'An error occurred, please contact the website owner.',
                                    'allowRetry' => false,
                                );
                                break;
                            }
                            continue;
                        }
                        if (!empty($row['type']) && $row['type'] === 'email_fail') {
                            $jobError = array(
                                'message' => !empty($row['message']) ? $row['message'] : 'Email validation failed',
                                'allowRetry' => true,
                            );
                            break;
                        }
                        if (!empty($row['type']) && $row['type'] === 'email_ok') {
                            $okRow = $row;
                            continue;
                        }
                        // if (empty($row['type']) || $row['type'] !== 'step') {
                        //     continue;
                        // }
                        // $baseProg = ($idx / $total) * 100;
                        // $sub = 0;
                        // if (!empty($row['step']) && !empty($row['of'])) {
                        //     $sub = (($row['step'] - 1) / $row['of']) * (100 / $total);
                        // }
                        // $this->sendSSE('progress', array(
                        //     'total' => $total * 6,
                        //     'progress' => $baseProg + $sub,
                        //     'message' => !empty($row['message']) ? $row['message'] : json_encode($row),
                        //     'email' => $email,
                        //     'worker' => $row,
                        // ));
                    }

                    if($jobError) {
                        break;
                    }
                }

                if (microtime() - $lastHeartbeat >= $heartbeatEvery) {
                    $lastHeartbeat = microtime();
                    $timeElapsed = microtime() - $childStarted;
                    $this->sendSSE('progress', array(
                        'total' => $total * $childTimeout,
                        'progress' => ($timeElapsed + $idx * $childTimeout) / ($total * $childTimeout) * 100,
                        'message' => 'Validating ' . $field . '…' . ($childTimeout - $timeElapsed) ." seconds left",
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



            if($jobError) {
                $this->error($jobError['message'], $jobError['allowRetry']);
            }

            if ($exitCode !== 0) {
                $this->error(
                    trim($bufErr) !== '' ? trim($bufErr) : ('Validation failed for ' . $field . ' (exit ' . $exitCode . ')'),
                    true
                );
            }

            if ($okRow === null) {
                foreach (array_filter(array_map('trim', explode("\n", trim($bufOut)))) as $ln) {
                    $decoded = json_decode($ln, true);
                    if (is_array($decoded) && !empty($decoded['type']) && $decoded['type'] === 'email_ok') {
                        $okRow = $decoded;
                    }
                }
            }
            if ($okRow === null) {
                $this->error('No success result from worker for ' . $field, true);
            }

            $results[$field] = array(
                'email' => $okRow['email'],
                'domain_id' => $okRow['domain_id'],
                'token' => $okRow['token'],
            );
        }

        $this->sendSSE('progress', array(
            'total' => $total * 6,
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

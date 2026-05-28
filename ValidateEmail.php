<?php

require_once 'Pman.php';

/**
 * SSE multi-email SMTP validation (one loopback HTTP worker request per address).
 * URL: Core/ValidateEmail (POST, FormData; use Roo.form.Action.Sse).
 *
 * Ops: parent SSE may run up to N*90s; Cloudflare/proxy read timeout should allow that.
 * Child worker (Core/Process/ValidateEmailWorker): php-fpm request_terminate_timeout and
 * nginx fastcgi_read_timeout should be >= 90s for loopback requests.
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
        echo "\n"
            . "event: {$event}\n"
            . 'data: ' . json_encode($data) . "\n";
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

    /**
     * POST one email to loopback worker; SSE progress while waiting (max $childTimeout s).
     *
     * @return array{ok: ?array, error: string}
     */
    function runWorkerHttp($workerUrl, $email, $authUserId, $childTimeout, $heartbeatCb)
    {
        $ch = curl_init($workerUrl);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(array(
                'email' => $email,
                'auth_user_id' => $authUserId,
            )),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => (int) ceil($childTimeout + 5),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
        ));

        $mh = curl_multi_init();
        curl_multi_add_handle($mh, $ch);

        $childStarted = microtime(true);
        $running = true;
        $body = '';
        $httpCode = 0;

        do {
            $status = curl_multi_exec($mh, $running);

            if (microtime(true) - $childStarted > $childTimeout) {
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
                curl_multi_close($mh);
                return array('ok' => null, 'error' => 'timeout');
            }

            $heartbeatCb(microtime(true) - $childStarted);

            if ($running) {
                curl_multi_select($mh, 1.0);
            }
        } while ($running && $status === CURLM_OK);

        $body = curl_multi_getcontent($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
        curl_multi_close($mh);

        $res = json_decode(trim($body), true);
        if(
            !is_array($res) || 
            empty($res['data']) || 
            empty($res['data']['type']) || 
            !in_array($res['data']['type'], array('email_fail', 'email_ok'))
        ) {
            return array('ok' => null, 'error' => 'Invalid response from worker: ' . $body);
        }
        $row = $res['data'];

        if ($row['type'] === 'email_fail') {
            return array(
                'ok' => null,
                'error' => !empty($row['message']) ? $row['message'] : 'Validation failed',
            );
        }

        return array('ok' => $row, 'error' => '');
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
        $jobs = json_decode($_POST['validate_email_jobs'], true);

        $ff = HTML_FlexyFramework::get();
        if (!isset($ff->Mail['helo'])) {
            $this->errorlog('config Mail[helo] is not set');
            $this->error('An error occurred, please contact the website owner.');
            
        }

        if (empty($ff->Pman['local_base_url'])) {
            $this->errorlog('Pman[local_base_url] is not set');
            $this->error('An error occurred, please contact the website owner.');
        }

        $total = count($jobs);
        $results = array();
        $childTimeout = 90.0;
        $heartbeatEvery = 1.0;

        foreach ($jobs as $idx => $jobRow) {
            $field = $jobRow['field'];
            $email = $jobRow['email'];
            $lastHeartbeat = 0.0;
            $jobError = '';
            $okRow = null;

            $this->sendSSE('progress', array(
                'total' => $total * $childTimeout,
                'progress' => $idx / $total * 100,
                'message' => 'Validating email (' . $email . ') - ' . round($childTimeout) . ' seconds left',
            ));

            $workerResult = $this->runWorkerHttp(
                $ff->Pman['local_base_url'] . '/Core/Process/ValidateEmailWorker',
                $email,
                $au->id,
                $childTimeout,
                function ($elapsed) use (
                    &$lastHeartbeat,
                    $heartbeatEvery,
                    $childTimeout,
                    $total,
                    $idx,
                    $email
                ) {
                    if (microtime(true) - $lastHeartbeat < $heartbeatEvery) {
                        return;
                    }
                    $lastHeartbeat = microtime(true);
                    $this->sendSSE('progress', array(
                        'total' => $total * $childTimeout,
                        'progress' => ($elapsed + $idx * $childTimeout) / ($total * $childTimeout) * 100,
                        'message' => 'Validating email (' . $email . ') - ' . round($childTimeout - $elapsed) . ' seconds left',
                    ));
                }
            );

            if ($workerResult['error'] === 'timeout') {
                $this->errorlog('ValidateEmail timed out for ' . $email);
                $this->error('Validation timed out for ' . $email);
            }

            if ($workerResult['ok'] !== null) {
                $okRow = $workerResult['ok'];
            } elseif ($workerResult['error'] !== '') {
                $jobError = $workerResult['error'];
            } else {
                $jobError = 'An error occurred. Please contact the website admin.';
            }

            $results[$field] = array(
                'email' => $email,
                'error' => $jobError,
                'domain_id' => $jobError !== '' ? '' : $okRow['domain_id'],
                'token' => $jobError !== '' ? '' : $okRow['token'],
            );
        }

        $this->sendSSE('progress', array(
            'total' => $total * $childTimeout,
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

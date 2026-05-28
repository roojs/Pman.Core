<?php

require_once 'Pman/Core/Sse.php';

/**
 * SSE multi-email SMTP validation (one loopback HTTP worker request per address).
 * URL: Core/ValidateEmail (POST, FormData; use Roo.form.Action.Sse).
 *
 * Ops: parent SSE may run up to N*90s; Cloudflare/proxy read timeout should allow that.
 * Child worker (Core/Process/ValidateEmailWorker): php-fpm request_terminate_timeout and
 * nginx fastcgi_read_timeout should be >= 90s for loopback requests.
 */
class Pman_Core_ValidateEmail extends Pman_Core_Sse
{
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->cli)) {
            return true;
        }
        return $this->authRequired();
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
                return array('ok' => null, 'error' => 'Validation timed out: ' . $email);
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
            $this->sseError('Invalid response from worker: ' . $body);
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
        $au = $this->getAuthUser();
        $jobs = json_decode($_POST['validate_email_jobs'], true);
        $total = count($jobs);
        $childTimeout = 1.0;
        $heartbeatEvery = 1.0;

        $this->startSse(array(
            'progressTotal' => $total * $childTimeout
        ));

        $ff = HTML_FlexyFramework::get();
        if (!isset($ff->Mail['helo'])) {
            $this->sseError('config Mail[helo] is not set');
        }

        $results = array();

        foreach ($jobs as $idx => $jobRow) {
            $field = $jobRow['field'];
            $email = $jobRow['email'];
            $lastHeartbeat = 0.0;
            $jobError = '';
            $okRow = null;

            $this->sseProgress($idx / $total * 100, 'Validating email (' . $email . ') - ' . round($childTimeout) . ' seconds left');

            $workerResult = $this->runWorkerHttp(
                'http://localhost' . $this->baseURL . '/Core/Process/ValidateEmailWorker',
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
                    $this->sseProgress(($elapsed + $idx * $childTimeout) / ($total * $childTimeout) * 100, 'Validating email (' . $email . ') - ' . round($childTimeout - $elapsed) . ' seconds left');
                }
            );

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

        $this->sseProgress(100, 'Validation complete');
        $this->sseComplete($results);
        exit;
    }
}

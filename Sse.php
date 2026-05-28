<?php

require_once 'Pman.php';

class Pman_Core_Sse extends Pman
{
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

    function startSse()
    {
        set_time_limit(0);

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        while (ob_get_level()) {
            ob_end_flush();
        }
    }

    function error($message)
    {
        $this->sendSSE('error', array(
            'success' => false,
            'errorMsg' => $message
        ));
    }
}

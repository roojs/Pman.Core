<?php

require_once 'Pman.php';

class Pman_Core_Sse extends Pman
{
    var $sse = false;
    var $progressTotal = 100;

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

    function startSse($options = array())
    {
        $this->sse = true;
        foreach($options as $key => $value) {
            if(!property_exists($this, $key)) {
                continue;
            }
            $this->{$key} = $value;
        }
        set_time_limit(0);

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        while (ob_get_level()) {
            ob_end_flush();
        }
    }

    function sseProgress($progress, $message = '')
    {
        $this->sendSSE('progress', array(
            'total' => $this->progressTotal,
            'progress' => $progress,
            'message' => $message
        ));
    }

    function sseError($message, $options = array())
    {
        $this->errorlog($message);
        $this->sendSSE('error', array_merge(array(
            'success' => false,
            'errorMsg' => $message
        ), $options));
    }

    function sseComplete($data = array())
    {
        $this->sendSSE('complete', $data);
    }
}

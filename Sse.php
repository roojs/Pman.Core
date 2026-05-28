<?php

require_once 'Pman.php';

class Pman_Core_Sse extends Pman
{
    var $sse = false;
    var $progressTotal = 100;

    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->cli)) {
            return true;
        }
        $this->authRequired();

        set_time_limit(0);

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        while (ob_get_level()) {
            ob_end_flush();
        }

        return true;
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

    function sseProgress($progress, $message = '')
    {
        $this->sendSSE('progress', array(
            'total' => $this->progressTotal,
            'progress' => $progress,
            'message' => $message
        ));
    }

    // reuse the name jerr even if the logic is different
    // easier to read and understand
    function jerr($str, $errors=array(), $content_type = false)
    {
        $this->errorlog($str);
        $this->sendSSE('error', array(
            'success' => false,
            'errorMsg' => $str
        ));
    }

    function sseComplete($data = array())
    {
        $this->sendSSE('complete', array(
            'success' => true,
            'data' => $data
        ));
    }
}

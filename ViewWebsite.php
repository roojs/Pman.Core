<?php

require_once 'Pman.php';

class Pman_Core_ViewWebsite extends Pman 
{
    function getAuth() 
    {
        $au = $this->getAuthUser();
        if (!$au) {
             $this->jerror("LOGIN-NOAUTH", "Not authenticated", array('authFailure' => true));
        }
        $this->authUser = $au;
        
        return true; 
    }
    
    function get($base='', $opts = array())
    {
        die('invalid get');
    }

    function post($base = '')
    {
        if(empty($_REQUEST['url'])) {
            die('missing url');
        }
        $ch = curl_init($_REQUEST['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true); // Include headers in output
        $response = curl_exec($ch);

        // Separate headers and body
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        // Extract Content-Type from headers
        $contentType = 'text/plain'; // Default fallback
        if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $headers, $matches)) {
            $contentType = trim($matches[1]);
        }

        $ret = $body;

        // Set Content-Type header for browser
        header("Content-Type: $contentType");
        curl_close($ch);
        if($contentType == 'application/rss+xml') {
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($body);

            $ret = $dom->saveXML();
        }
        var_dump($ret);
        die('test');
        echo $ret;
        exit;
    }
}
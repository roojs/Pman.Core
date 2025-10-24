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
        $this->post($base);
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
        
        // Pretty print XML for various XML content types
        if (preg_match('/\bxml\b/i', $contentType) || $this->isXMLContent($body)) {
            // $ret = $this->prettyPrintXML($body);
        }
        
        echo $ret;
        exit;
    }
    
    /**
     * Check if content is XML by examining the content itself
     */
    private function isXMLContent($content) 
    {
        // Trim whitespace and check for XML declaration or root element
        $trimmed = trim($content);
        return (strpos($trimmed, '<?xml') === 0) || 
               (strpos($trimmed, '<') === 0 && strpos($trimmed, '>') !== false);
    }
    
    /**
     * Pretty print XML content
     */
    private function prettyPrintXML($xmlContent) 
    {
        try {
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xmlContent);
            return $dom->saveXML();
        } catch (Exception $e) {
            // If XML parsing fails, return original content
            return $xmlContent;
        }
    }
}
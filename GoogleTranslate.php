<?php

/**
 * Description of GoogleTranslate
 *
 * @author chris
 */

require_once 'Pman.php';
class Pman_Core_GoogleTranslate extends Pman
{
    //put your code here
    function getAuth()
    {
        
        $au = $this->getAuthUser();
        if (!$au) {
            $this->jerrAuth("only authenticated users");
        }
        
        $this->authUser = $au;
    }
    function get() {
        // for testing..
        return $this->post();
    }
    
    function post()
    {
        $pc = HTML_FlexyFramework::get()->Pman_Core;
        if (empty($pc['googlekey'])) {
            $this->jerr("Google API Key not configured");
        }
        
        $param = array(
            'key' => $pc['googlekey'],
            'q' => rawurlencode($_REQUEST['text']),
            'source' => $_REQUEST['src'],
            'target' => $_REQUEST['dest']
        );
        
        $url = 'https://www.googleapis.com/language/translate/v2?'.http_build_query($param);

        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handle);                 
        $responseDecoded = json_decode($response, true);
        curl_close($handle);
    
        
        $this->jdata($responseDecoded);
        
    }
    
    
}

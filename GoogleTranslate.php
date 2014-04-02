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
        if (!strlen(trim($_REQUEST['text']))) {
            $this->jok(array("translatedText" =>""));
        }
        $param = array(
            'key' => $pc['googlekey'],
            'q' =>  $_REQUEST['text'],
            'source' => $_REQUEST['src'],
            'target' => $_REQUEST['dest'],
            'format' => 'text',
        );
        
        $url = 'https://www.googleapis.com/language/translate/v2';

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_POST, count($param));
        curl_setopt($handle, CURLOPT_POSTFIELDS, $param);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: GET'));

        $response = curl_exec($handle);

//        $responseDecoded = json_decode($response);
        curl_close($handle);
        
        header("content-type: text/json");
        echo $response;
        exit;
        
    }
    
    
}

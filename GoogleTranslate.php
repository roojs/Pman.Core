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
        
        $url = 'https://www.googleapis.com/language/translate/v2';
//
//        $handle = curl_init();
//        curl_setopt($handle, CURLOPT_URL, $url);
//        curl_setopt($handle, CURLOPT_POST, count($param));
//        curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($param));
//        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($handle, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: GET'));
//
//        $response = curl_exec($handle);
//        
        $responseDecoded = json_decode('{"success":true,"total":1,"data":{"data":{"translations":[{"translatedText":"Alessi% E6% 96% B0% E5% 93% 81% E7% 99% BC% E5% B8% 83% E3% 80% 80% E6% 99% 82% E5% B0% 9A% E5% AF% A6% E7% 94% A8% E5% 85% BC% E5% 82% 99"}]}}}');
//        curl_close($handle);
        print_r($responseDecoded->data->data->translations[0]->translatedText);
        $this->jok($responseDecoded->data->data->translations[0]);
        
    }
    
    
}

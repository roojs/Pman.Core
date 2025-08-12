
<?php

require_once 'Pman.php';

class Pman_Core_ViewWebsite extends Pman 
{
    function getAuth() 
    {
        return true;
    }
    
    function get($base='', $opts = array())
    {
        $ch = curl_init('https://api.xmware.com/statenewsnetwork/latest/?feed=MOUT&key=mediaKjWOlqkkWo&page=1');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $this->jdata($response);
        exit;
    }

    function post($base = '')
    {
        die('invalid post');
    }
}
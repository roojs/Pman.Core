
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

    }

    function post($base = '')
    {
        die('invalid post');
    }
}
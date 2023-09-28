<?php
require_once 'Pman.php';

class Pman_Core_NotifySend extends Pman
{
    function getAuth()
    {
        parent::getAuth();
        
        if (!$this->getAuthUser()) {  
            $this->jerr("Not authenticated", array('authFailure' => true));
        }
        
        return true;
    }

    function get($base, $opts=array())
    {
        die('test');
    }

    function post($base) {
        die('Invalid post');
    }
}
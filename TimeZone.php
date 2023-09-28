<?php
require_once 'Pman.php';

class Pman_Core_NotifySend extends Pman
{
    function getAuth()
    {
        parent::getAuth(); // load company!
        $au = $this->getAuthUser();
        
        if (!$au) {
            $this->authUser = false;
            return false;
        }
        
        $this->authUser = $au;
        
        return true;
        
        function get($base, $opts=array())
        {
            die('test');
        }

        function post($base) {
            die('invalid post');
        }
    
    }
}
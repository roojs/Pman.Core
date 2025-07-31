<?php

require_once 'Pman/Core/Auth.php';

/***
* 
* Authentication - Required (really an abstract class )
* 
* other classes extend this..
*/



class Pman_Core_Auth_Required extends Pman_Core_Auth 
{ 
    function getAuth()
    {
        parent::getAuth();
        return $this->authRequired(); // defined in Pman ?? not sure why - is it used elsewhere?
    }
     
}
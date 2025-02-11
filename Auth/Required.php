<?php

require_once 'Pman/Core/Auth.php';

/***
* 
* Authentication - Required (really an abstract class )
* 
* other classes extend this..
*/



class Pman_Core_Auth_SwitchPublic extends Pman_Core_Auth_Required
{ 
    function getAuth()
    {
        parent::getAuth();
        return $this->authRequired();
    }
     
}
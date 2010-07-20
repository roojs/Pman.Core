<?php

require_once 'Pman.php';

class Pman_Core_i18N extends Pman
{
    function getAuth()
    {
        // anyone can downlaod this list - as it's needed prior to the login box.
        return true;
    }
    
    
}
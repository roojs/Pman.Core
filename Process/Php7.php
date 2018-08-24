<?php

class Pman_Core_Php7 extends Pman
{
    
    
    
    function getAuth()
    {
        if (empty($this->cli)) {
            die("CLI only");
        }
    }
    
}
<?php

class Pman_Core_Php7 extends Pman
{
    
    static $cli_desc = "Tests for PHP compatibilty, by including files..."; 
    
    
    
    function getAuth()
    {
        if (empty($this->cli)) {
            die("CLI only");
        }
    }
    
    
    
    
}
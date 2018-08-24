<?php

/**
 *
 * PHP7 produces all sorts of pointless warnings... this attempts to just include all the files,
 * so that you can view them..??
 *
 * would be nice to write the code to fix them.
 *
 */

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
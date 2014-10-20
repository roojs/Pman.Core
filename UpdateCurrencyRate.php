<?php

require_once 'Pman.php';

class Pman_Core_UpdateCurrencyRate extends Pman
{
    
    static $cli_desc = "Update Currency Exchange Rate";
    
    var $cli = false; 
    
    function getAuth() 
    {
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->cli)) {
            $this->cli = true;
            return true;
        }
        
        die("NOT ALLOWED");
    }
    
    function get()
    {
        
    }
    
}
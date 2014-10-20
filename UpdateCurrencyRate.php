<?php

require_once 'Pman.php';

class Pman_Core_UpdateCurrencyRate extends Pman
{
    
    static $cli_desc = "Update Currency Exchange Rate";
    
    static $cli_opts = array();
    
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
        echo"'update currency exchange rate \n";
        
    }
    
}
<?php

require_once 'Pman.php';

class Pman_Core_UpdateCurrencyRate extends Pman
{
    
    static $cli_desc = "Update Currency Exchange Rate";
    
    static $cli_opts = array();
    
    var $cli = false; 
    
    var $actionUrl = 'http://www.oanda.com/currency/historical-rates-classic';
    
    
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
    
    /*
    lang:en
    result:1
    date1:10/14/14
    date:10/20/14
    date_fmt:us
    exch:USD
    exch2:
    expr:EUR
    expr2:
    margin_fixed:0
    format:HTML
    SUBMIT:Get Table
    */
}
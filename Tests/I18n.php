<?php

require_once 'Pman.php';

class Pman_Core_Tests_I18n extends Pman
{
    function getAuth()
    {
        print_R($this);exit;
        if (!$this->bootcli) {
            die("not cli?");
        }
        
    }
    function get()
    {
        require_once 'Pman/Core/I18n.php';
         $i = new Pman_Core_I18n();
         $ret = $i->convertCurrency(100,"HKD","USD");
        var_dump($ret); 
        
        
    }
    function output()
    {
        die("done");
    }
    
    
}
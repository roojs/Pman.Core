<?php

require_once 'Pman/Roo.php';

class Pman_Core_Import_Core_holiday extends Pman_Roo 
{
    static $cli_desc = "Update the holiday database (HK Only at present)"; 
    
    static $cli_opts = array(
        
    );
    
    function getAuth()
    {
        if (!HTML_FlexyFramework::get()->cli) {
            return false;
        }
        
        return true;
        
    }

    var $defaults = array();
    
    function get($v, $opts=array())
    {   
        $d = DB_DataObject::factory('core_holiday');
        $d->
    }
    
    function log($str)
    {
        echo "$str \n";
    }
}
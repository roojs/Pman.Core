<?php

require_once 'Pman/Core/Cli.php';

class Pman_Core_Import_Core_holiday extends Pman_Core_Cli 
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
        //DB_DAtaObject::debugLevel(1);
        $d = DB_DataObject::factory('core_holiday');
        $d->updateHolidays('hk',true );
        $d->updateHolidays('cny', true);
    }
    
    function log($str)
    {
        echo "$str \n";
    }
}
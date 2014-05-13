<?php

require_once 'Pman/Roo.php';

class Pman_Core_Import_Core_geoip extends Pman_Roo
{
    static $cli_desc = "Insert the geoip database";
    
    static $cli_opts = array();
    
    static $id_mapping = array();
    
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        if (!$ff->cli) {
            die("access denied");
        }
        HTML_FlexyFramework::ensureSingle(__FILE__, $this);
        return true;
    }
    
    function post()
    {
        $this->get();
    }
    
    function get()
    {
        
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'onPearError'));
        
        $location = '/tmp/GeoLite2-City-Locations.csv';
        $block = '/tmp/GeoLite2-City-Blocks.csv';
        
        if(!file_exists($location) || !file_exists($block)){
            $this->jerr('GeoLite2-City-Locations.csv OR GeoLite2-City-Blocks.csv does not exists?!');
        }
        
        print_r($this->id_mapping);exit;
        
        $this->insertLocation($location);
        
        $this->insertBlock($block);
    }
    
    
    
    function log($str)
    {
        echo "$str \n";
    }
}

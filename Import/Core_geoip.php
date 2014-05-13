<?php

require_once 'Pman/Roo.php';

class Pman_Core_Import_Core_geoip extends Pman_Roo
{
    static $cli_desc = "Insert the geoip database";
    
    static $cli_opts = array();
    
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        if (!$ff->cli) {
            die("access denied");
        }
        HTML_FlexyFramework::ensureSingle(__FILE__, $this);
        return true;
    }
    
    function get()
    {
        
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'onPearError'));
        
        $location = '/tmp/GeoLite2-City-Locations.csv';
        $block = '/tmp/GeoLite2-City-Blocks.csv';
        
        if(!file_exists($location) || !file_exists($block)){
            $this->jerr('GeoLite2-City-Locations.csv OR GeoLite2-City-Blocks.csv does not exists?!');
        }
        
        $this->jerr('exists');
//        $fc = new File_Convert($cp, 'application/vnd.ms-excel');
//        //var_Dump($img->getStoreName());
//        $csv = $fc->convert('text/csv');
//        unlink($cp);
//        //var_dump($csv);
//        $this->importCsv($csv);
    }
    
    function post()
    {
//        $this->transObj = DB_DataObject::Factory('custinfo');
//        
//        $this->transObj->query('BEGIN');
//        
//        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'onPearError'));
//        
//        $img = DB_DataObject::Factory('images');
//        $img->setFrom(array(
//            'onid' => 0,
//            'ontable' => 'ipshead'
//        ));
//        $img->onUpload(false);
//        
//        require_once 'File/Convert.php';
//        $fc = new File_Convert($img->getStoreName(), $img->mimetype );
//        $csv = $fc->convert('text/csv');
//        $this->importCsv($csv);
    }
    
    function importCsv($csv)
    {
        
        exit;
    }
    
    function log($str)
    {
        echo "$str \n";
    }
}

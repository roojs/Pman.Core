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
        
        static $id_mapping = array();
        
        ini_set("auto_detect_line_endings", true);
        
        $this->insertLocation($location);
        
        $this->insertBlock($block);
    }
    
    function insertLocation($csv)
    {
        $fh = fopen($csv, 'r');
        if (!$fh) {
            $this->jerr("invalid location file");
        }
        
        $req = array(
            'GEONAME_ID', 'CONTINENT_CODE', 'CONTINENT_NAME',
            'COUNTRY_ISO_CODE', 'COUNTRY_NAME', 'SUBDIVISION_ISO_CODE',
            'SUBDIVISION_NAME', 'CITY_NAME', 'METRO_CODE',
            'TIME_ZONE'
        );
        
        $cols = false;
        
        while(false !== ($n = fgetcsv($fh,10000, ',', '"'))) {
            if(!array_filter($n)){ // empty row
                continue;
            }
            
            if (!$cols) {
                $cols = array();
                foreach($n as $k) {
                    $cols[] = strtoupper(trim($k));
                }
                
                if (empty($cols)) {
                    continue;
                }
                foreach($req as $r) {
                    if (!in_array($r,$cols)) {
                        $cols = false;
                        break;
                    }
                }
                continue;
            }
            
            $row = array();
            
            foreach($cols as $i=>$k) {
                $row[$k] = trim($n[$i]);
            }
            
            $this->processLocation($row);
        }
        
    }
    
    function processLocation($row)
    {
        $continent = $this->processContinent($row['CONTINENT_CODE'], $row['CONTINENT_NAME']);
        
        $continent_id = (!empty($continent) && !empty($continent->id)) ? $continent->id : 0;
        
        $country = $this->processCountry($row['COUNTRY_ISO_CODE'], $row['COUNTRY_NAME'], $continent_id);
        
        $division = $this->processDivision($row['SUBDIVISION_ISO_CODE'], $row['SUBDIVISION_NAME']);
        
        
        
    }
    
    function processContinent($code, $name)
    {
        if(empty($code)){
            return false;
        }
        
        $continent = DB_DataObject::factory('core_geoip_continent');
        if(!$continent->get('code', $code)){
            $continent->setFrom(array(
                'code' => $code,
                'name' => (!empty($name)) ? $name : $code
            ));

            $continent->insert();
        }
        
        return $continent;
    }
    
    function processCountry($code, $name, $continent_id)
    {
        if(empty($code)){
            return false;
        }
        
        $country = DB_DataObject::factory('core_geoip_country');
        if(!$country->get('code', $code)){
            $country->setFrom(array(
                'code' => $code,
                'name' => (!empty($name)) ? $name : $code,
                'continent_id' => $continent_id
            ));

            $country->insert();
        }
        
        return $country;
    }
    
    function processDivision($code, $name)
    {
        if(empty($code)){
            return false;
        }
        
        $division = DB_DataObject::factory('core_geoip_division');
        if(!$division->get('code', $code)){
            $division->setFrom(array(
                'code' => $code,
                'name' => (!empty($name)) ? $name : $code
            ));

            $division->insert();
        }
        
        return $division;
    }
    
    
    
    function log($str)
    {
        echo "$str \n";
    }
}

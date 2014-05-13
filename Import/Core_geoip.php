<?php

require_once 'Pman/Roo.php';

class Pman_Core_Import_Core_geoip extends Pman_Roo
{
    static $cli_desc = "Insert the geoip database";
    
    static $cli_opts = array();
    
    var $id_mapping = array();

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
    
    var $processed = 0;
    var $total = 0;
    var $echo = '';
    
    function get()
    {
        
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'onPearError'));
        
        $location = '/tmp/GeoLite2-City-Locations.csv';
        $block = '/tmp/GeoLite2-City-Blocks.csv';
        
        if(!file_exists($location) || !file_exists($block)){
            $this->jerr('GeoLite2-City-Locations.csv OR GeoLite2-City-Blocks.csv does not exists?!');
        }
        
        $this->log("Insert location data start");
        
        $this->insertLocation($location);
        
        $this->log("Insert Block data start");
        
        $this->insertBlock($block);
        
        $this->jok("DONE");
    }
    
    function insertLocation($csv)
    {
        ini_set("auto_detect_line_endings", true);
        
        
        
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
        
        $this->processed = 0;
        $this->total = count(file($csv));
        $this->echo = '';
        
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
            
            $this->processStatus();
        }
        
    }
    
    function insertBlock($csv)
    {
        ini_set("auto_detect_line_endings", true);
        
        $fh = fopen($csv, 'r');
        if (!$fh) {
            $this->jerr("invalid location file");
        }
        
        $req = array(
            'NETWORK_START_IP', 'NETWORK_MASK_LENGTH', 'GEONAME_ID',
            'REGISTERED_COUNTRY_GEONAME_ID', 'REPRESENTED_COUNTRY_GEONAME_ID', 'POSTAL_CODE',
            'LATITUDE', 'LONGITUDE', 'IS_ANONYMOUS_PROXY',
            'IS_SATELLITE_PROVIDER'
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
            
            $this->processBlock($row);
        }
        
    }
    
    
    
    function processLocation($row)
    {   
        $continent = $this->processContinent($row['CONTINENT_CODE'], $row['CONTINENT_NAME']);
        
        $country = $this->processCountry($row['COUNTRY_ISO_CODE'], $row['COUNTRY_NAME'], $continent);
        
        $division = $this->processDivision($row['SUBDIVISION_ISO_CODE'], $row['SUBDIVISION_NAME']);
        
        $city = $this->processCity($row['CITY_NAME'], $row['METRO_CODE'], $row['TIME_ZONE'], $country, $division);
        
        if(!empty($city) && !empty($city->id)){
            $this->id_mapping[$row['GEONAME_ID']] = $city->id;
        }
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
    
    function processCountry($code, $name, $continent)
    {
        if(empty($code)){
            return false;
        }
        
        $country = DB_DataObject::factory('core_geoip_country');
        if(!$country->get('code', $code)){
            $country->setFrom(array(
                'code' => $code,
                'name' => (!empty($name)) ? $name : $code,
                'continent_id' => (!empty($continent) && !empty($continent->id)) ? $continent->id : 0
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
    
    function processCity($name, $metro_code, $time_zone, $country, $division)
    {
        if(empty($name)){
            return false;
        }
        
        $city = DB_DataObject::factory('core_geoip_city');
        
        if($city->get('name', $name)){
            return $city;
        }
        
        $city->setFrom(array(
            'name' => $name,
            'metro_code' => $metro_code,
            'time_zone' => $time_zone,
            'country_id' => (!empty($country) && !empty($country->id)) ? $country->id : 0,
            'division_id' => (!empty($division) && !empty($division->id)) ? $division->id : 0
        ));
        
        $city->insert();
        
        return $city;
        
    }
    
    function processBlock($row)
    {
        if(empty($this->id_mapping[$row['GEONAME_ID']])){
            $this->log("Missing mapping for {$row['GEONAME_ID']}");
            $this->log("IP : {$row['NETWORK_START_IP']}");
            return;
        }
        
        $network_mapping = DB_DataObject::factory('core_geoip_network_mapping');
        
        $start_ip = array_pop(explode(":", $row['NETWORK_START_IP']));
        
        $network_mapping->setFrom(array(
            'start_ip' => $start_ip,
            'mask_length' => pow(2, (128 - $row['NETWORK_MASK_LENGTH'])),
            'city_id' => $this->id_mapping[$row['GEONAME_ID']]
        ));
        
        if(!$network_mapping->find(true)){
            $network_mapping->insert();
        }
        
        $location = DB_DataObject::factory('core_geoip_location');
        if(!$location->get('city_id', $network_mapping->city_id)){
            $location->setFrom(array(
                'latitude' => $row['LATITUDE'],
                'longitude' => $row['LONGITUDE'],
                'city_id' => $network_mapping->city_id
            ));
        }
        
        
        if(!empty($row['POSTAL_CODE'])){
            $city = DB_DataObject::factory('core_geoip_city');
            if($city->get($network_mapping->city_id)){
                return;
            }

            $oc = clone($city);
            $city->postal_code = $row['POSTAL_CODE'];
            
            $city->update($oc);
        }
        
    }
    
    function processStatus()
    {
        echo "$str \n";
    }
}

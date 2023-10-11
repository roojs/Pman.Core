<?php

// default framework settings for release

class Pman_Core_Config {
    
    
    var $memory_limit = 0;
    
    var $defaults = array(  ); // override... 
    
    
    // note if other extended 'config's require more, then you porbably need to include these first.
    var $required_extensions = array(
        'json',        
        'curl',
        'gd',
        'mbstring',
        'http'
    );
    
    function init($ff, $cfg)
    {
        
        
        
        $cfg = $this->overlayDefaults($cfg);
        
        if (!empty($this->memory_limit)) {
            $mem = ini_get('memory_limit');
            if (php_sapi_name() != 'cli' && $this->to_bytes($mem) < $this->to_bytes($this->memory_limit)) {
                trigger_error("increase the memory limit settings to 2048M or more", E_USER_ERROR);
            }
        
        }
        
        $this->verifyExtensions();
        
        
        if (!isset($cfg['Pman']['timezone'])) {
            trigger_error("timezone needs setting in Pman[timezone]", E_USER_ERROR);
        }
        if ($cfg['Pman']['timezone'] != ini_get('date.timezone')) {
            trigger_error("timezone needs setting in php.ini date.timezone = " . $cfg['Pman']['timezone'], E_USER_ERROR);
        }
        
        
        return $cfg;
    }
    
    function to_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val = substr($val, 0, -1);
                $val *= 1024;
            case 'm':
                $val = substr($val, 0, -1);
                $val *= 1024;
                $val = substr($val, 0, -1);
            case 'k':
                $val *= 1024;
        }
    
        return $val;
    }
    function overlayDefaults($cfg)
    {
        if (isset($this->defaults['disable']) && is_array($this->defaults['disable']) ) {
            $this->defaults['disable'] = implode(',', $this->defaults['disable']);
        }
        foreach($this->defaults as $k=>$v) {
            if (is_array($v)) {
                
                if (!isset($cfg[$k])) {
                    $cfg[$k] = $v;
                    continue;
                }
                
                foreach($v as $kk=>$vv) {
                    if (isset($cfg[$k][$kk])) {
                        continue;
                    }
                    
                    $cfg[$k][$kk] = $vv;
                }
            }
            
            if (!isset($cfg[$k])) {
                $cfg[$k] = $v;
            }
        }
        
        return $cfg;
    }
    function verifyExtensions()
    {
        $error = array();
        
        foreach ($this->required_extensions as $e){
            
            if(empty($e) || extension_loaded($e)) {
                continue;
            }
            
            $error[] = "Error: Please install php extension: {$e}";
        }
        
        if(empty($error)){
           return true; 
        }
        trigger_error("Missing Extensions: \n" . implode('\n', $error), E_USER_ERROR);
    }

}

<?php

// default framework settings for release

class Pman_Core_Config {
    
    
    var $memory_limit = 0;
    
    var $defaults = array(
        'Pman' => array(
            'storedir' => '/home/press',
        
        ),
        'Factiva' => array(
            'HTTP_HOST' => 'www.media-outreach.com',
            'baseURL' => '/release.php',
        ),
         
        
        'Mail' => array(
             'helo' => 'media-outreach.com'
        ),
        
        'Siteworth' => array(
            'key' => '1baaa889d4d006a7ef7c2e34a51d5b4b526fdbeb',
        ) 
    );
    function init($ff, $cfg)
    {
      
        $cfg = $this->overlayDefaults($cfg);
        
        if (!empty($this->memory_limit)) {
            $mem = ini_get('memory_limit');
            if (php_sapi_name() != 'cli' && $this->to_bytes($mem) < $this->to_bytes($this->memory_limit)) {
                die("increase the memory limit settings to 2048M or more");
            }
        
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
        $this->defaults['disable'] = implode(',', $this->defaults['disable']);
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
    

}

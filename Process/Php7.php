<?php

/**
 *
 * PHP7 produces all sorts of pointless warnings... this attempts to just include all the files,
 * so that you can view them..??
 *
 * would be nice to write the code to fix them.
 *
 */

class Pman_Core_Php7 extends Pman
{
    
    static $cli_desc = "Tests for PHP compatibilty, by including files..."; 
    
    
    
    function getAuth()
    {
        if (empty($this->cli)) {
            die("CLI only");
        }
        
    }
    
    function get()
    {
        $base = realpath(__DIR__ . '/../..');
        var_dump($base);
        exit;
        $this->scan($base, '');
    }
    
    function scan($p,$pr, $path=false) {
        
        
        
        $full_path = array($p,$pr);
        $class_path = array();
        if ($path !== false)  {
            $full_path= array_merge($full_path, $path);
            $class_path = array_merge($class_path, $path);
        }
        //print_r("CHKDIR:    ". implode('/', $full_path)."\n");
        
        foreach(scandir(implode('/', $full_path)) as $d) {
            
            if (!strlen($d) || $d[0] == '.') {
                continue;
            }
            $chk = $full_path;
            $chk[] = $d;
            
            $clp = $class_path;
            
            
            
            //print_r("CHK:          " . implode('/', $chk)."\n");
            // is it a file.. and .PHP...
            if (!is_dir(implode('/', $chk))) {
                if (!preg_match('/\.php$/',$d)) {
                    continue;
                }
                $clp[] = preg_replace('/\.php$/','', $d);
                
                //print_r("CLP:          " . implode('/', $clp)."\n");
                var_dump(implode('/', $clp ));
                continue;
            }
            $clp[] = $d;
            // otherwise recurse...
            //print_r("RECURSE:        " . implode('/', $clp)."\n");
            
            $this->scan($p,$pr, $clp);
        }
    }
    
    
}
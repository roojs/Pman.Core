<?php

/**
 *
 * PHP7 produces all sorts of pointless warnings... this attempts to just include all the files,
 * so that you can view them..??
 *
 * would be nice to write the code to fix them.
 *
 */
require_once 'Pman.php';

class Pman_Core_Process_Php7 extends Pman 
{

    static $cli_desc = "Tests for PHP compatibilty, by including files...";
    static $cli_opts = array();

    function getAuth() 
    {
        if (empty($this->bootLoader->cli)) {
            die("CLI only");
        }
    }
    
    function get($base, $opts = array()) 
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline ){
//            throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
            echo "{$errstr}\n";
        });
        
        $this->scan();
    }
    
    function scan($route = array()) 
    {
        $dir = $this->rootDir . "/" . implode("/", $route);
        
        echo "Looking for {$dir}\n";
        
        foreach (scandir($dir) as $d) {
            
            if (!strlen($d) || $d[0] == '.') {
                continue;
            }
            
            if (is_dir($d)) {
                $this->scan(array_merge($route, array($d)));
                continue;
            }
            
            if (!preg_match('/\.php$/', $d)) {
                continue;
            }
            
//            require_once implode('/', $route) . "/" . $d;
            
            try {
                require_once implode('/', $route) . "/" . $d;
            } catch (ErrorException $ex) {
                echo "Unable to load configuration file.";
            }
            
        }
        
    }

//    function scan($p, $pr, $path = false) 
//    {
//        $full_path = array($p, $pr);
//        $class_path = array();
//        if ($path !== false) {
//            $full_path = array_merge($full_path, $path);
//            $class_path = array_merge($class_path, $path);
//        }
//        //print_r("CHKDIR:    ". implode('/', $full_path)."\n");
//
//        foreach (scandir(implode('/', $full_path)) as $d) {
//
//            if (!strlen($d) || $d[0] == '.') {
//                continue;
//            }
//            $chk = $full_path;
//            $chk[] = $d;
//
//            $clp = $class_path;
//
//
//
//            //print_r("CHK:          " . implode('/', $chk)."\n");
//            // is it a file.. and .PHP...
//            if (!is_dir(implode('/', $chk))) {
//                if (!preg_match('/\.php$/', $d)) {
//                    continue;
//                }
//                $clp[] = preg_replace('/\.php$/', '', $d);
//
//                //print_r("CLP:          " . implode('/', $clp)."\n");
//                require_once "Pman/" . implode('/', $clp) . '.php';
//                continue;
//            }
//            $clp[] = $d;
//            // otherwise recurse...
//            //print_r("RECURSE:        " . implode('/', $clp)."\n");
//
//            $this->scan($p, $pr, $clp);
//        }
//    }

    function output() 
    {
        die("DONE");
    }

}

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
            print_r(array(
                $errno,
                $errstr,
                $errfile,
                $errline
            ));
        });
        
        $this->scan(array("Pman"));
        $this->scan(array("Hebe"));
        
        exit;
    }
    
    function scan($route = array()) 
    {
        $dir = $this->rootDir . "/" . implode("/", $route);
        
        echo "Looking for {$dir}\n";
        
        foreach (scandir($dir) as $d) {
            
            if (!strlen($d) || $d[0] == '.') {
                echo "not handle {$d}\n";
                continue;
            }
            
            if (is_dir($d)) {
                echo "directory : {$d}\n";
                $this->scan(array_merge($route, array($d)));
                continue;
            }
            
            if (!preg_match('/\.php$/', $d)) {
                continue;
            }
            
            try {
                
                require_once implode('/', $route) . "/" . $d;
                
            } catch (ErrorException $ex) {
                echo $ex->getMessage() . "\n";
            }
            
        }
        
    }
    
    function output() 
    {
        die("DONE");
    }

}

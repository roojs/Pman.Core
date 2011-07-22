<?php

/**
* wrapper code around js builder...
* 
*  -- we will use this later to compile on the fly...
*/
require_once 'Pman.php';


class Pman_Core_JsCompile  extends Pman
{
    var $cli = false;
    function getAuth()
    {
        // command line only ATM
        $this->cli = HTML_FlexyFramework::get()->cli;
      //  var_dump($this->cli);
        if ($this->cli) {
            return true;
        }
        return  false;
    }
    
    
    function get($proj, $args)
    {
        if (empty($args)) {
            die("missing action : eg. build or install");
        }
        // list of projects.
        if (empty($args[1])) {
            
            $ar = $this->gatherProjects();
             echo "SELECT Component to build\n";
            print_r($ar);
            exit;
        } else {
            $ar = array($args[1]); 
            //$ar = $args;
            //array_shift($ar);
        }
        
        switch ($args[0]) {
            case 'build':
             
                foreach($ar as $p) {
                    $this->build($p);
                    //$this->install($p);
                }
                break;
            case 'install' :     // needed for install on remote sites..
                die('not yet..');
                foreach($ar as $p) {
                    $this->install($p);
                }
                break;
        }
        exit;
    }
    
    /***
     * build:
     *
     * @param {String} $proj name of Pman component to build
     *
     * 
     *
     */
     
     
     
    function build($proj) 
    {
        echo "Building $proj\n";
       // var_dump($proj);
        if (empty($proj)) {
            $this->err = "no project";
            if ($this->cli) echo $this->err;
            return;
        }
        // first item in path is always the app start directory..
        $src= array_shift(explode(PATH_SEPARATOR, ini_get('include_path'))) .'/Pman/'. $proj;
        
        
        
       //$tmp = ini_get('session.save_path')."/{$proj}_". posix_getuid(). '_'.md5($src);
        
        
        require_once 'System.php';
        $seed= System::which('seed');
        if (!$seed) {
            $this->err ="no seed installed";
            if ($this->cli) echo $this->err;
            return false;
        }
        
        $o = HTML_FlexyFramework::get()->Pman_Core;
        
        if (empty($o['jspacker']) || !file_exists($o['jspacker'].'/pack.js')) {
            $this->err ="no jstoolkit path set [Pman_Core][jspacker] to the
                    introspection documentation directory where pack.js is located.";
            if ($this->cli) echo $this->err;
            return false;
        }  
        
        // should we be more specirfic!??!?!?
         
        $cmd = "$seed {$o['jspacker']}/pack.js -m $proj  -a  $src/*.js";
        echo "$cmd\n";
        passthru($cmd);
        // technically we should trash old compiled files.. 
        // or we move towards a 'cache in session directory model..'
        
        
        /*
        
        $ret = $tmp . '/'. $proj . '.js';
        if ($this->cli) {
            echo "BUILT:  $ret \n";
            exit;
        }
        return $ret;
        */
        
    }
    // link {PROJECT}/compiled/{PROJECT}.js to _compiled_ folder to make it live.
        
    function install($proj) 
    {
       
        $base = dirname(realpath($_SERVER["SCRIPT_FILENAME"]));
        if (empty($base )) {
            $base = getcwd();
        }
        var_dump($base .'/Pman/'. $proj.'/compiled/'.$proj .'.js');
        $src =  realpath($base .'/Pman/'. $proj.'/compiled/'.$proj .'.js');
        if (!$src) {
            echo "SKIP : no js file $proj\n";
            return;
        }
        if (!file_exists("$base/_compiled_")) {
            mkdir ("$base/_compiled_", 0755, true);
        }
        $target = "$base/_compiled_/".$proj .'.js';
        print_R(array($src,$target));
        if (file_exists($target)) {
            return; // already installed.
        }
        
        symlink($src, $target);
        
        
    }
    
    function gatherProjects() {
        $src= array_shift(explode(PATH_SEPARATOR, ini_get('include_path'))) .'/Pman';
        
        $ret = array();
        foreach(scandir($src) as $f) {
            if (!strlen($f) || $f[0] == '.') {
                continue;
            }
            
            $fp = "$src/$f";
            if (!is_dir($fp)) {
                continue;
            }
            if ($f == 'templates') {
                continue;
            }
            $ret[] = $f;
            
            
        }
        return $ret;
    }
}


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
        
        
        
        $tmp = ini_get('session.save_path')."/{$proj}_". posix_getuid(). '_'.md5($src);
        
        
        require_once 'System.php';
        $seed= System::which('seed');
        if (!$seed) {
            $this->err ="no seed installed";
            if ($this->cli) echo $this->err;
            return false;
        }
        
        $o = PEAR::getStaticProperty('Pman_Core','options');
        if (empty($o['jspacker']) || !file_exists($o['jspacker'].'/pack.js')) {
            $this->err ="no jstoolkit path set [Pman_Core][jspacker] to the introspection documentation directory where pack.js is located.";
            if ($this->cli) echo $this->err;
            return false;
        }  
        
        // should we be more specirfic!??!?!?
        
        $buildjs = 
        $cmd = "$seed {$o['jspacker']}/pack.js -m $proj  -a  $src/*.js -o $tmp";
        echo $cmd;
        passthru($cmd);
        
        
        exit;
        // copy into the revision controlled area.
        
        
        
        
        
        
        
        $src = realpath(dirname(__FILE__).'/../../_compiled_tmp_/'.$proj .'.js');
        if (!$src) {
            return;
        }
        $pdir = realpath(dirname(__FILE__).'/../'. $proj);
        if (!file_exists($pdir.'/compiled')) {
            mkdir($pdir.'/compiled', 0755, true);
            
        }
        copy($src , $pdir.'/compiled/'. $proj .'.js');
        
        // copy the translation strings.
        $src = realpath(dirname(__FILE__).'/../../_compiled_tmp_/'.$proj .'/build/_translation_.js');
       // var_dump($src);
        
        $pdir = realpath(dirname(__FILE__).'/../'. $proj);
       
        copy($src , $pdir.'/compiled/_translation_.js');
        
        if ($svn) {
            $base = getcwd();
            chdir($pdir);
            $cmd = "$svn add compiled";
            `$cmd`;
            $cmd = "$svn add ". escapeshellarg('compiled/'.$proj .'.js'); 
            $cmd = "$svn add ". escapeshellarg('compiled/_translation_.js'); 
            
            `$cmd`;
            `$svn commit -m 'update compiled version'`;
            chdir($base);
        }
        
        
        
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
        $src =  realpath(dirname(__FILE__).'/../');
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


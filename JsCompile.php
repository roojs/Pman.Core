<?php

/**
* wrapper code around js builder...
* 
*  -- we will use this later to compile on the fly...
*
*  -- updated to use roojspacker https://github.com/roojs/roojspacker
*
*
* For general usage:
*  $x = new Pman_Core_JsCompile();
*  $x->pack('/path/to/files/', 'destination')
*  
*/
require_once 'Pman.php';


class Pman_Core_JsCompile  extends Pman
{
    
    static $cli_desc = "Wrapper around Javascript compression tools
                        Runs the javascript compiler - merging all the JS files so the load faster.
                        Note: cfg option Pman_Builder['jspacker'] must be set to location of jstoolkit code 
";
    
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
    
    
    function get($proj, $args=array())
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
    /**
     * packScript:
     *
     * @param {String} basedir absolute path to files
     * @param {Array}  list of files (ontop of basedir) 
     * @param {String} output url (path to basedir basically), or false
     *                  to not compile
     * 
     *
     */
    
    static function jsSort($a,$b)
    {
        $a = substr($a, 0, -3);
        $b=  substr($b, 0, -3);
        if ($a == $b) {
            return 0;
        }
        return ($a > $b) ? +1 : -1;
    }
    
    
    function packScript($basedir, $files,  $output_url, $compile=true)
    {
        // this outputs <script tags..>
        // either for just the original files,
        // or the compressed version.
        // first expand files..
        
        echo "<!-- compiling   $basedir  -->\n";
        
        $arfiles = array();
        $ofiles = array();
        foreach($files as $f) {
             if (!file_exists($basedir .'/' .$f)) {
                continue;
            }
            if (!is_dir($basedir .'/' .$f)) {
                
                $arfiles[$basedir .'/' .$f] = filemtime($basedir .'/' .$f);
                $ofiles[] = $f;
                continue;
            }
            
            foreach(glob($basedir .'/' .$f.'/*.js') as $fx) {
                
                $arfiles[$fx] = filemtime($fx);
                $ofiles [] = $f . '/'. basename($fx);
            }
        }
        $tf = 
        // sort exc. the .js
        usort($ofiles,function($a,$b) { return Pman_Core_JsCompile::jsSort($a,$b); });
        
        //print_R($ofiles);
        
        $output = md5(serialize($arfiles)) .'.js';
        
        if ( $compile && !file_exists($basedir.'/_cache_/'.$output)) {
            $this->pack($arfiles,$basedir.'/_cache_/'.$output);
        }
        
        if ($compile && file_exists($basedir.'/_cache_/'.$output)) {
            
            echo '<script type="text/javascript" src="'.$output_url.'/_cache_/'. $output.'"></script>';
            return;
        }
        foreach($ofiles as $f) {
            echo '<script type="text/javascript" src="'.$output_url.'/'.$f.'"></script>'."\n";
            
        }
          
    }
    
    // this is depricated... - we can use the pear CSS library for this..
    
    function packCss($basedir, $files,   $output_url)
    {
        // this outputs <script tags..>
        // either for just the original files,
        // or the compressed version.
        // first expand files..
        
        $arfiles = array();
        $ofiles = array();
        //print_R($files);
        foreach($files as $f) {
            if (!file_exists($basedir .'/' .$f)) {
                continue;
            }
            if (!is_dir($basedir .'/' .$f)) {
                $arfiles[$basedir .'/' .$f] = filemtime($basedir .'/' .$f);
                $ofiles[] = $f;
                continue;
            }
            foreach(glob($basedir .'/' .$f.'/*.css') as $fx) {
                $arfiles[$fx] = filemtime($fx);
                $ofiles [] = $f . '/'. basename($fx);
            }
        }
        
        $output = md5(serialize($arfiles)) .'.css';
        
        if (!file_exists($basedir.'/_cache_/'.$output)) {
            $this->packCssCore($arfiles,$basedir.'/_cache_/'.$output);
        }
        //var_dump()$basedir. '/_cache_/'.$output);
        if (file_exists($basedir. '/_cache_/'.$output)) {
            echo '<link type="text/css" rel="stylesheet" media="screen" href="'.$output_url. '/_cache_/'. $output.'" />';
            return;
        }
        foreach($ofiles as $f ) {
            echo '<link type="text/css" rel="stylesheet" media="screen" href="'.$output_url.'/'.$f.'" />'."\n";
             
        }
         
        
    }
     /**
     * wrapper arroudn packer...
     * @param {Array} map of $files => filemtime the files to pack
     * @param {String} $output name fo file to output
     *
     */
    function packCssCore($files, $output)
    {
        

        
        echo '<!-- JSCOMPILE - should not be used for CSS packing ?? -->';
        return false;
        // if we did.. use this?
        
        //require_once 'HTML/CSS/Minify.php';
        //$x = new HTML_CSS_Minify(substr($relpath,0,-1), $dir, $relfiles);
            
          //  file_put_contents($compiledir.'/'.$output , $x->minify( $this->baseURL.$asset));
        
        
    }
    /**
     * wrapper arround packer...
     * uses the translation module & puts index in __tra
     * 
     * @param {Array} map of $files => filemtime the files to pack
     * @param {String} $output name fo file to output
     *
     */
    
    function pack($files, $output, $translation_base=false)
    {
        
        if (empty($files)) {
            return false;
        }
        
        // if packer is running, then dont compile - just output onebyone...
        
        
        require_once 'System.php';
        $packer = System::which('roojspacker');
        
        
        if (!$packer) {
            echo '<!-- roojspacker is not installed -->';
            return false;
            
        }
        $targetm = file_exists($output) && filesize($output) ? filemtime($output) : 0;
        $max = 0;
        $ofiles = array();
        foreach($files as $f => $mt) {
            $max = max($max,$mt);
            $ofiles[] = escapeshellarg($f);
        }
        if ($max < $targetm) {
            echo '<!--  use cached compile. -->';
            return true;
        }
        
        
        
        $pg = System::which('pgrep');
        $cmd = "$pg roobuilder";
        $out = trim(`$cmd`);
        if (strlen($out) > 0) {
            echo '<!--  onther process is compiling compile. -->';
            return false;
        }
         
        
        if (file_exists($output)) {
            unlink($output);
        }
        
         
        if (!file_exists(dirname($output))) {
            mkdir(dirname($output), 0755, true);
        }
        
        usort($ofiles, function($a,$b)  { return strlen($a) > strlen($b) ? 1 : -1; });
        
        //$eoutput = " -k  -o " . escapeshellarg($output) ; // with whitespace..
        $eoutput = "  -t " . escapeshellarg($output) ;
          
        // no support for translation any more?         
        //if (  $translation_base) {
        //    $toutput = " -t ". escapeshellarg(preg_replace('/\.js$/', '.__translation__.js', $output)) .
        //            " -p " . escapeshellarg($translation_base) ;//." -k "; // this kills the compression.
        //            
        //}
        
    
        $cmd = "$packer  $eoutput  -f " . implode(' -f ', $ofiles) . ' 2>&1';
        //echo "<PRE>$cmd\n";
        //echo `$cmd`;
        
         echo "<!-- Compile javascript
          
            " . htmlspecialchars($cmd) . "
            
            -->";
            
       // return false;
        
        $res = `$cmd`;
        //exit;
        file_put_contents($output.'.log', $cmd."\n\n". $res);
        // since this only appears when we change.. it's ok to dump it out..
        echo "<!-- Compiled javascript
            " . htmlspecialchars($res) . "
            -->";
        clearstatcache();
        // we should do more checking.. return val etc..
        if (file_exists($output) && filesize($output) && ($max < filemtime($output) ) ) {
            echo "<!-- file looks like its been generated -->\n";
            return true;
        }
        echo '<script type="text/javascript"> alert('. json_encode("Error: Javascript Compile failed\n" . $res) .');</script>';
     
        
        echo "<!-- JS COMPILE ERROR: packed file did not exist  -->";
        return false;
        
    }
    
    
    function packIsRunning()
    {
        require_once 'System.php';
      
        
    }
    
    
    // depricated verison using seed.
    function packSeed($files, $output, $translation_base=false)
    {
        
         
        $o = HTML_FlexyFramework::get()->Pman_Core;
        
        if (empty($o['packseed']) || !file_exists($o['jspacker'].'/pack.js')) {
            echo '<!-- JS COMPILE ERROR: option: Pman_Core[jspacker] not set to directory -->';
            return false;
            
        }
        require_once 'System.php';
        $seed= System::which('seed');
        $gjs = System::which('gjs');
        
        if (!$seed && !$gjs) {
            echo '<!-- seed or gjs are  not installed -->';
            return false;
            
        }
        $targetm = file_exists($output) && filesize($output) ? filemtime($output) : 0;
        $max = 0;
        $ofiles = array();
        foreach($files as $f => $mt) {
            $max = max($max,$mt);
            $ofiles[] = escapeshellarg($f);
        }
        if ($max < $targetm) {
            echo '<!--  use cached compile. -->';
            return true;
        }
        //var_dump($output);
        if (!file_exists(dirname($output))) {
            mkdir(dirname($output), 0755, true);
        }
        $lsort = create_function('$a,$b','return strlen($a) > strlen($b) ? 1 : -1;');
        usort($ofiles, $lsort);
        
        //$eoutput = " -k  -o " . escapeshellarg($output) ; // with whitespace..
        $eoutput = "  -o " . escapeshellarg($output) ;
                   
        if (  $translation_base) {
            $toutput = " -t ". escapeshellarg(preg_replace('/\.js$/', '.__translation__.js', $output)) .
                    " -p " . escapeshellarg($translation_base) ;//." -k "; // this kills the compression.
                    
        }
        
        
        $cmd = ($seed ?
             "$seed {$o['packseed']}/pack.js " :
             "$gjs -I {$o['packseed']} -I {$o['packseed']}/JSDOC  {$o['packseed']}/pack.js -- -- " 
              
             ) . " $eoutput  $toutput " . implode($ofiles, ' ') . ' 2>&1';
        //echo "<PRE>$cmd\n";
        //echo `$cmd`;
        
         echo "<!-- Compile javascript
          
            " . htmlspecialchars($cmd) . "
            
            -->";
            
       // return false;
        
        $res = `$cmd`;
        //exit;
        file_put_contents($output.'.log', $cmd."\n\n". $res);
        // since this only appears when we change.. it's ok to dump it out..
        echo "<!-- Compiled javascript
            " . htmlspecialchars($res) . "
            -->";
            
        // we should do more checking.. return val etc..
        if (file_exists($output) && ($max < filemtime($output) ) ) {
            
            return true;
        }
        
         
        echo "\n<!-- JS COMPILE ERROR: packed file did not exist  -->\n";
        return false;
        
    }
    
    
    /***
     * build:
     *
     * @param {String} $proj name of Pman component to build
     * runs pack.js -m {proj} -a $src/*.js
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
        
        if (empty($o['packseed']) || !file_exists($o['packseed'].'/pack.js')) {
            $this->err ="no jstoolkit path set [Pman_Core][packseed] to the
                    introspection documentation directory where pack.js is located.";
            if ($this->cli) echo $this->err;
            return false;
        }  
        
        // should we be more specirfic!??!?!?
         
        $cmd = "$seed {$o['packseed']}/pack.js -m $proj  -a  $src/*.js";
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


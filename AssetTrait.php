<?php
// a little experimental... we are going to use the same name as the class. for these..

trait Pman_Core_AssetTrait {
    
    
    
     /**
     * usage in template
     * {outputJavascriptDir(#Hydra#,#Hydra.js",#.......#)}
     *
     * call_user
     * 
     */
    
    function outputJavascriptDir($path)
    {
        
        $relpath = $this->rootURL . '/' . $path .'/';
        $ff = HTML_FlexyFramework::get();
        $dir =   $this->rootDir.'/' . $path;
        
        $args = func_get_args();
        $ar = array();
        if (count($args) < 2) {
            $ar = glob($dir . '/*.js');
        } else {
            array_shift($args);
            foreach($args as $f) {
                if (strpos($f,'*') > -1) {
 
                    $ar = array_merge($ar ,  glob($dir . '/'. $f));
                    continue;
                }
                if (!preg_match('/\.js$/', $f)) {
                    $f .= ".js";
                }
                $ar[] = $dir .'/'. $f;
            }
          
        }
         // cached version?? - how do we decide if it's expired?
        // while scanning the directory is slow... - it's faster than serving every file...
        if (empty($ar)) {
            echo "<!-- skipping $path - no files found -->\n";
            return;
        }
        
        //$path = $this->rootURL ."/Pman/$mod/";
        
        
        
        $files = array();
        $arfiles = array();
        $maxtime = 0;
        $mtime = 0;
        foreach($ar as $fn) {
            $f = basename($fn);
            if (!preg_match('/\.js$/', $f) || $fn == '.js' || !file_exists($dir . '/' . $f)) { // only javascript files... (so XXX.Dialog.YYY*  works..)
                continue;
            }
            // got the 'module file..'
            $mtime = filemtime($dir . '/'. $f);
            $maxtime = max($mtime, $maxtime);
            $arfiles[$fn] = $mtime;
            $files[] = $relpath  . $f . '?ts='.$mtime;
        }
        
        ksort($arfiles); // just sort by name so it's consistant for serialize..
        
        require_once 'Pman/Core/Asset.php';
        $compiledir = Pman_Core_Asset::getCompileDir('js', '', true);
        
         
        
        $lsort = function($a,$b) { return strlen($a) > strlen($b) ? 1 : -1; };
        usort($files, $lsort);
        
        $ff = HTML_FlexyFramework::get();
        
        if (!empty($ff->Pman['isDev']) && !empty($_REQUEST['isDev'])) {
            echo "<!-- Javascript compile turned off (isDev on) -->\n";
            $this->assetArrayToHtml($files,'js');
            return;
        }
        
        
        $smod = str_replace('/','.',$path);
        
        $output = date('Y-m-d-H-i-s-', $maxtime). $smod .'-'.md5(serialize($arfiles)) .'.js';
         
        
        
        
        // where are we going to write all of this..
        // This has to be done via a 
        if (!file_exists($compiledir.'/'.$output) || !filesize($compiledir.'/'.$output)) {
            require_once 'Pman/Core/JsCompile.php';
            $x = new Pman_Core_JsCompile();
            
            $x->pack($arfiles,$compiledir.'/'.$output, false);
            clearstatcache();
            if (!file_exists($compiledir.'/'.$output) ||
                !filesize($compiledir.'/'.$output)) {
                echo "<!-- compile did not generate files : ". basename($compiledir)  ."/{$output} -->\n";
                $this->assetArrayToHtml($files,'js');
                return;
            } 
            
        } else {
         //   echo "<!-- file already exists: {$basedir}/{$output} -->\n";
        }
        
        $asset = $ff->project == 'Pman' ? '/Core/Asset/js/' : '/Asset/js/';
        //$this->arrayToJsInclude(  $files);
        $this->assetArrayToHtml(  array(
            $this->baseURL.$asset. $output,
          
        ), 'js');
        
    }
    
    function assetArrayToHtml($ar, $type)
    {
        foreach($ar as $f) {
            switch( $type) {
                case 'js':
                    echo '<script type="text/javascript" src="'. $f. '"></script>'."\n";
                    break;
                case 'css':
                    echo '<link rel="stylesheet" href="'. $f. '"/>'."\n";
                    break;
       
            }
            
        }
    }
    
    
    /**
     * usage in template
     * {outputCSSDir(#{Hydra/templates/images/css/#,#Hydra.js",#.......#)}
     */
    
    function outputCSSDir($path)
    {
          
        $relpath = $this->rootURL . '/' . $path .'/';
        $ff = HTML_FlexyFramework::get();
        $dir =   $this->rootDir.'/' . $path;
        
        $args = func_get_args();
        $ar = array();
        if (count($args) < 2) {
            $ar = glob($dir . '/*.css');
        } else {
            array_shift($args);
            foreach($args as $f) {
                if (strpos($f,'*') > -1) {
 
                    $ar = array_merge($ar ,  glob($dir . '/'. $f));
                    continue;
                }
                // what if the fiel does not exist???
                $ar[] = $dir .'/'. $f;
            }
          
        }
        if (empty($ar)) {
            echo "<!-- skipping $path - no files found -->\n";
            return;
        }
        
         // cached version?? - how do we decide if it's expired?
        // while scanning the directory is slow... - it's faster than serving every file...
        
        
        //$path = $this->rootURL ."/Pman/$mod/";
        
        //print_R($ar);exit;
        $missing_files  = false;
        $files = array();
        $arfiles = array();
        $relfiles = array(); // array of files without the path part...
        $maxtime = 0;
        $mtime = 0;
        foreach($ar as $fn) {
            $relfiles[] = substr($fn, strlen($dir)+1);
            $f = basename($fn);
            // got the 'module file..'
            
            if (!file_exists($dir . '/'. $f)) {
                echo "<!-- missing {$relpath}{$f} -->\n";
                $files[] = $relpath  . $f . '?ts=0';
                $missing_files = true;
                continue;
            }
            
            $mtime = filemtime($dir . '/'. $f);
            $maxtime = max($mtime, $maxtime);
            $arfiles[$fn] = $mtime;
            $files[] = $relpath  . $f . '?ts='.$mtime;
            
            
            
        }
        if ($missing_files) {
            $this->assetArrayToHtml($files, 'css');
            return;
            
        }
        
         
        //print_r($relfiles);
      
        require_once 'Pman/Core/Asset.php';
        $compiledir = Pman_Core_Asset::getCompileDir('css', '', true);
        
         
        if (!file_exists($compiledir)) {
            mkdir($compiledir,0700,true);
        }
        
         
        
        
        // yes sort... if includes are used - they have to be in the first file...
        $lsort = function($a,$b ) {
                return strlen($a) > strlen($b) ? 1 : -1;
        };
        usort($files, $lsort);
        usort($relfiles,$lsort);
       // print_R($relfiles);
        
        $ff = HTML_FlexyFramework::get();
        
        // isDev set
        
        if ((!empty($ff->Pman['isDev']) || $_SERVER['HTTP_HOST'] == 'localhost' )&& !empty($_REQUEST['isDev'])) {
            echo "<!-- CSS compile turned off (isDev on) -->\n";
            $this->assetArrayToHtml($files,'css');
            return;
        }
        
        
        $smod = str_replace('/','.',$path);
        
        $output = date('Y-m-d-H-i-s-', $maxtime). $smod .'-'.md5(serialize(array($this->baseURL, $arfiles))) .'.css';
         
        $asset = $ff->project == 'Pman' ? '/Core/Asset/css/' : '/Asset/css/';
        
        // where are we going to write all of this..
        // This has to be done via a 
        if ( !file_exists($compiledir.'/'.$output) || !filesize($compiledir.'/'.$output)) {
            
            //print_r($relfiles);
            
            require_once 'HTML/CSS/Minify.php';
            $x = new HTML_CSS_Minify(substr($relpath,0,-1), $dir, $relfiles);
            
            file_put_contents($compiledir.'/'.$output , $x->minify( $this->baseURL.$asset));
            clearstatcache();
            if (!file_exists($compiledir.'/'.$output) ||
                !filesize($compiledir.'/'.$routput)) {
                echo "<!-- compile did not generate files : " . basename($compiledir) . "/{$output} -->\n";
                $this->assetArrayToHtml($files,'css');
                return;
            } 
            
        } else {
         //   echo "<!-- file already exists: {$basedir}/{$output} -->\n";
        }
        
         
        //$this->arrayToJsInclude(  $files);
        $this->assetArrayToHtml(  array(
            $this->baseURL.$asset. $output,
          
        ),'css');
        
    }
    
    
    
    function outputSCSS($smod)
    {
        // we cant output non-cached versions of this.... 
        $ff = HTML_FlexyFramework::get();
        $fp =   "{$this->rootDir}/Pman/$smod/scss/{$smod}.scss";
        var_dump($fp);
        if (!file_exists($fp)) {
            return;
        }
        
        $ar = glob(dirname($fp) . '/*.scss');
        $maxtime = 0;
        foreach($ar as $fn) {
            $maxtime=max($maxtime, filemtime($fn));
        }
        
        
        
        //print_r($relfiles);
      
        require_once 'Pman/Core/Asset.php';
        $compiledir = Pman_Core_Asset::getCompileDir('scss', $smod, true);
        
         
        if (!file_exists($compiledir)) {
            mkdir($compiledir,0700,true);
        }
        
        
        
         
        $output = date('Y-m-d-H-i-s-', $maxtime). $smod .'-'.md5(serialize(array($this->baseURL, $ar))) .'.css';
         
        $asset = $ff->project == 'Pman' ? '/Core/Asset/scss/' : '/Asset/scss/';
        
        // where are we going to write all of this..
        // This has to be done via a
        
        
        
        if ( !file_exists($compiledir.'/'.$output) || !filesize($compiledir.'/'.$output)) {
            require_once 'HTML/Scss.php';
            $scss = new HTML_Scss();
         
            $scss->setSourceMap(HTML_Scss::SOURCE_MAP_FILE);
            $scss->setSourceMapOptions(array(
                    //'sourceRoot' => $file['sourceMapRootpath'],
            
                    // an optional name of the generated code that this source map is associated with.
                    //'sourceMapFilename' => "{$file['baseDir']}/{$file['name']}.map",
            
                    // url of the map
                    //'sourceMapURL' => "{$file['name']}.map",
            
                    // absolute path to a file to write the map to
                    //'sourceMapWriteTo' => "{$file['baseDir']}/{$file['name']}.map",
            
                    // output source contents?
                    'outputSourceFiles' => false,
            
                    // this is added to the file path.
                  //  'sourceMapRootpath' =>  '../',
            
                    // this is removed from the filepath.
                    //'sourceMapBasepath' => $rootDir .'/roojs1/scss'
                   // 'sourceMapBasepath' => dirname($fp)
                
            ));
           
            $scss->setImportPaths($this->rootDir .'/roojs1/scss');
            $scss->setFormatter('Expanded');
             
            echo $scss->compile("@import \"{$smod}.scss\";");
            exit;
             
            file_put_contents($compiledir.'/'.$output, $scss->compile("@import \"{$smod}.scss\";"));
            
            //print_r($relfiles);
            
            require_once 'HTML/SCSS.php';
            
            require_once 'HTML/CSS/Minify.php';
            $x = new HTML_CSS_Minify(substr($relpath,0,-1), $dir, $relfiles);
            
            file_put_contents($compiledir.'/'.$output , $x->minify( $this->baseURL.$asset));
            clearstatcache();
            if (!file_exists($compiledir.'/'.$output) ||
                !filesize($compiledir.'/'.$routput)) {
                echo "<!-- compile did not generate files : " . basename($compiledir) . "/{$output} -->\n";
                $this->assetArrayToHtml($files,'css');
                return;
            } 
            
        } else {
         //   echo "<!-- file already exists: {$basedir}/{$output} -->\n";
        }
        
         
        //$this->arrayToJsInclude(  $files);
        $this->assetArrayToHtml(  array(
            $this->baseURL.$asset. $output,
          
        ),'css');
        
    }
    
    
}
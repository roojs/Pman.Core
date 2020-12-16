<?php
 
 /**
  * Generic cached assset server... -- No security om this.. should only return compressed CSS/JS
  *
  * Does a few tricks with headers to improve caching...
  *
  *
  * Also includes code to generate assets...
  *
  * methods outputJavascriptDir / outputCssDir generate links to 
  *    BASEURL/Asset/css/xxxx.yyy.zzz
  *    BASEURL/Asset/js/xxxx.yyy.zzz
  *
  *   then
  *   we deliver the file from
  *       SESSION-DIR/{$www-user}-{$ff->project}-$ff->version}-{js|css}-compile/{filename}/PATH';
  *
  *   
  */
 
require_once 'Pman.php';

class Pman_Core_Asset extends Pman {
     
    var $types = array(
        'css' => 'text/css',
        'js' => 'text/javascript',
    );
    
    function getAuth()
    {
        return true;
    }
    
    
    function get($s='', $opts = Array())
    {
        $this->sessionState(0);
        
        $bits = explode('/', $s);
        
        if (empty($bits[0]) || empty($bits[1])  || !isset($this->types[$bits[0]])) {
            $this->jerr("invalid url");
        }
       
        $s = str_replace('/', '-', $bits[1]);
        
        $ui = posix_getpwuid(posix_geteuid());
        $ff = HTML_FlexyFramework::get();
        
        $compile = self::getCompileDir($bits[0], '', false);
        
        $fn = $compile . '/'. $s .'.'. $bits[0];
        
        if (!file_exists($fn)) {
            header('Content-Type: '. $this->types[$bits[0]]);
        
            echo "// compiled file not found = $fn";
            exit;
        }
        
        $supportsGzip = !empty($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) !== false;

        
        $last_modified_time = filemtime($fn);
        
        
        if (
            (
                isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
                strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time
            )
            ||
            (
                 isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
                trim($_SERVER['HTTP_IF_NONE_MATCH']) == md5($fn)
            )) { 
            header("HTTP/1.1 304 Not Modified");
            exit;
        }
        
        header('Content-Type: '. $this->types[$bits[0]]);
        
        
        header("Pragma: public");
        
        header('Cache-Control: max-age=2592000, public');
        header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 2592000));
        header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', $last_modified_time));
        header('Etag: '. md5($fn)); 
        
         if ( $supportsGzip ) {
            $content = gzencode( file_get_contents($fn) , 9);
            
            header('Content-Encoding: gzip');
            header('Vary: Accept-Encoding');
            header('Content-Length: '. strlen($content));
            
            echo $content;
            
        } else {
            
            
            $fh = fopen($fn,'r');
            fpassthru($fh);
            fclose($fh);
            $content = $data;
        }
        
        
        
        exit;
        
    }
    
    function post($s='') {
        if(!empty($_REQUEST['_clear_cache'])) {
            $this->clearCompiledFilesCache();
        }
        
        die('invalid');
    }
     
    static function getCompileDir($type, $module = '', $is_mkdir = true)
    {
        $ff = HTML_FlexyFramework::get();
        
        $ui = posix_getpwuid(posix_geteuid());
        
        $compile_dir = session_save_path() . "/";
        
        if (empty($module)) {
            $module = $ff->project . (isset($ff->appNameShort) ?  '_' . $ff->appNameShort : '');
        }
        
        
        switch($type) {
            case 'js':
            case 'css':
            case 'scss':
                $compile_dir .= implode("-", array(
                    $ui['name'],
                    $module,
                    $ff->version,
                    "{$type}compile"
                ));
                break;
            // template config?
            default:
                return false;
        }
        
        if (file_exists($compile_dir)) {
            return $compile_dir;
        }
        
        if(!$is_mkdir) {
            return false;
        }
        
        if(mkdir($compile_dir, 0700, true)) {
            return $compile_dir;
        }
        
        return false;
    }
    
    function clearCompiledFilesCache()
    {
        $au = $this->getAuthUser();
        if (!$au && !in_array($_SERVER['REMOTE_ADDR'] , array('127.0.0.1','::1'))) {
            $this->jerr("Cache can only be cleared by authenticated users");
        }
        
        require_once 'System.php';
        $ff = HTML_FlexyFramework::get();
        
        $mods = $this->modulesList();
        $mods[] = $ff->project; // Pman - this was the old format...
        $mods[] = ''; // Pman + appshortname..
        
        foreach ($mods as $module) {
            $compile_dir = $this->getCompileDir('js', $module, false);
        
            if(!empty($compile_dir)) {
                System::rm(array('-r', $compile_dir));
            }
            $compile_dir = $this->getCompileDir('css', $module, false);
        
            if(!empty($compile_dir)) {
                System::rm(array('-r', $compile_dir));
            }
        }
         
        $this->jok('DONE');
    }
}

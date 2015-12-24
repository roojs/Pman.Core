<?php
 
 /**
  * Generic cached assset server...
  *
  *
  * Also includes code to generate assets...
  *
  * Hydra generates a url for the compressed files as /Asset/css/xxxx.yyy.zzz
  *
  */
 
require_once 'Pman.php';

class Pman_Core_Asset extends Pman {
    
    
    var $types = array(
        'css' => 'text/css',
        'js' => 'text/javascript',
    );
    
    
    function get($s='')
    {
       
        $bits = explode('/', $s);
        
        if (empty($bits[0]) || empty($bits[1])  || !isset($this->types[$bits[0]])) {
            $this->jerr("invalid url");
        }
       
        $s = str_replace('/', '-', $bits[1]);
        
        $ui = posix_getpwuid(posix_geteuid());
        $ff = HTML_FlexyFramework::get();
        
        $compile = session_save_path() . '/' .
                $ui['name'] . '-' . $ff->project . '-' . $ff->version .  '-'. $bits[0] . 'compile';
     
        $fn = $compile . '/'. $s .'.'. $bits[0];
        
        
        
        
        if (!file_exists($fn)) {
            header('Content-Type: '. $this->types[$bits[0]]);
        
            echo "// compiled file not found = $fn";
            exit;
        }
        
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
        header('Content-Length: '. filesize($fn));
        header('Cache-Control: max-age=2592000, public');
        header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 2592000));
        header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', $last_modified_time));
        header('Etag: '. md5($fn)); 
        
        $fh = fopen($fn,'r');
        fpassthru($fh);
        fclose($fh);
        exit;
        
    }
    function post($s='') {
        die('invalid');
    }
     
    
}

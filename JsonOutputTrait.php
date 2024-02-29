<?php

trait Pman_Core_JsonOutputTrait {
    
    function jok($str)
    {
        if ($this->transObj ) {
            $this->transObj->query( connection_aborted() ? 'ROLLBACK' :  'COMMIT');
        }
        
        $cli = HTML_FlexyFramework::get()->cli;
        
        if ($cli) {
            echo "OK: " .$str . "\n";
            exit;
        }
        require_once 'Services/JSON.php';
        $json = new Services_JSON();
        
        $retHTML = isset($_SERVER['CONTENT_TYPE']) && 
                preg_match('#multipart/form-data#i', $_SERVER['CONTENT_TYPE']);
        
        if ($retHTML){
            if (isset($_REQUEST['returnHTML']) && $_REQUEST['returnHTML'] == 'NO') {
                $retHTML = false;
            }
        } else {
            $retHTML = isset($_REQUEST['returnHTML']) && $_REQUEST['returnHTML'] !='NO';
        }
        
        if ($retHTML) {
            header('Content-type: text/html');
            echo "<HTML><HEAD></HEAD><BODY>";
            // encode html characters so they can be read..
            echo  str_replace(array('<','>'), array('\u003c','\u003e'),
                        $json->encodeUnsafe(array('success'=> true, 'data' => $str)));
            echo "</BODY></HTML>";
            exit;
        }
        
        
        echo  $json->encode(array('success'=> true, 'data' => $str));
        
        exit;
    }
    
    
    function jerr($str, $errors=array(), $content_type = false)
    {
        if ($this->transObj) {
            $this->transObj->query('ROLLBACK');
        }
        
        return $this->jerror('ERROR', $str,$errors,$content_type);
    }
    
    function jerror($type, $str, $errors=array(), $content_type = false) // standard error reporting..
    {
        if ($type !== false) {
            $this->addEvent($type, false, $str);
        }
         
        $cli = HTML_FlexyFramework::get()->cli;
        if ($cli) {
            echo "ERROR: " .$str . "\n";
            exit;
        }
        
        
        if ($content_type == 'text/plain') {
            header('Content-Disposition: attachment; filename="error.txt"');
            header('Content-type: '. $content_type);
            echo "ERROR: " .$str . "\n";
            exit;
        } 
        
        require_once 'Services/JSON.php';
        $json = new Services_JSON();
        
        $retHTML = isset($_SERVER['CONTENT_TYPE']) && 
                preg_match('#multipart/form-data#i', $_SERVER['CONTENT_TYPE']);
        
        if ($retHTML){
            if (isset($_REQUEST['returnHTML']) && $_REQUEST['returnHTML'] == 'NO') {
                $retHTML = false;
            }
        } else {
            $retHTML = isset($_REQUEST['returnHTML']) && $_REQUEST['returnHTML'] !='NO';
        }
        
        if ($retHTML) {
            header('Content-type: text/html');
            echo "<HTML><HEAD></HEAD><BODY>";
            echo  $json->encodeUnsafe(array(
                    'success'=> false, 
                    'errorMsg' => $str,
                    'message' => $str, // compate with exeption / loadexception.

                    'errors' => $errors ? $errors : true, // used by forms to flag errors.
                    'authFailure' => !empty($errors['authFailure']),
                ));
            echo "</BODY></HTML>";
            exit;
        }
        
        if (isset($_REQUEST['_debug'])) {
            echo '<PRE>'.htmlspecialchars(print_r(array(
                'success'=> false, 
                'data'=> array(), 
                'errorMsg' => $str,
                'message' => $str, // compate with exeption / loadexception.
                'errors' => $errors ? $errors : true, // used by forms to flag errors.
                'authFailure' => !empty($errors['authFailure']),
            ),true));
            exit;
                
        }
        
        echo $json->encode(array(
            'success'=> false, 
            'data'=> array(), 
            'errorMsg' => $str,
            'message' => $str, // compate with exeption / loadexception.
            'errors' => $errors ? $errors : true, // used by forms to flag errors.
            'authFailure' => !empty($errors['authFailure']),
        ));
        
        exit;
        
    }
    
    function jdata($ar,$total=false, $extra=array(), $cachekey = false)
    {
        // should do mobile checking???
        if ($total == false) {
            $total = count($ar);
        }
        $extra=  $extra ? $extra : array();
        require_once 'Services/JSON.php';
        $json = new Services_JSON();
        
        $retHTML = isset($_SERVER['CONTENT_TYPE']) && 
                preg_match('#multipart/form-data#i', $_SERVER['CONTENT_TYPE']);
        
        if ($retHTML){
            if (isset($_REQUEST['returnHTML']) && $_REQUEST['returnHTML'] == 'NO') {
                $retHTML = false;
            }
        } else {
            $retHTML = isset($_REQUEST['returnHTML']) && $_REQUEST['returnHTML'] !='NO';
        }
        
        if ($retHTML) {
            
            header('Content-type: text/html');
            echo "<HTML><HEAD></HEAD><BODY>";
            // encode html characters so they can be read..
            echo  str_replace(array('<','>'), array('\u003c','\u003e'),
                        $json->encodeUnsafe(array('success' =>  true, 'total'=> $total, 'data' => $ar) + $extra));
            echo "</BODY></HTML>";
            exit;
        }
        
        
        // see if trimming will help...
        if (!empty($_REQUEST['_pman_short'])) {
            $nar = array();
            
            foreach($ar as $as) {
                $add = array();
                foreach($as as $k=>$v) {
                    if (is_string($v) && !strlen(trim($v))) {
                        continue;
                    }
                    $add[$k] = $v;
                }
                $nar[] = $add;
            }
            $ar = $nar;
              
        }
        
      
        $ret =  $json->encode(array('success' =>  true, 'total'=> $total, 'data' => $ar) + $extra);  
        
        if (!empty($cachekey)) {
            
            $fn = ini_get('session.save_path') . '/json-cache'.date('/Y/m/d').'.'. $cachekey . '.cache.json';
            if (!file_exists(dirname($fn))) {
                mkdir(dirname($fn), 0777,true);
            }
            file_put_contents($fn, $ret);
        }
        echo $ret;
        exit;
    }
    
    /** a daily cache **/
    function jdataCache($cachekey)
    {
        $fn = ini_get('session.save_path') . '/json-cache'.date('/Y/m/d').'.'. $cachekey . '.cache.json';
        if (file_exists($fn)) {
            header('Content-type: application/json');
            echo file_get_contents($fn);
            exit;
        }
        return false;
        
    }
}
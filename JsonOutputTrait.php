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
                        $this->jsencode(array('success'=> true, 'data' => $str), false));
            echo "</BODY></HTML>";
            exit;
        }
        
        
        echo  $this->jsencode(array('success'=> true, 'data' => $str),true);
        
        exit;
        
    }
    
    
      /**
     * ---------------- Standard JSON outputers. - used everywhere
     * JSON error - simple error with logging.
     * @see Pman::jerror
     */
    
    function jerr($str, $errors=array(), $content_type = false) // standard error reporting..
    {
        return $this->jerror('ERROR', $str,$errors,$content_type);
    }
    
    function jnotice($type, $str, $errors=array(), $content_type = false)
    {
        return $this->jerror('NOTICE-' . $type, $str, $errors, $content_type);
    }
    
     /**
     * jerrAuth: standard auth failure - with data that let's the UI know..
     */
    function jerrAuth()
    {
        $au = $this->authUser;
        if ($au) {
            // is it an authfailure?
            $this->jerror("LOGIN-NOPERM", "Permission denied to view this resource", array('authFailure' => true));
        }
        $this->jerror("LOGIN-NOAUTH", "Not authenticated", array('authFailure' => true));
    }
     
     
    /**
     * Recomended JSON error indicator
     *
     * 
     * @param string $type  - normally 'ERROR' - you can use this to track error types.
     * @param string $message - error message displayed to user.
     * @param array $errors - optioanl data to pass to front end.
     * @param string $content_type - use text/plain to return plan text - ?? not sure why...
     *
     */
    
    function jerror($type, $str, $errors=array(), $content_type = false) // standard error reporting..
    {
        if ($this->transObj) {
            $this->transObj->query('ROLLBACK');
        }
        
        $cli = HTML_FlexyFramework::get()->cli;
        if ($cli) {
            echo "ERROR: " .$str . "\n"; // print the error first, as DB might fail..
        }
        $pman = HTML_FlexyFramework::get();
        
       
        

        
        if ($type !== false  &&  empty($pman->nodatabase)) {
            
            if(!empty($errors)){
                DB_DataObject::factory('Events')->writeEventLogExtra($errors);
            }
            // various codes that are acceptable.
            // 
            if (!preg_match('/^(ERROR|NOTICE|LOG)/', $type )) {
                $type = 'ERROR-' . $type;
            }
            
            $this->addEvent($type, false, $str);
            
        }
         
        $cli = HTML_FlexyFramework::get()->cli;
        if ($cli) {
            exit(1); // cli --- exit code to stop shell execution if necessary.
        }
        
        
        if ($content_type == 'text/plain') {
            header('Content-Disposition: attachment; filename="error.txt"');
            header('Content-type: '. $content_type);
            echo "ERROR: " .$str . "\n";
            exit;
        } 
        
     // log all errors!!!
        
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
            echo  $this->jsencode(array(
                    'success'=> false, 
                    'errorMsg' => $str,
                    'message' => $str, // compate with exeption / loadexception.

                    'errors' => $errors ? $errors : true, // used by forms to flag errors.
                    'authFailure' => !empty($errors['authFailure']),
                ), false);
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
        
        echo $this->jsencode(array(
            'success'=> false, 
            'data'=> array(),
            'code' => $type,
            'errorMsg' => $str,
            'message' => $str, // compate with exeption / loadexception.
            'errors' => $errors ? $errors : true, // used by forms to flag errors.
            'authFailure' => !empty($errors['authFailure']),
        ),true);
        
        
        exit;
        
    }
    
     
   
    
    
    
    /**
     * output data for grids or tree
     * @ar {Array} ar Array of data
     * @total {Number|false} total number of records (or false to return count(ar)
     * @extra {Array} extra key value list of data to pass as extra data.
     * 
     */
    function jdata($ar,$total=false, $extra=array(), $cachekey = false)
    {
        // should do mobile checking???
        if ($total == false) {
            $total = count($ar);
        }
        $extra=  $extra ? $extra : array();
        
        
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
            echo "<HTML><HEAD></HEAD><BODY><![CDATA[";
            // encode html characters so they can be read..
            echo  str_replace(array('<','>'), array('\u003c','\u003e'),
                        $this->jsencode(array('success' =>  true, 'total'=> $total, 'data' => $ar) + $extra, false));
            echo "]]></BODY></HTML>";
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
        
      
        $ret =  $this->jsencode(array('success' =>  true, 'total'=> $total, 'data' => $ar) + $extra,true);  
        
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
    
     function jsencode($v, $header = false)
    {
        if ($header) {
            header("Content-type: text/javascript");
        }
        if (function_exists("json_encode")) {
            $ret=  json_encode($v);
            if ($ret !== false) {
                return $ret;
            }
        }
        require_once 'Services/JSON.php';
        $js = new Services_JSON();
        return $js->encodeUnsafe($v);
        
        
        
    }
    
}
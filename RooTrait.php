<?php

trait Pman_Core_RooTrait {
    
    var $validTables = false; 
    
    var $key;
    
    var $transObj = false;
    
    var $debugEnabled = true;
    
    function init() 
    {
        if (isset($this->_hasInit)) {
            return;
        }
        
        $this->_hasInit = true;
        
        $boot = HTML_FlexyFramework::get();
        
        $this->appName= $boot->appName;
        $this->appNameShort= $boot->appNameShort;
        $this->appModules= $boot->enable;
        $this->isDev = empty($boot->Pman['isDev']) ? false : $boot->Pman['isDev'];
        $this->appDisable = $boot->disable;
        $this->appDisabled = explode(',', $boot->disable);
        $this->version = $boot->version; 
        $this->uiConfig = empty($boot->Pman['uiConfig']) ? false : $boot->Pman['uiConfig']; 
        
        if (!empty($ff->Pman['local_autoauth']) && 
            ($_SERVER['SERVER_ADDR'] == '127.0.0.1') &&
            ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') 
        ) {
            $this->isDev = true;
        }
        
    }
    
    function checkDebug($req = false)
    {
        $req =  $req === false  ? $_REQUEST : $req;
        if (isset($req['_debug']) 
                && 
                $this->authUser
                &&
                (
                    (
                        method_exists($this->authUser,'canDebug')
                        &&
                        $this->authUser->canDebug()
                    )
                ||
                    (  
                    
                        method_exists($this->authUser,'groups') 
                        &&
                        is_a($this->authUser, 'Pman_Core_DataObjects_Person')
                        &&
                        in_array('Administrators', $this->authUser->groups('name'))
                    )
                )
                
            ){
            DB_DAtaObject::debuglevel((int)$req['_debug']);
        }
        
    }
    
    function checkDebugPost()
    {
        return (!empty($_GET['_post']) || !empty($_GET['_debug_post'])) && 
                    $this->authUser && 
                    method_exists($this->authUser,'groups') &&
                    in_array('Administrators', $this->authUser->groups('name')); 
        
    }
    
    function dataObject($tab)
    {
        if (is_array($this->validTables) &&  !in_array($tab, $this->validTables)) {
            $this->jerr("Invalid url - not listed in validTables");
        }
        
        $tab = str_replace('/', '',$tab); // basic protection??
        
        $x = DB_DataObject::factory($tab);
        
        if (!is_a($x, 'DB_DataObject')) {
            $this->jerr('invalid url - no dataobject');
        }
    
        return $x;
    }
    
    /*
     * From Pman.php
     */
    
    static $permitError = false;
    
    function onPearError($err)
    {
        static $reported = false;
        if ($reported) {
            return;
        }
        
        if (Pman::$permitError) {
             
            return;
            
        }
        
        $reported = true;
        $out = $err->toString();
        
        $ret = array();
        $n = 0;
        
        foreach($err->backtrace as $b) {
            $ret[] = @$b['file'] . '(' . @$b['line'] . ')@' .   @$b['class'] . '::' . @$b['function'];
            if ($n > 20) {
                break;
            }
            $n++;
        }
        //convert the huge backtrace into something that is readable..
        $out .= "\n" . implode("\n",  $ret);
     
        print_R($out);exit;
        
        $this->jerr($out);
        
    }
    
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
    
    function addEvent($act, $obj = false, $remarks = '') 
    {
        
        if (!empty(HTML_FlexyFramework::get()->Pman['disable_events'])) {
            return;
        }
        
        $au = $this->getAuthUser();
       
        $e = DB_DataObject::factory('Events');
        $e->init($act,$obj,$remarks); 
         
        $e->event_when = date('Y-m-d H:i:s');
        
        $eid = $e->insert();
        
        // fixme - this should be in onInsert..
        $wa = DB_DataObject::factory('core_watch');
        if (method_exists($wa,'notifyEvent')) {
            $wa->notifyEvent($e); // trigger any actions..
        }
        
        
        $e->onInsert(isset($_REQUEST) ? $_REQUEST : array() , $this);
        
       
        return $e;
        
    }
}

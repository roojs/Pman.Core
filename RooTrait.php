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
    
    function getAuthUser()
    {
        die('Get auth user is not implement.');
    }
}

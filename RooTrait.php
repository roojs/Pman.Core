<?php

trait Pman_Core_RooTrait {
    
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
    
    function checkDebugPost()
    {
        return (!empty($_GET['_post']) || !empty($_GET['_debug_post'])) && 
                    $this->authUser && 
                    method_exists($this->authUser,'groups') &&
                    in_array('Administrators', $this->authUser->groups('name')); 
    }
    
    function checkDebug($req = false)
    {
        /*
         * Not allow to doing this
         */
        return false;
    }
}

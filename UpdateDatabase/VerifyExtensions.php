<?php

require_once 'Pman.php';

class Pman_Core_UpdateDatabase_VerifyExtensions extends Pman
{
    static $cli_opts = array(

    );
    
    function getAuth()
    {
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            return true;
        }
        
        $this->getAuthUser();
        
        if(empty($this->authUser)) {
            return false;
        }
        
        return true;
    }
    
    function get($base, $opts = array())
    {
        $extensions = array();
        
        $ff = HTML_FlexyFramework::get();
        
        foreach($this->modulesList() as $m) {
            
            $fd = $ff->rootDir. "/Pman/$m/UpdateDatabase.php";
            
            if (!file_exists($fd)) {
                continue;
            }
            require_once $fd;
            $cls = new ReflectionClass('Pman_'. $m . '_UpdateDatabase');
            $props = $cls->getDefaultProperties();
            
            if(!empty($props['required_extensions'])) {
                $extensions = array_merge($extensions, $props['required_extensions']);
            }
        }
        
        $error = '';
        
        foreach ($extensions as $e){
            
            if(empty($e) || extension_loaded($e)) {
                continue;
            }
            
            $error .= "$e\n";
        }
        
        if(!empty($error)) {
            $this->jerror(false,$error);
        }
        
        $this->jok("DONE");
        
    }
}
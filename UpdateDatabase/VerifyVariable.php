<?php

require_once 'Pman.php';

class Pman_Core_UpdateDatabase_VerifyVariable extends Pman
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
        $requirements = array();
        
        $ff = HTML_FlexyFramework::get();
        
        foreach($this->modulesList() as $m) {
            
            $fd = $ff->rootDir. "/Pman/$m/UpdateDatabase.php";
            
            if (!file_exists($fd)) {
                continue;
            }
            
            require_once $fd;
            $cls = new ReflectionClass('Pman_'. $m . '_UpdateDatabase');
            $props = $cls->getDefaultProperties();
            
            if(!empty($props['required_variable'])) {
                $requirements = array_merge($requirements, $props['required_variable']);
            }
        }
        
        $error = '';
        
        foreach ($extensions as $e){
            
            if(extension_loaded($e)) {
                continue;
            }
            
            $error .= "$e\n";
        }
        
        if(!empty($error)) {
            $this->jerr($error);
        }
        
        $this->jok("DONE");
        
    }
}
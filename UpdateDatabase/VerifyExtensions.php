<?php

require_once 'Pman.php';

class Pman_Core_UpdateDatabase_VerifyExtensions extends Pman
{
    static $cli_opts = array(

    );
    
    function getAuth()
    {
        return true;
    }
    
    function get($base, $opts = array())
    {
        $extensions = $error = array();
        
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
        
        foreach ($extensions as $e){
            
            if(extension_loaded($e)) {
                continue;
            }
            
            $error[] = "Error: Please install php extension: {$e}";
        }
        
        if(!empty($error)) {
            $this->jerr($error);
        }
        
        $this->jok("DONE");
        
    }
}
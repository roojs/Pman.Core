<?php

require_once 'Pman.php';

class Pman_Core_UpdateDatabase_VerifyConfig extends Pman
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
            
            if(!empty($props['required_config'])) {
                $requirements = array_merge($requirements, $props['required_config']);
            }
        }
        
        $error = array();
        
        foreach ($requirements as $k => $v){
            
            if(empty($ff->{$k})){
                $error[] = "Missing Config: {$k} Config";
                continue;
            }
            
            foreach ($v as $r){
                
                if(isset($ff->{$k}[$r])){
                    continue;
                }
                
                $error[] = "Missing Config: {$k} - {$r}";
            }
        }
        
        return $error;
    }
}
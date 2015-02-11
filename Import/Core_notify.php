<?php

require_once 'Pman/Roo.php';

class Pman_Core_Import_Core_notify extends Pman_Roo 
{
    static $cli_desc = "Create Core.NotifyType from core notify"; 
    
    static $cli_opts = array(
        
    );
    
    function getAuth()
    {
        if (!HTML_FlexyFramework::get()->cli) {
            return false;
        }
        
        return true;
        
    }

    var $defaults = array();
    
    function get()
    {   
        
        $this->transObj = DB_DataObject::Factory('core_enum');
        
        $this->transObj->query('BEGIN');
        
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'onPearError'));
        
        $this->modules = $this->modulesList();
        
        $this->etype();
        
        $this->defaults();
        
        foreach ($this->defaults as $k => $v){
            $enum = DB_DataObject::factory('core_enum');
            $enum->setFrom(array(
                'etype' => $this->etype->name,
                'name' => $k,
                'active' => 1
            ));
            
            if($enum->find(true)){
                continue;
            }
            
            $enum->display_name = $v;
            $enum->insert();
        }
        
        $notify = DB_DataObject::factory('core_notify');
        $notify->selectAdd();
        $notify->selectAdd("
            DISTINCT(evtype) AS evtype
        ");
        
        $types = $notify->fetchAll();
        
        foreach ($types as $t){
            $enum = DB_DataObject::factory('core_enum');
            $enum->setFrom(array(
                'etype' => $this->etype->name,
                'name' => $t,
                'active' => 1
            ));
            
            if($enum->find(true)){
                continue;
            }
            
            $enum->display_name = $t;
            $enum->insert();
        }
        
        $this->jok('DONE');
        
        
    }
    
    function etype()
    {
        $this->etype = DB_DataObject::factory('core_enum');
        $this->etype->setFrom(array(
            'etype' => '',
            'name' => 'Core.NotifyType',
            'display_name' => 'Core.NotifyType',
            'active' => 1
        ));
        
        if($this->etype->find(true)){
            return;
        }
        
        $this->etype->insert();
    }
    
    function defaults()
    {
        foreach ($this->modules as $m){
            $file = $this->rootDir. "/Pman/$m/Core.NotifyType.json";
            
            if(!file_exists($file)){
                continue;
            }
            
            $this->defaults = array_merge($this->defaults, json_decode(file_get_contents($file), true)) ;
        }
        
    }
    
    function log($str)
    {
        echo "$str \n";
    }
}
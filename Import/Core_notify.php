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

    function get()
    {   
        
        $this->transObj = DB_DataObject::Factory('core_enum');
        
        $this->transObj->query('BEGIN');
        
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'onPearError'));
        
        $this->etype();
        
        
        
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
}
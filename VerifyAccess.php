<?php

require_once 'Pman.php';

class Pman_Core_VerifyAccess extends Pman
{
    /*
     * This is a public page
     */
    function getAuth() 
    {
        return true;
    }
    
    function get($id)
    {
        @list($id, $key) = explode('/', $id);
        
        $ff = HTML_FlexyFramework::get();
        
        $core_person = DB_DataObject::factory('core_person');
        $core_ip_access = DB_DataObject::factory('core_ip_access');
        
        if(
                empty($ff->Pman['ip_management']) || 
                empty($ff->Pman['XMPP']) ||
                empty($ff->Pman['XMPP']['to']) ||
                !$core_person->get('email', $ff->Pman['XMPP']['to']) ||
                empty($id) ||
                empty($key) ||
                !$core_ip_access->get($id) ||
                $core_ip_access->authorized_key != $key
        ) {
            $this->jerr('Invalid URL');
        }
        
        
        
        
        
        
    }
    
}

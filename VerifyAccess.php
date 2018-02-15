<?php

require_once 'Pman.php';

class Pman_Core_VerifyAccess extends Pman
{
    var $masterTemplate = 'master-verify-ip-access.html';
    
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
        
        if(empty($id) || empty($key)){
            $this->jerr('Invalid URL');
        }
        
        $core_ip_access = DB_DataObject::factory('core_ip_access');
        
        if(!$core_ip_access->get($id) || $core_ip_access->authorized_key != $key){
            $this->jerr('This URL is broken');
        }
        
        $ff = HTML_FlexyFramework::get();
        
        $core_person = DB_DataObject::factory('core_person');
        
        
        $ff->Pman['XMPP']['to'] = 'edward@roojs.com'; // testing...
        
        if(
                empty($id) ||
                empty($key) ||
                empty($ff->Pman['ip_management']) || 
                empty($ff->Pman['XMPP']) ||
                empty($ff->Pman['XMPP']['to']) ||
                !$core_person->get('email', $ff->Pman['XMPP']['to']) ||
                !$core_ip_access->get($id) ||
                $core_ip_access->authorized_key != $key
        ) {
            $this->jerr('Invalid URL');
        }
        
        return;
        
    }
    
}

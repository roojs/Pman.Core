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
        
        if(empty($ff->Pman['ip_management']) || empty($ff->Pman['XMPP']) || empty($ff->Pman['XMPP']['to'])) {
            $this->jerr('[System Error] This site does not using IP management');
        }
        
        $ff->Pman['XMPP']['to'] = 'edward@roojs.com'; // testing...
        
        $core_person = DB_DataObject::factory('core_person');
        
        if(!$core_person->get('email', $ff->Pman['XMPP']['to'])) {
            $this->jerr('[System Error] Please setup the XMPP correctly');
        }
        
        return;
        
    }
    
    function post()
    {
        $core_ip_access = DB_DataObject::factory('core_ip_access');
        
        if(
                empty($_REQUEST['id']) || 
                empty($_REQUEST['authorized_key']) ||
                !$core_ip_access->get($_REQUEST['id']) ||
                $core_ip_access->authorized_key != $_REQUEST['authorized_key']
        ){        	   
            $this->jerr('Invalid URL');
        }
        
        if(!empty($_REQUEST['_to_data'])){
            $this->jdata($core_ip_access->toArray());
        }
        
        $o = clone($core_ip_access);
        
        $core_ip_access->status = empty($_REQUEST['status']) ? 0 : $_REQUEST['status'];
        
        if($core_ip_access->status == -2){
            
            $core_ip_access->setFrom(array(
                'status' => 1,
                'expire_dt' => date('Y-m-d', strtotime($_REQUEST['expire_dt']))
            ));
        }
        
        $core_ip_access->update($o);
        
    }
    
    
}

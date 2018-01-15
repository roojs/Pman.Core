<?php

class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_ip_access extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_ip_access';
    public $id;
    public $ip;
    public $created_dt;
    public $status;
    public $authorized_by;
    public $authorized_key;
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function applyFilters($q, $au)
    {
        
    }
    
    function sendXMPP()
    {
        $ff = HTML_FlexyFramework::get();
        
        if(
                empty($ff->Pman['ip_management']) || 
                empty($ff->Pman['XMPP']) ||
                empty($ff->Pman['XMPP']['username']) ||
                empty($ff->Pman['XMPP']['password']) ||
                empty($ff->Pman['XMPP']['to'])
        ) {
            return;
        }
    }
    
    
}

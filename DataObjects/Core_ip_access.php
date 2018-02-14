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
    public $email;
    public $expire_dt;
    public $user_agent;

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
        
        require_once 'Net/XMPP.php';
            
        $conn = new Net_XMPP('talk.google.com', 5222, $ff->Pman['XMPP']['username'], $ff->Pman['XMPP']['password'], 'xmpphp', 'gmail.com', $printlog=false, $loglevel=Net_XMPP_Log::LEVEL_VERBOSE);

        $url = "{$ff->baseURL}/Core/VerifyAccess/{$this->id}/{$this->authorized_key}";
        
        try {
            $conn->connect();
            $conn->processUntil('session_start');
            $conn->presence();
            $conn->message($ff->Pman['XMPP']['to'], "
                New IP Login Required\n
                {$url}
            ");
            $conn->disconnect();
            
        } catch(XMPPHP_Exception $e) {
            $ff->page->jerr($e->getMessage());
        }

        return;
        
    }
    
    
}

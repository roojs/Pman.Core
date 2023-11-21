<?php
/**
 * Table Definition for core_notify_sender
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_notify_sender extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_notify_sender';    // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $email;
    public $poolname;
    public $is_active;
    public $priority;
    
    
    
    function emailAddrToSender($from_addr , $notify)
    {
        
        $from = $from_addr->mailbox . '@' . $from_addr->host;

        $ns = DB_DataObject::Factory($this->tableName());
        $ns->setFrom(array(
            'email' => $from,
            'poolname' => $notify->tableName()
        ));
        if (!$ns->find(true)) {
            $ns->is_active = 1;
            $ns->priority = 1;
            $ns->insert();
            return false;
        }
        return $ns;
    }
    
    
    function emailToAddr($email)
    {
        require_once 'Mail/RFC822.php';
        $parser = new Mail_RFC822();
        $addresses = $parser->parseAddressList( $email['headers']['From'], 'localhost', false);
        if (is_a($addresses, 'PEAR_Error')) {
            return false;
        }
        return $addresses[0];
        //print_r($email['headers']['From']);         print_R($from_addr);exit;

      
    }
    
    
    function filterEmail($email, $notify)
    {
        //
        if (empty($email['headers']['From']) || empty($notify)) {
            return $email;
        }
        $bits = explode('@', $notify->to_email);
        $to_dom = DB_DataObject::factory('core_domain')->loadOrCreate($bits[1]);
        
        $from_addr = $this->emailToAddr($email);
        
        $ns = $this->emailAddrToSender($from_addr, $notify);
        if ($ns == false) {
            return $email;
        }
        // is it blacklisted?
        $bl = DB_DAtaObject::Factory('core_notify_sender_blacklist');
        $bl->setFrom(array(
                'sender_id'=> $ns->id,
                'domain_id' => $to_dom->id
        ));
        if (!$bl->count()) {
            return $email;
        }
        // it's blacklisted..
        // try finding alternative.
        $bl = DB_DAtaObject::Factory('core_notify_sender_blacklist');
        $bl->domain_id = $to_dom->id;
        $bad_ids = $bl->fetchAll('sender_id');
        
        $ns = DB_DataObject::Factory($this->tableName());
        $ns->setFrom(array(
             'poolname' => $notify->tableName(),
            'is_active' => 1,
        ));
        $ns->whereAddIn('!id', $bad_ids, 'int');
        if (!$ns->count()){
            return $email; // no alternative available
        }
        $ns->limit(1);
        $ns->find(true);
        $email['headers']['From'] = $from_addr->personal . ' <' . $ns->email .'>';
        
        return $email;
        
        //check blacklist for 
        
         
        
    }
    
    function checkSmtpResponse($email, $notify, $errmsg)
    {
        $bl = DB_DataObject::factory('core_notify_sender_blacklist');
        if (!$bl->messageIsBlacklisted($errmsg)) {
            return; // ok.
        }
        // create a record.
        
        $bits = explode('@', $notify->to_email);
        $to_dom = DB_DataObject::factory('core_domain')->loadOrCreate($bits[1]);
        
        
        
        $from_addr = $this->emailToAddr($email);
        $ns = $this->emailAddrToSender($from_addr, $notify);
      
        $bl->setFrom(array(
            'sender_id' => $ns->id,
            'domain_id' => $to_dom->id,
        ));
        if ($bl->count()) {
            return;
        }
        $bl->error_str = $errmsg;
        $bl->added_dt = $bl->sqlValue('NOW()');
        $bl->insert();
        
    }
    
}
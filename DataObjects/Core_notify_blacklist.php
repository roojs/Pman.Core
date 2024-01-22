<?php
/**
 * Table Definition for core_notify_blacklist
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_notify_blacklist extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_notify_blacklist';    // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $server_id;
    public $domain_id;
    public $error_msg;
    public $added_dt;
    
    
    function messageIsBlacklisted($err)
    {
        $match = array(
            '5.7.0 DT:SPM', // 163.com
            '5.7.1 H:DYNB', // some other black list
            'on our block list',  // live.com
            'spameatingmonkey.net', // spameatingmonkey.net (users)
            'sender is listed on the block', // korian?
            'proofpoint.com', // another spam detecotr
            'cloud-security.net', // another spam protector..
            'spam complain',
            'ANTISPAM',
            'probability of spam',
            'block list by spam', // spamhaus
            'blocked using Spamhaus',
            'spamhaus.org',  // www zen rbl...
            'detected as Spam',
            'poor reputation',
            'AntiSpam',
            'ip address in rbl',
            'IP address blacklisted',
            'spamauditor.org',
            'detect spam',
            'message as spam',
            'DNSBL:RBL',
            'SpamHaus SBL-XBL',
            'blocked by sbl-xbl.spam',
            'Sophos Anti Spam Engine',
            'spam filters',
            
        );
        foreach($match as $str) {
            if (strpos($err, $str) !== false) {
                return true;
            }
        }
        return false;
    }
    
    // delete blacklists older than 1 week (and try again)
    function prune()
    {
        $this->query("
            DELETE FROM {$this->tableName()} where added_dt < NOW()  - INTERVAL 1 WEEK
        ");
            
    }
    
}
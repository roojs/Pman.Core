<?php
/**
 * Table Definition for core_notify_sender_blacklist
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_notify_sender_blacklist extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_notify_sender_blacklist';    // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $sender_id;
    public $domain_id;
    public $error_msg;
    public $added_dt;
     
    function messageIsBlacklisted($err)
    {
        $match = array(
        
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
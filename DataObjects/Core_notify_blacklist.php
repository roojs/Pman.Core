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
    
    
    
    
}
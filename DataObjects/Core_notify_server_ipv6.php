<?php
/**
 * Table Definition for core_notify_server_ipv6
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_notify_server_ipv6 extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_notify_server_ipv6';    // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $range_id;                        // int(11)  not_null
    public $ipv6_addr;                       // varchar(255)  not_null
    public $domain_id;                       // int(11)  not_null
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    function keys() { return array('id'); }
    function sequenceKey() { return array('id', true); }
}

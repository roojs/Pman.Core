<?php
/**
 * Table Definition for core_notify_server_ipv6_range
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_notify_server_ipv6_range extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_notify_server_ipv6_range';    // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $server_id;                       // int(11)  not_null
    public $ipv6_range_from;                 // varchar(255)  not_null
    public $ipv6_range_to;                   // varchar(255)  not_null
    public $ipv6_ptr;                        // varchar(255)  not_null
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    function keys() { return array('id'); }
    function sequenceKey() { return array('id', true); }
}

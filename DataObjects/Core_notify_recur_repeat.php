<?php
/**
 * Table Definition for core_notify_recur_repeat
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_notify_recur_repeat extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_notify_recur_repeat';    // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $freq;                            // string(8)  not_null
    public $freq_day;                        // blob(65535)  not_null blob
    public $freq_hour;                       // blob(65535)  not_null blob
    public $recur_id;                        // int(11)  not_null

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

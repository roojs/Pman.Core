<?php
/**
 * Table Definition for core_notify_recur
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_notify_recur extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_notify_recur';    // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $person_id;                       // int(11)  not_null
    public $dtstart;                         // datetime(19)  not_null binary
    public $dtend;                           // datetime(19)  not_null binary
    public $tz;                              // real(6)  not_null
    public $updated_dt;                      // datetime(19)  not_null binary
    public $last_applied_dt;                 // datetime(19)  not_null binary

    public $freq; //  varchar(8) NOT NULL;
    public $freq_day; // text NOT NULL;
    public $freq_hour; // text 
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function repeats()
    {
        
    }
    
    function notifytimes()
    {
        // make a list of datetimes when notifies need 
        $ar = 
        
        
        
        
        
    }
    
    
    
}

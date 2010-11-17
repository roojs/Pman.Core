<?php
/**
 * Table Definition for Events
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Events extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Events';                          // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $person_name;                     // string(128)  
    public $event_when;                      // datetime(19)  binary
    public $action;                          // string(32)  
    public $ipaddr;                          // string(16)  
    public $on_id;                           // int(11)  
    public $on_table;                        // string(64)  
    public $person_id;                       // int(11)  
    public $remarks;                         // blob(65535)  blob

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    /**
     * check who is trying to access this. false == access denied..
     */
    function checkPerm($lvl, $au) 
    {
        return $lvl == 'S' && $au->hasPerm("Admin.Admin_Tab", $lvl);
    } 
    
    function init($act, $obj, $remarks)
    {
        $pg = HTML_FlexyFramework::get()->page;
        $au = $pg->getAuthUser();
         
        $this->person_name = $au ? $au->name : '';
        $this->person_id = $au ? $au->id : '';
        $this->ipaddr = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : 'cli';
        $this->action = $act;
        $this->on_table = $obj ? $obj->tableName() : '';
        $pk = $obj ? $obj->keys()  : false;
        $this->on_id  = $obj && $pk ? $obj->{$pk[0]}: 0;
        $this->remarks = $remarks;
    }
}

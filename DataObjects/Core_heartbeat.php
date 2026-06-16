<?php
/**
 * Table Definition for core_heartbeat - tracks server heartbeat status
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_heartbeat extends DB_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */
    public $__table = 'core_heartbeat';                       // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $hostname;                        // string(255)  not_null
    public $last_update_dt;                  // datetime  not_null
   
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    function checkPerm($lvl, $au, $req=null)
    {
        if (!$au) {
            return false;
        }
        return true;
    }

    /**
     * Check and update heartbeat for a hostname
     * @param string $hostname The hostname to check/update
     * @return string "OK - HEARTBEAT WORKING" or "FAILED"
     */
    function hostCheck($hostname)
    {
        $this->hostname = $hostname;
        
        if (!$this->find(true)) {
            // Insert new record
            $this->last_update_dt = date('Y-m-d H:i:s');
            if (!$this->insert()) {
                return "FAILED";
            }
            return "OK - HEARTBEAT WORKING";
        }
        
        // Check if heartbeat is recent (within 30 seconds)
        $lastUpdate = strtotime($this->last_update_dt);
        if ((time() - $lastUpdate) < 30) {
            return "OK - HEARTBEAT WORKING";
        }
        
        // Update existing record
        $old = clone($this);
        $this->last_update_dt = date('Y-m-d H:i:s');
        if (!$this->update($old)) {
            return "FAILED";
        }
        return "OK - HEARTBEAT WORKING";
    }
}

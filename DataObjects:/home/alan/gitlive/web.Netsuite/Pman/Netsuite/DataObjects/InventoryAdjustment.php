<?php
/**
 * Table Definition for InventoryAdjustment
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_InventoryAdjustment extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'InventoryAdjustment';             // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $account;                         // int(11)  
    public $adjLocation;                     // int(11)  
    public $class;                           // int(11)  
    public $createdDate;                     // datetime(19)  binary
    public $customForm;                      // int(11)  
    public $customer;                        // int(11)  
    public $department;                      // int(11)  
    public $estimatedTotalValue;             // real(14)  
    public $externalId;                      // string(255)  
    public $lastModifiedDate;                // datetime(19)  binary
    public $location;                        // int(11)  
    public $memo;                            // string(255)  
    public $postingPeriod;                   // int(11)  
    public $subsidiary;                      // int(11)  
    public $tranDate;                        // datetime(19)  binary
    public $tranId;                          // string(255)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_InventoryAdjustment',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

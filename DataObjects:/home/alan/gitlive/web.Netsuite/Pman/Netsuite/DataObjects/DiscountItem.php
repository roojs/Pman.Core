<?php
/**
 * Table Definition for DiscountItem
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_DiscountItem extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'DiscountItem';                    // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $account;                         // int(11)  
    public $availableToPartners;             // int(4)  
    public $class;                           // int(11)  
    public $createdDate;                     // datetime(19)  binary
    public $customForm;                      // int(11)  
    public $deferredRevenueAccount;          // int(11)  
    public $department;                      // int(11)  
    public $description;                     // string(255)  
    public $displayName;                     // string(255)  
    public $expenseAccount;                  // int(11)  
    public $externalId;                      // string(255)  
    public $includeChildren;                 // int(4)  
    public $incomeAccount;                   // int(11)  
    public $isInactive;                      // int(4)  
    public $isPreTax;                        // int(4)  
    public $issueProduct;                    // int(11)  
    public $itemId;                          // string(255)  
    public $lastModifiedDate;                // datetime(19)  binary
    public $location;                        // int(11)  
    public $nonPosting;                      // int(4)  
    public $parent;                          // int(11)  
    public $rate;                            // string(255)  
    public $revRecSchedule;                  // int(11)  
    public $salesTaxCode;                    // int(11)  
    public $taxSchedule;                     // int(11)  
    public $upcCode;                         // string(255)  
    public $vendorName;                      // string(255)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_DiscountItem',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

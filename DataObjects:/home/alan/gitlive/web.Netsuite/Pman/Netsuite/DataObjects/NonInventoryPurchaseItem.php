<?php
/**
 * Table Definition for NonInventoryPurchaseItem
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_NonInventoryPurchaseItem extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'NonInventoryPurchaseItem';        // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $amortizationPeriod;              // int(11)  
    public $amortizationTemplate;            // int(11)  
    public $availableToPartners;             // int(4)  
    public $class;                           // int(11)  
    public $cost;                            // real(14)  
    public $costUnits;                       // string(255)  
    public $createdDate;                     // datetime(19)  binary
    public $currency;                        // string(255)  
    public $customForm;                      // int(11)  
    public $deferralAccount;                 // int(11)  
    public $department;                      // int(11)  
    public $displayName;                     // string(255)  
    public $expenseAccount;                  // int(11)  
    public $externalId;                      // string(255)  
    public $includeChildren;                 // int(4)  
    public $isFulfillable;                   // int(4)  
    public $isInactive;                      // int(4)  
    public $isTaxable;                       // int(4)  
    public $issueProduct;                    // int(11)  
    public $itemId;                          // string(255)  
    public $lastModifiedDate;                // datetime(19)  binary
    public $location;                        // int(11)  
    public $matrixType;                      // string(255)  
    public $parent;                          // int(11)  
    public $purchaseDescription;             // string(255)  
    public $purchaseTaxCode;                 // int(11)  
    public $purchaseUnit;                    // int(11)  
    public $residual;                        // string(255)  
    public $salesTaxCode;                    // int(11)  
    public $taxSchedule;                     // int(11)  
    public $unitsType;                       // int(11)  
    public $upcCode;                         // string(255)  
    public $vendor;                          // int(11)  
    public $vendorName;                      // string(255)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_NonInventoryPurchaseItem',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

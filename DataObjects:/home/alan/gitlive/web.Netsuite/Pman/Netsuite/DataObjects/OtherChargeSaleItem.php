<?php
/**
 * Table Definition for OtherChargeSaleItem
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_OtherChargeSaleItem extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'OtherChargeSaleItem';             // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $availableToPartners;             // int(4)  
    public $billingSchedule;                 // int(11)  
    public $class;                           // int(11)  
    public $costEstimate;                    // real(14)  
    public $costEstimateType;                // string(255)  
    public $costEstimateUnits;               // string(255)  
    public $createdDate;                     // datetime(19)  binary
    public $customForm;                      // int(11)  
    public $deferredRevenueAccount;          // int(11)  
    public $department;                      // int(11)  
    public $displayName;                     // string(255)  
    public $enforceMinQtyInternally;         // int(4)  
    public $externalId;                      // string(255)  
    public $includeChildren;                 // int(4)  
    public $incomeAccount;                   // int(11)  
    public $isFulfillable;                   // int(4)  
    public $isGcoCompliant;                  // int(4)  
    public $isInactive;                      // int(4)  
    public $isOnline;                        // int(4)  
    public $isTaxable;                       // int(4)  
    public $issueProduct;                    // int(11)  
    public $itemId;                          // string(255)  
    public $lastModifiedDate;                // datetime(19)  binary
    public $location;                        // int(11)  
    public $matrixType;                      // string(255)  
    public $minimumQuantity;                 // int(11)  
    public $minimumQuantityUnits;            // string(255)  
    public $offerSupport;                    // int(4)  
    public $overallQuantityPricingType;      // string(255)  
    public $parent;                          // int(11)  
    public $pricesIncludeTax;                // int(4)  
    public $pricingGroup;                    // int(11)  
    public $purchaseTaxCode;                 // int(11)  
    public $quantityPricingSchedule;         // int(11)  
    public $rate;                            // real(14)  
    public $revRecSchedule;                  // int(11)  
    public $saleUnit;                        // int(11)  
    public $salesDescription;                // string(255)  
    public $salesTaxCode;                    // int(11)  
    public $softDescriptor;                  // string(255)  
    public $taxSchedule;                     // int(11)  
    public $unitsType;                       // int(11)  
    public $upcCode;                         // string(255)  
    public $useMarginalRates;                // int(4)  
    public $vsoeDeferral;                    // string(255)  
    public $vsoeDelivered;                   // int(4)  
    public $vsoePermitDiscount;              // string(255)  
    public $vsoePrice;                       // real(14)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_OtherChargeSaleItem',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

<?php
/**
 * Table Definition for SalesOrderItem
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_SalesOrderItem extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'SalesOrderItem';                  // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $altSalesAmt;                     // real(14)  
    public $amount;                          // real(14)  
    public $billingSchedule;                 // int(11)  
    public $class;                           // int(11)  
    public $commitInventory;                 // string(255)  
    public $costEstimate;                    // real(14)  
    public $costEstimateType;                // string(255)  
    public $createPo;                        // string(255)  
    public $createWo;                        // int(4)  
    public $createdPo;                       // int(11)  
    public $deferRevRec;                     // int(4)  
    public $department;                      // int(11)  
    public $description;                     // string(255)  
    public $fromJob;                         // int(4)  
    public $giftCertFrom;                    // string(255)  
    public $giftCertMessage;                 // string(255)  
    public $giftCertNumber;                  // string(255)  
    public $giftCertRecipientEmail;          // string(255)  
    public $giftCertRecipientName;           // string(255)  
    public $grossAmt;                        // real(14)  
    public $isClosed;                        // int(4)  
    public $isEstimate;                      // int(4)  
    public $isTaxable;                       // int(4)  
    public $item;                            // int(11)  
    public $itemIsFulfilled;                 // int(4)  
    public $job;                             // int(11)  
    public $licenseCode;                     // string(255)  
    public $line;                            // int(11)  
    public $location;                        // int(11)  
    public $percentComplete;                 // real(14)  
    public $poCurrency;                      // string(255)  
    public $poRate;                          // real(14)  
    public $poVendor;                        // int(11)  
    public $price;                           // int(11)  
    public $quantity;                        // real(14)  
    public $quantityAvailable;               // real(14)  
    public $quantityBackOrdered;             // real(14)  
    public $quantityBilled;                  // real(14)  
    public $quantityCommitted;               // real(14)  
    public $quantityFulfilled;               // real(14)  
    public $quantityOnHand;                  // real(14)  
    public $quantityPacked;                  // real(14)  
    public $quantityPicked;                  // real(14)  
    public $rate;                            // string(255)  
    public $revRecEndDate;                   // datetime(19)  binary
    public $revRecSchedule;                  // int(11)  
    public $revRecStartDate;                 // datetime(19)  binary
    public $revRecTermInMonths;              // int(11)  
    public $serialNumbers;                   // string(255)  
    public $shipAddress;                     // int(11)  
    public $shipGroup;                       // int(11)  
    public $shipMethod;                      // int(11)  
    public $tax1Amt;                         // real(14)  
    public $taxCode;                         // int(11)  
    public $taxRate1;                        // real(14)  
    public $taxRate2;                        // real(14)  
    public $units;                           // int(11)  
    public $vsoeAllocation;                  // real(14)  
    public $vsoeAmount;                      // real(14)  
    public $vsoeDeferral;                    // string(255)  
    public $vsoeDelivered;                   // int(4)  
    public $vsoePermitDiscount;              // string(255)  
    public $vsoePrice;                       // real(14)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_SalesOrderItem',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

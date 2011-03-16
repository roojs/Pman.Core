<?php
/**
 * Table Definition for SalesOrder
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_SalesOrder extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'SalesOrder';                      // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $actualShipDate;                  // datetime(19)  binary
    public $altHandlingCost;                 // real(14)  
    public $altSalesTotal;                   // real(14)  
    public $altShippingCost;                 // real(14)  
    public $authCode;                        // string(255)  
    public $balance;                         // real(14)  
    public $billAddress;                     // string(255)  
    public $billAddressList;                 // int(11)  
    public $billingSchedule;                 // int(11)  
    public $ccApproved;                      // int(4)  
    public $ccAvsStreetMatch;                // string(255)  
    public $ccAvsZipMatch;                   // string(255)  
    public $ccExpireDate;                    // datetime(19)  binary
    public $ccName;                          // string(255)  
    public $ccNumber;                        // string(255)  
    public $ccSecurityCode;                  // string(255)  
    public $ccSecurityCodeMatch;             // string(255)  
    public $ccStreet;                        // string(255)  
    public $ccZipCode;                       // string(255)  
    public $class;                           // int(11)  
    public $contribPct;                      // string(255)  
    public $createdDate;                     // datetime(19)  binary
    public $createdFrom;                     // int(11)  
    public $creditCard;                      // int(11)  
    public $creditCardProcessor;             // int(11)  
    public $currencyName;                    // string(255)  
    public $customForm;                      // int(11)  
    public $debitCardIssueNo;                // string(255)  
    public $deferredRevenue;                 // real(14)  
    public $department;                      // int(11)  
    public $discountItem;                    // int(11)  
    public $discountRate;                    // string(255)  
    public $discountTotal;                   // real(14)  
    public $email;                           // string(255)  
    public $endDate;                         // datetime(19)  binary
    public $entity;                          // int(11)  
    public $estGrossProfit;                  // real(14)  
    public $estGrossProfitPercent;           // real(14)  
    public $exchangeRate;                    // real(14)  
    public $excludeCommission;               // int(4)  
    public $externalId;                      // string(255)  
    public $fax;                             // string(255)  
    public $fob;                             // string(255)  
    public $getAuth;                         // int(4)  
    public $giftCertApplied;                 // real(14)  
    public $handlingCost;                    // real(14)  
    public $handlingTax1Rate;                // real(14)  
    public $handlingTax2Rate;                // string(255)  
    public $handlingTaxCode;                 // int(11)  
    public $ignoreAvs;                       // int(4)  
    public $internalId;                      // string(255)  
    public $isMultiShipTo;                   // int(4)  
    public $isTaxable;                       // int(4)  
    public $job;                             // int(11)  
    public $lastModifiedDate;                // datetime(19)  binary
    public $leadSource;                      // int(11)  
    public $linkedTrackingNumbers;           // string(255)  
    public $location;                        // int(11)  
    public $memo;                            // string(255)  
    public $message;                         // string(255)  
    public $messageSel;                      // int(11)  
    public $opportunity;                     // int(11)  
    public $orderStatus;                     // string(255)  
    public $otherRefNum;                     // string(255)  
    public $partner;                         // int(11)  
    public $payPalStatus;                    // string(255)  
    public $payPalTranId;                    // string(255)  
    public $paymentEventDate;                // datetime(19)  binary
    public $paymentEventHoldReason;          // string(255)  
    public $paymentEventResult;              // string(255)  
    public $paymentEventType;                // string(255)  
    public $paymentEventUpdatedBy;           // string(255)  
    public $paymentMethod;                   // int(11)  
    public $paypalAuthId;                    // string(255)  
    public $paypalProcess;                   // int(4)  
    public $pnRefNum;                        // string(255)  
    public $promoCode;                       // int(11)  
    public $recognizedRevenue;               // real(14)  
    public $revCommitStatus;                 // string(255)  
    public $revRecEndDate;                   // datetime(19)  binary
    public $revRecOnRevCommitment;           // int(4)  
    public $revRecSchedule;                  // int(11)  
    public $revRecStartDate;                 // datetime(19)  binary
    public $revenueStatus;                   // string(255)  
    public $salesEffectiveDate;              // datetime(19)  binary
    public $salesGroup;                      // int(11)  
    public $salesRep;                        // int(11)  
    public $shipAddress;                     // string(255)  
    public $shipAddressList;                 // int(11)  
    public $shipComplete;                    // int(4)  
    public $shipDate;                        // datetime(19)  binary
    public $shipMethod;                      // int(11)  
    public $shippingCost;                    // real(14)  
    public $shippingTax1Rate;                // real(14)  
    public $shippingTax2Rate;                // string(255)  
    public $shippingTaxCode;                 // int(11)  
    public $source;                          // string(255)  
    public $startDate;                       // datetime(19)  binary
    public $status;                          // string(255)  
    public $subTotal;                        // real(14)  
    public $subsidiary;                      // int(11)  
    public $syncPartnerTeams;                // int(4)  
    public $syncSalesTeams;                  // int(4)  
    public $tax2Total;                       // real(14)  
    public $taxItem;                         // int(11)  
    public $taxRate;                         // real(14)  
    public $taxTotal;                        // real(14)  
    public $terms;                           // int(11)  
    public $threeDStatusCode;                // string(255)  
    public $toBeEmailed;                     // int(4)  
    public $toBeFaxed;                       // int(4)  
    public $toBePrinted;                     // int(4)  
    public $total;                           // real(14)  
    public $totalCostEstimate;               // real(14)  
    public $trackingNumbers;                 // string(255)  
    public $tranDate;                        // datetime(19)  binary
    public $tranId;                          // string(255)  
    public $tranIsVsoeBundle;                // int(4)  
    public $validFrom;                       // datetime(19)  binary
    public $vatRegNum;                       // string(255)  
    public $vsoeAutoCalc;                    // int(4)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_SalesOrder',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

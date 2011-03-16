<?php
/**
 * Table Definition for PurchaseOrder
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_PurchaseOrder extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'PurchaseOrder';                   // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $billAddress;                     // string(255)  
    public $billAddressList;                 // int(11)  
    public $class;                           // int(11)  
    public $createdDate;                     // datetime(19)  binary
    public $createdFrom;                     // int(11)  
    public $currencyName;                    // string(255)  
    public $customForm;                      // int(11)  
    public $department;                      // int(11)  
    public $dueDate;                         // datetime(19)  binary
    public $email;                           // string(255)  
    public $employee;                        // int(11)  
    public $entity;                          // int(11)  
    public $exchangeRate;                    // real(14)  
    public $externalId;                      // string(255)  
    public $fax;                             // string(255)  
    public $fob;                             // string(255)  
    public $lastModifiedDate;                // datetime(19)  binary
    public $linkedTrackingNumbers;           // string(255)  
    public $location;                        // int(11)  
    public $memo;                            // string(255)  
    public $message;                         // string(255)  
    public $orderStatus;                     // string(255)  
    public $otherRefNum;                     // string(255)  
    public $shipAddress;                     // string(255)  
    public $shipAddressList;                 // int(11)  
    public $shipDate;                        // datetime(19)  binary
    public $shipMethod;                      // int(11)  
    public $shipTo;                          // int(11)  
    public $source;                          // string(255)  
    public $status;                          // string(255)  
    public $subTotal;                        // real(14)  
    public $subsidiary;                      // int(11)  
    public $supervisorApproval;              // int(4)  
    public $tax2Total;                       // real(14)  
    public $taxTotal;                        // real(14)  
    public $terms;                           // int(11)  
    public $toBeEmailed;                     // int(4)  
    public $toBeFaxed;                       // int(4)  
    public $toBePrinted;                     // int(4)  
    public $total;                           // real(14)  
    public $trackingNumbers;                 // string(255)  
    public $tranDate;                        // datetime(19)  binary
    public $tranId;                          // string(255)  
    public $vatRegNum;                       // string(255)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_PurchaseOrder',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

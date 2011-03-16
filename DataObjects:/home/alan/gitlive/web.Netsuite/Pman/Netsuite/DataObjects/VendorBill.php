<?php
/**
 * Table Definition for VendorBill
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_VendorBill extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'VendorBill';                      // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $account;                         // int(11)  
    public $class;                           // int(11)  
    public $createdDate;                     // datetime(19)  binary
    public $creditLimit;                     // real(14)  
    public $currencyName;                    // string(255)  
    public $customForm;                      // int(11)  
    public $department;                      // int(11)  
    public $discountAmount;                  // real(14)  
    public $discountDate;                    // datetime(19)  binary
    public $dueDate;                         // datetime(19)  binary
    public $entity;                          // int(11)  
    public $exchangeRate;                    // real(14)  
    public $externalId;                      // string(255)  
    public $landedCostMethod;                // string(255)  
    public $lastModifiedDate;                // datetime(19)  binary
    public $location;                        // int(11)  
    public $memo;                            // string(255)  
    public $postingPeriod;                   // int(11)  
    public $status;                          // string(255)  
    public $subsidiary;                      // int(11)  
    public $tax2Total;                       // real(14)  
    public $taxTotal;                        // real(14)  
    public $terms;                           // int(11)  
    public $tranDate;                        // datetime(19)  binary
    public $tranId;                          // string(255)  
    public $userTotal;                       // real(14)  
    public $vatRegNum;                       // string(255)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_VendorBill',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

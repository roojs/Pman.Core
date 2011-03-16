<?php
/**
 * Table Definition for Vendor
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Vendor extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Vendor';                          // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $accountNumber;                   // string(255)  
    public $altEmail;                        // string(255)  
    public $altName;                         // string(255)  
    public $altPhone;                        // string(255)  
    public $balance;                         // real(14)  
    public $bcn;                             // string(255)  
    public $billPay;                         // int(4)  
    public $category;                        // int(11)  
    public $comments;                        // string(255)  
    public $companyName;                     // string(255)  
    public $creditLimit;                     // real(14)  
    public $currency;                        // int(11)  
    public $customForm;                      // int(11)  
    public $dateCreated;                     // datetime(19)  binary
    public $defaultAddress;                  // string(255)  
    public $eligibleForCommission;           // int(4)  
    public $email;                           // string(255)  
    public $emailPreference;                 // string(255)  
    public $emailTransactions;               // int(4)  
    public $entityId;                        // string(255)  
    public $expenseAccount;                  // int(11)  
    public $externalId;                      // string(255)  
    public $fax;                             // string(255)  
    public $faxTransactions;                 // int(4)  
    public $firstName;                       // string(255)  
    public $giveAccess;                      // int(4)  
    public $globalSubscriptionStatus;        // string(255)  
    public $homePhone;                       // string(255)  
    public $image;                           // int(11)  
    public $internalId;                      // string(255)  
    public $is1099Eligible;                  // int(4)  
    public $isAccountant;                    // int(4)  
    public $isInactive;                      // int(4)  
    public $isJobResourceVend;               // int(4)  
    public $isPerson;                        // int(4)  
    public $laborCost;                       // real(14)  
    public $lastModifiedDate;                // datetime(19)  binary
    public $lastName;                        // string(255)  
    public $legalName;                       // string(255)  
    public $middleName;                      // string(255)  
    public $mobilePhone;                     // string(255)  
    public $openingBalance;                  // real(14)  
    public $openingBalanceAccount;           // int(11)  
    public $openingBalanceDate;              // datetime(19)  binary
    public $password;                        // string(255)  
    public $password2;                       // string(255)  
    public $phone;                           // string(255)  
    public $phoneticName;                    // string(255)  
    public $printOnCheckAs;                  // string(255)  
    public $printTransactions;               // int(4)  
    public $requirePwdChange;                // int(4)  
    public $salutation;                      // string(255)  
    public $sendEmail;                       // int(4)  
    public $subsidiary;                      // int(11)  
    public $taxIdNum;                        // string(255)  
    public $terms;                           // int(11)  
    public $title;                           // string(255)  
    public $unbilledOrders;                  // real(14)  
    public $url;                             // string(255)  
    public $vatRegNumber;                    // string(255)  
    public $workCalendar;                    // int(11)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Vendor',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

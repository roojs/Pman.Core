<?php
/**
 * Table Definition for KitItem
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_KitItem extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'KitItem';                         // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $availableToPartners;             // int(4)  
    public $billingSchedule;                 // int(11)  
    public $class;                           // int(11)  
    public $costEstimate;                    // real(14)  
    public $costEstimateType;                // string(255)  
    public $countryOfManufacture;            // string(255)  
    public $createdDate;                     // datetime(19)  binary
    public $customForm;                      // int(11)  
    public $deferredRevenueAccount;          // int(11)  
    public $department;                      // int(11)  
    public $description;                     // string(255)  
    public $displayName;                     // string(255)  
    public $dontShowPrice;                   // int(4)  
    public $enforceMinQtyInternally;         // int(4)  
    public $excludeFromSitemap;              // int(4)  
    public $externalId;                      // string(255)  
    public $featuredDescription;             // string(255)  
    public $handlingCost;                    // real(14)  
    public $includeChildren;                 // int(4)  
    public $incomeAccount;                   // int(11)  
    public $isDonationItem;                  // int(4)  
    public $isFulfillable;                   // int(4)  
    public $isGcoCompliant;                  // int(4)  
    public $isInactive;                      // int(4)  
    public $isOnline;                        // int(4)  
    public $isTaxable;                       // int(4)  
    public $issueProduct;                    // int(11)  
    public $itemId;                          // string(255)  
    public $lastModifiedDate;                // datetime(19)  binary
    public $location;                        // int(11)  
    public $manufacturer;                    // string(255)  
    public $manufacturerCity;                // string(255)  
    public $manufacturerState;               // string(255)  
    public $manufacturerTariff;              // string(255)  
    public $manufacturerTaxId;               // string(255)  
    public $manufacturerZip;                 // string(255)  
    public $manufactureraddr1;               // string(255)  
    public $maxDonationAmount;               // real(14)  
    public $metaTagHtml;                     // string(255)  
    public $minimumQuantity;                 // int(11)  
    public $mpn;                             // string(255)  
    public $multManufactureAddr;             // int(4)  
    public $nexTagCategory;                  // string(255)  
    public $noPriceMessage;                  // string(255)  
    public $offerSupport;                    // int(4)  
    public $onSpecial;                       // int(4)  
    public $outOfStockBehavior;              // string(255)  
    public $outOfStockMessage;               // string(255)  
    public $overallQuantityPricingType;      // string(255)  
    public $pageTitle;                       // string(255)  
    public $parent;                          // int(11)  
    public $preferenceCriterion;             // string(255)  
    public $pricesIncludeTax;                // int(4)  
    public $pricingGroup;                    // int(11)  
    public $printItems;                      // int(4)  
    public $producer;                        // int(4)  
    public $quantityPricingSchedule;         // int(11)  
    public $rate;                            // real(14)  
    public $relatedItemsDescription;         // string(255)  
    public $revRecSchedule;                  // int(11)  
    public $salesTaxCode;                    // int(11)  
    public $scheduleBCode;                   // int(11)  
    public $scheduleBNumber;                 // string(255)  
    public $scheduleBQuantity;               // int(11)  
    public $searchKeywords;                  // string(255)  
    public $shipIndividually;                // int(4)  
    public $shipPackage;                     // int(11)  
    public $shippingCost;                    // real(14)  
    public $shoppingDotComCategory;          // string(255)  
    public $shopzillaCategoryId;             // int(11)  
    public $showDefaultDonationAmount;       // int(4)  
    public $sitemapPriority;                 // string(255)  
    public $softDescriptor;                  // int(11)  
    public $specialsDescription;             // string(255)  
    public $stockDescription;                // string(255)  
    public $storeDescription;                // string(255)  
    public $storeDetailedDescription;        // string(255)  
    public $storeDisplayImage;               // int(11)  
    public $storeDisplayName;                // string(255)  
    public $storeDisplayThumbnail;           // int(11)  
    public $storeItemTemplate;               // int(11)  
    public $taxSchedule;                     // int(11)  
    public $upcCode;                         // string(255)  
    public $urlComponent;                    // string(255)  
    public $useMarginalRates;                // int(4)  
    public $vsoeDeferral;                    // string(255)  
    public $vsoeDelivered;                   // int(4)  
    public $vsoePermitDiscount;              // string(255)  
    public $vsoePrice;                       // real(14)  
    public $weight;                          // real(14)  
    public $weightUnit;                      // int(11)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_KitItem',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

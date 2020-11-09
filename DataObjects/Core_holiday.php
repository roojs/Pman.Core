<?php
/**
 * Table Definition for core company
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_holiday extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_holiday';               // table name
    public $id;                              
    public $country;                            
    public $holiday_date;                            

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

<?php
/**
 * Table Definition for core_curr_rate
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_curr_rate extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */
  
    public $__table = 'core_curr_rate';    // table name
    public $id;
    public $curr;
    public $rate;
    public $from;
    public $to;

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function applyFilters($q, $au, $roo)
    {
        
    }
    
    
}

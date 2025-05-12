<?php
/**
 * Table Definition for yahoo queries cache
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_Cache_Yahoo extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_cache_yahoo';                       // table name
    public $id;
    public $query;
    public $result;
    
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
<?php
/**
 * Table Definition for translations
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Translations extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'translations';                    // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $module;                          // string(64)  not_null multiple_key
    public $tfile;                           // string(128)  not_null
    public $tlang;                           // string(8)  not_null
    public $tkey;                            // string(32)  not_null
    public $tval;                            // blob(4294967295)  not_null blob

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function loadFromModule($modinfo)
    {
        
        
        
        
    }
    
}

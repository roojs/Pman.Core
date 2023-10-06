<?php
/**
 * Table Definition for core_domain
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_domain extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */
  
    public $__table = 'core_domain';
    public $id;
    public $domain;

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    function loadOrCreate($dom)
    {
        // should we validate domain?
        $cd = DB_DataObject::Factory($dom);
        if ($cd->get('domain', $dom)) {
            return $cd;
        }
        $cd->domain = $dom;
        $cd->insert();
        return $cd;
    }
}

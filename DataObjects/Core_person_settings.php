<?php
/**
 * Table Definition for Core_person_settings
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_person_settings extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_person_settings';
    public $id;
    public $person_id;
    public $scope;
    public $data;
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
 }
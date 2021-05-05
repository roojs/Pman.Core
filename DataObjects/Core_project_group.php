<?php
/**
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_project_group extends DB_DataObject 
{
  
    public $__table = 'core_project_group'; 
  
}
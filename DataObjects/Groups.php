<?php
/**
 * @deprecated - use Core_groups
 * 
 */
require_once 'Pman/Core/DataObjects/Core_groups.php';

class  Pman_Core_DataObjects_Groups extends Pman_Core_DataObjects_Core_groups
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Groups';                          // table name
    
    
     function memberTable()
    {
        return 'group_members';
    }
     function rightsTable()
    {
        return 'group_rights';
    }
    
}

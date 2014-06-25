<?php
/**
 * Table Definition for core_oauth_clients
 *
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_oauth_clients extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_oauth_clients';         // table name
    public $client_id;                              // varchar
    public $client_secret;                          // varchar
    public $redirect_uri;                           // varchar
    public $grant_types;                            // varchar
    public $scope;                                  // varchar
    public $user_id;                                // varchar
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    /** make sure there is a watch for this user.. */
    
    
    function applyFilters($q,$au, $roo)
    {
        
    }
    
}

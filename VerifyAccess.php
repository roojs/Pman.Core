<?php

require_once 'Pman.php';

class Pman_Core_VerifyAccess extends Pman
{
    /*
     * This is a public page
     */
    function getAuth() 
    {
        return true;
    }
    
    function get($id)
    {
        @list($vid, $key) = explode('/', $id);
        
        print_R($array($vid, $key));exit;
        
    }
    
}

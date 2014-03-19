<?php

/**
 * Description of GroupCountries
 *
 * @author chris
 */
require_once 'Pman.php';

class Pman_Core_GroupCountries extends Pman
{
    //put your code here
    
    function getAuth() {
        parent::getAuth(); // load company!
        $au = $this->getAuthUser();
        if (!$au) {
            $this->jerr("Not authenticated", array('authFailure' => true));
        }
        if ($au->company()->comptype != 'OWNER') {
            $this->jerr("Permission Denied" );
        }
        $this->authUser = $au;
        return true;
    }
    
    function get()
    {
        $this->post();
    }
    
    function post()
    {
        if (!$this->hasPerm( 'Core.Groups','E')) { // editing groups..
            $this->jerr("PERMISSION DENIED");
        }
        
        $users = explode(',', $_REQUEST['user_ids']);
        
        $cls = $_REQUEST['action'].'PersonToCountry';
        $this->$cls($users);
        
        print_r($_REQUEST);
    }
    
    function addPersonToCountry()
    {
        foreach($users as $id){
            
        }
    }
    
    function supPersonToCountry()
    {
        
    }
    
}

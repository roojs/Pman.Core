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
            $this->jerror("LOGIN-NOAUTH", "Not authenticated", array('authFailure' => true));
        }
        if ($au->company()->comptype != 'OWNER') {
            $this->jerr("Permission Denied" );
        }
        $this->authUser = $au;
        return true;
    }
    
    function get($v, $opts=array())
    {
        $this->post($v);
    }
    
    function post($v)
    {
        if (!$this->hasPerm( 'Core.Groups','E')) { // editing groups..
            $this->jerr("PERMISSION DENIED");
        }
        
        $users = explode(',', $_REQUEST['user_ids']);
        
        $cls = $_REQUEST['action'].'PersonToCountry';// add or sup
        $this->$cls($users);
        
        print_r($_REQUEST);
    }
    
    function addPersonToCountry($users)
    {
        foreach($users as $id){
            $p = DB_DataObject::factory('core_person');
            if(!$p->get($id)){
                $this->jerr('This Person is not exsiting');
            }
            $c = explode(',', $p->countries);
            $c[] = $_REQUEST['country'];
            sort($c);
//            print_r($c); 
            $p->countries = implode(',', $c);
            $p->update();
        }
        $this->jok(true);
    }
    
    function subPersonToCountry($users)
    {
        foreach($users as $id){
            $p = DB_DataObject::factory('core_person');
            if(!$p->get($id)){
                $this->jerr('This Person is not exsiting');
            }
            $c = explode(',', $p->countries);
            if(($key = array_search($_REQUEST['country'], $c)) !== false) {
                unset($c[$key]);
            }
            sort($c); 
//            print_r($c);
            $p->countries = implode(',', $c);
            $p->update();
        }
        $this->jok(true);
    }
    
}

<?php


class Pman_Core_RooConfig extends Pman {
    
    function getAuth()
    {
        return true; // everyone allowed
    }
    
    function post() {
        $this->jerr("access denied");
    }
    
    function get()
    {
        $c = DB_DataObject::factory('core_enum')->fetchAllByType('comptype'); // array of object.s
        
        
        
    }
}

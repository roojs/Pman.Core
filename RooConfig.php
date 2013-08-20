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
        $fonts = DB_DataObject::factory('core_enum')->fetchAllByType('HtmlEditor.font-family');
        $ar = array();
        foreach($fonts as $f) {
            $ar[] = array( $f->name, $f->display_name );
        }
        
        
        
    }
}

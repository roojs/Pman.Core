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
        // at present these are public core enums..
        // if we want to include private ones, we will have to load them after the page has loaded..
        
        
        $fonts = DB_DataObject::factory('core_enum')->fetchAllByType('HtmlEditor.font-family');
        $ar = array();
        foreach($fonts as $f) {
            $ar[] = array( $f->name, $f->display_name );
        }
        if (!empty($ar)) { 
            echo "Roo.form.HtmlEditor.ToolbarContext.options['font-family'] = " . json_encode($ar) .";\n";
        }
        /// add any other generic data in here..
        
    }
}

<?php

/**
 * parse BJS files .... 
 *
 *
 */

class Pman_Core_Bjs {
    
    
    static function formFields($file)
    {
        $a = new Pman_Core_Bjs();
        $json = json_decode(file_get_contents($file));
    
        return $a->iterateFields($json->items,$res);
    }
    
    
    
}

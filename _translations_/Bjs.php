<?php

/**
 * parse BJS files .... 
 *
 *
 */

class Pman_Core_Bjs {
    
    
    function formFields($file)
    {
        $json = json_decode(file_get_contents($file));
    
        return $this->iterateFields($json->items,$res);
    }
    
}

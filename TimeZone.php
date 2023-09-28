<?php
require_once 'Pman.php';

class Pman_Core_NotifySend extends Pman
{
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        if (!$ff->cli) {
            $this->errorHandler("access denied");
        }
        //HTML_FlexyFramework::ensureSingle(__FILE__, $this);
        return true;
        
        function get($id, $opts=array())
        {
        }
    
    }
}
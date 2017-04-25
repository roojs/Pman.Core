<?php


class Pman_Core_GenerateNotifications {
    
    
    function generate($roo)
    {
        
     
        $w = DB_DataObject::factory('core_notify_recur');
        if (is_a($w, 'DB_DataObject')) {
            $w->generateNotifications();
        }
        
    }
    
}

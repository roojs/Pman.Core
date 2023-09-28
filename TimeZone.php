<?php
require_once 'Pman.php';

class Pman_Core_TimeZone extends Pman
{
    function getAuth()
    {
        parent::getAuth();
        
        if (!$this->getAuthUser()) {  
            $this->jerr("Not authenticated", array('authFailure' => true));
        }
        
        return true;
    }

    function get($base, $opts=array())
    {
        $ce = DB_DataObject::factory('core_enum');
        $res = $ce->query('
            SELECT
                *
            FROM
                mysql.time_zone_name
        ');
        die('test');
    }

    function post($base) {
        die('Invalid post');
    }
}
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
        self::getTimezones();

        $data = array();

        foreach(self::$timezones as $tz => $offset) {
            $arr = explode('/', $tz);
            $data[] = array(
                'region' => $arr[0],
                'area' => $arr[1],
                'offset' => $offset
                'displayArea' => $
            )
        }

        echo json_encode(array(
            'data' => $data,
            'metaData' => array(
                'id' => 'id',
                'root' => 'data',
                'successProperty' => 'success',
                'totalProperty' => 'total',
                'fields' => array(
                    'region',
                    'area',
                    'offset'
                )
            ),
            'success' => true,
            'total' => count($data),
            
        ));
        exit;
    }

    function post($base) {
        die('Invalid post');
    }

    static $offsets = array();

    static function getTimezones()
    {
        if(!empty(self::$timezones)) {
            return self::$timezones;
        }

        $ce = DB_DataObject::factory('core_enum');
        $ce->query('
            SELECT
                *, TIME_FORMAT(TIMEDIFF(NOW(), CONVERT_TZ(NOW(), Name, "UTC")), "%H:%i") as offset
            FROM
                mysql.time_zone_name
            ORDER BY
                offset DESC,
                Name DESC
        ');

        while($ce->fetch()) {
            // ignroe timezone such as 'CET' and 'America/Argentina/Buenos_Aires'
            if(substr_count($ce->Name, '/') != 1) {
                continue;
            }

            $ar = explode('/', $ce->Name);
            // ignore timezone such as 'Etc/GMT+8'
            if($ar[0] == 'Etc') {
                continue;
            }

            self::$timezones[$ce->Name] = substr($ce->offset, 0, 1) == '-' ? $ce->offset : '+' . $ce->offset;
        }

        return self::$timezones;
    }

    static function getDisplayArea($timezone)
    {
        self::getTimezones();

        // invalid timezone
        if(!isset(self::$timezones[$timezone])) {
            return '';
        }

        $ar = explode('/', $timezones);

        return $ar[1] . ' (GMT ' . self::$timezones[$timezone] . ')';
    }

    
}
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
        self::getOffsets();

        $data = array();

        foreach(self::$offsets as $tz => $o) {
            $arr = explode('/', $tz);

            if(!empty($_REQUEST['region']) && $_REQUEST['region'] != $arr[0]) {
                continue;
            }

            if(
                !empty($_REQUEST['query']['area_start']) 
                && 
                substr(strtolower($arr[1]), 0, strlen($_REQUEST['query']['area_start'])) != strtolower($_REQUEST['query']['area_start'])
            ){
                continue;
            }
            $data[] = array(
                'region' => $arr[0],
                'area' => $arr[1],
                'offset' => $o,
                'displayArea' => self::getDisplayArea($tz)
            );
        }

        echo json_encode(array(
            'data' => $data,
            'metaData' => array(
                'root' => 'data',
                'successProperty' => 'success',
                'totalProperty' => 'total',
                'fields' => array(
                    'region',
                    'area',
                    'offset',
                    'displayArea'
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

    static $timezones = array();

    static function getTimezones()
    {
        if(!empty(self::$timezones)) {
            return self::$timezones;
        }

        $ce = DB_DataObject::factory('core_enum');
        $ce->query('
            SELECT
                *, TIME_FORMAT(TIMEDIFF(NOW(), CONVERT_TZ(NOW(), Name, "UTC")), "%H:%i") as timeOffset
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

            $displayOffset = '(GMT ' . (substr($ce->timeOffset, 0, 1) == '-') ? '' : '+' . $ce->timeOffset . ')';

            self::$timezones[$ce->Name] = array(
                'region' => $ar[0],
                'area' => $ar[1],
                'displayName' => $ce->Name . ' ' . $displayOffset,
                'displayArea' => $ar[1] . ' ' . $displayOffset
            );

            $displayOffset = ' (GMT ' . (substr($ce->timeOffset, 0, 1) == '-') ? '' : '+' . $ce->timeOffset . ')';
        }

        return self::$offsets;
    }

    static function getDisplayArea($timezone)
    {
        self::getOffsets();

        // invalid timezone
        if(!isset(self::$offsets[$timezone])) {
            return '';
        }

        $ar = explode('/', $timezone);

        // e.g. 'Hong_Kong (GMT +08:00)'
        return $ar[1] . ' (GMT ' . self::$offsets[$timezone] . ')';
    }

    
}
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

        foreach(self::$timezones as $tz => $o) {
            if(!empty($_REQUEST['region']) && $_REQUEST['region'] != $o['region']) {
                continue;
            }

            if(
                !empty($_REQUEST['query']['area_start']) 
                && 
                substr(strtolower($o['area']), 0, strlen($_REQUEST['query']['area_start'])) != strtolower($_REQUEST['query']['area_start'])
            ){
                continue;
            }
            $data[] = array(
                'region' => $o['region'],
                'area' => $o['area'],
                'displayArea' => $o['displayArea']
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
                timeoffset DESC,
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

            $timeOffset = ((substr($ce->timeOffset, 0, 1) == '-') ? '' : '+') . $ce->timeOffset;
            $displayOffset = '(GMT ' . ((substr($ce->timeOffset, 0, 1) == '-') ? '' : '+') . $ce->timeOffset . ')';
            $offsetAr = explode(':', $ce->timeOffset);

            self::$timezones[$ce->Name] = array(
                'region' => $ar[0],
                'area' => $ar[1],
                'displayName' => $ce->Name . ' ' . $displayOffset,
                'displayArea' => $ar[1] . ' ' . $displayOffset,
                'timeOffset' => 
                'decimalOffset' => $offsetAr[0] + (($offsetAr[0] < 0) ? (-1 * $offsetAr[1] / 60) : ($offsetAr[1] / 60))
            );
        }

        return self::$timezones;
    }

    
}
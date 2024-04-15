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
        $this->lang = !empty($_REQUEST['lang']) && in_array($_REQUEST['lang'], array('en', 'zh_CN')) ? $_REQUEST['lang'] : 'en';
        self::getTimezones($this->lang);

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

    static function getTimezones($lang)
    {
        if(!empty(self::$timezones)) {
            return self::$timezones;
        }
         $ce = DB_DataObject::factory('core_enum');
        $ce->query("
            SELECT
                *, TIME_FORMAT(TIMEDIFF(NOW(), CONVERT_TZ(NOW(), Name, 'UTC')), '%H:%i') as timeOffset
            FROM
                mysql.time_zone_name
            WHERE
                Name LIKE '%/%'
                AND
                Name NOT LIKE '%/%/%'
                AND
                Name NOT LIKE 'right%'
                AND
                Name NOT LIKE 'posix%'
                AND
                Name NOT LIKE 'Etc%'
            ORDER BY
                SUBSTRING_INDEX(Name, '/', 1) ASC,
                timeoffset ASC,
                Name ASC
        ");

        $regions = DB_DataObject::factory('core_enum');
        $regions->setFrom(
            'etype' => 'Timezone.Region',
            'active' => 1
        );


        $ct = DB_DataObject::factory('core_templatestr');
        $ct->lang = $lang;
        $ct->on_table = 'core_enum';
        $ct->active = 1;
        $translations = array();
        foreach($ct->fetchAll() as $t) {
            if(empty($t->txt)) {
                continue;
            }
            $translations[$t->on_id][$t->on_col] = $t->txt;
        }

        while($ce->fetch()) {
            // ignroe timezone such as 'CET' and 'America/Argentina/Buenos_Aires'
           

            $ar = explode('/', $ce->Name);
            // ignore timezone such as 'Etc/GMT+8'

            $region =  $ar[0];
            $area = $ar[1];


            if(!empty($translations[$ce->id]['display_name'])) {
                $displayRegion = $translations[$ce->id]['display_name'];
            }

           

            $displayArea = str_replace('_', ' ', $ar[1]);

            $timeOffset = ((substr($ce->timeOffset, 0, 1) == '-') ? '' : '+') . $ce->timeOffset;
            $displayOffset = '(GMT ' . $timeOffset . ')';

            self::$timezones[$ce->Name] = array(
                'region' => $ar[0],
                'area' => $ar[1],
                'displayName' => $ar[0] . '/' . $displayArea . ' ' . $displayOffset,
                'displayArea' => $displayArea . ' ' . $displayOffset
            );
        }

        return self::$timezones;
    }

    static function isValidTimeZone($tz) {
        try {
            new DateTimeZone($tz);
        }
        catch (Exception $e) {
            return false;
        }

        return true;
    }

    static function toRegion($tz)
    {
        if(!self::isValidTimeZone($tz)) {
            return '';
        }
        
        return explode('/', $tz)[0];
    }

    static function toArea($tz)
    {
        if(!self::isValidTimeZone($tz)) {
            return '';
        }

        return explode('/', $tz)[1];
    }
    
    static function toTimeOffset($dt, $tz)
    {
        if(!self::isValidTimeZone($tz)) {
            return '';
        }

        if($dt == '0000-00-00 00:00:00' || $dt == '') {
            $dt = 'NOW';
        }

        $date = new DateTime($dt, new DateTimeZone($tz));
        return $date->format('P');
    }

    static function toDisplayArea($dt, $tz)
    {
        return str_replace('_', ' ', self::toArea($tz)) . ' (GMT ' . self::toTimeOffset($dt,$tz) . ')';

    }

    static function toDisplayName($dt, $tz)
    {
        return self::toRegion($tz) . '/' . self::toDisplayArea($dt, $tz);
    }
}
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
        $data = self::getTimezones();

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

    static function getTimezones()
    {
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

        $timezones = array();
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

            $data[] = array(
                'region' => $ar[0],
                'area' => $ar[1],
                'offset' => $ce->offset,
                'displayArea' => self::getOffset($ce->Name)
            );
        }

        return $data;
    }

    // (1) timezone in format of 'XXX/YYY'
    // (2) 'XXX' caanot be 'Etc'
    static function isValidTimeZone($timezone)
    {
        // invalid timezones such as 'CET' and 'America/Argentina/Buenos_Aires'
        if(substr_count($ce->Name, '/') != 1) {
            return false;
        }

        $ar = explode('/', $ce->Name);
        // invalid timezones such as 'Etc/GMT+8'
        if($ar[0] == 'Etc') 
        {
            return false;
        }

        return true;
    }

    static function getOffset($timezone)
    {
        $ce = DB_DataObject::factory('core_enum');
        $ce->query('
            SELECT
                TIME_FORMAT(TIMEDIFF(NOW(), CONVERT_TZ(NOW(), Name, "UTC")), "%H:%i") as offset
            FROM
                mysql.time_zone_name
            WHERE
                Name = "' . $ce->escape($timezone) . '"
        ');
        $ce->fetch();

        return empty($ce->offset) ? '' : $ce->offset;
    }

    
}
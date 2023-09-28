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
        $ce->query('
            SELECT
                *
            FROM
                mysql.time_zone_name
        ');

        $data = array();
        while($ce->fetch()) {
            // ignroe timezone such as 'CET' and 'America/Argentina/Buenos_Aires'
            if(substr_count($ce->Name, '/') != 1) {
                continue;
            }
            $ar = explode('/', $ce->Name);
            $data[] = array(
                'region' => $ar[0],
                'area' => $ar[1]
            );
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
                    'area'
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
}
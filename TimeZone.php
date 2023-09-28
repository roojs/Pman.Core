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

        $res = array();
        while($ce->fetch()) {
            $res[] = array(
                'name' => $ce->Name;
            )
        }

        var_dump($res);
        die('test');

        /*
                $ar = array();
        if (isset($_REQUEST['query']['name'])) {
            $res = $this->checkYahoo($_REQUEST['query']['name']);
            $data = json_decode($res['response']);
            $ar = isset($data->quotes) ? $data->quotes : array();
        }
        echo json_encode(array(
            'data' => $ar,
            'metaData' => array(
                'id' => 'id',
                'root' => 'data',
                'successProperty' => 'success',
                'totalProperty' => 'total',
                'fields' => array(
                    'exchange'  ,
                    'exchDisp',
                    'index',
                    'isYahooFinance'  ,
                    'longname'  ,
                    'quoteType'  ,
                    'score' ,
                    'shortname',
                    'symbol'  ,
                    'typeDisp'
                )
            ),
            'success' => true,
            'total' => count($ar),
            
        ));
        exit;
        */
    }

    function post($base) {
        die('Invalid post');
    }
}
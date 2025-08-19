<?php
/**
 * Table Definition for core company
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_holiday extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_holiday';               // table name
    public $id;                              
    public $country;                            
    public $holiday_date;                            

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    
    static $map = array(
        'hk' => 'http://www.1823.gov.hk/common/ical/gc/en.ics',
        'cny' => 'https://raw.githubusercontent.com/andrewlkho/cny.ics/master/cny.ics', // CNY
    );
    
    
    function applyFilters($q, $au, $roo)
    {
        if (isset($q['date_from'])) {
            $dt = date("Y-m-d",strtotime($q['date_from']));
            $this->whereAdd("holiday_date > '$dt'");
        }
        if (isset($q['date_to'])) {
            $dt = date("Y-m-d",strtotime($q['date_to']));
            $this->whereAdd("holiday_date < '$dt'");
        }
        
        
    }

    function beforeInsert($request, $roo)
    {
        if(!empty($request['_update_database'])) {
            $roo->jok("UPDATED");
        }
    }
    
    
     
    function updateHolidays($country)
    {
        
        if (!isset(self::$map[$country])) {
            die("invalid country");
        }
        
        // do we alredy have the data for this year.
        $d = DB_DataObject::Factory('core_holiday');
        $d->country = $country;
        $d->orderBy('holiday_date DESC');
        $d->limit(1);
        if ($d->count() && $d->find(true) && strtotime($d->holiday_date) > strtotime('NOW + 6 MONTHS')) {
            // no need to fetch..
            return;
        }
        
        
        
        $data = file_get_contents("https://www.1823.gov.hk/common/ical/gc/en.ics", false,
            stream_context_create(array(
                "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            ))
        );
        
        $vevents = explode('BEGIN:VEVENT', $data);
        
        foreach ($vevents as $vevent){
            
            $lines = explode("\n", $vevent);
            
            $start_dt = false;
            $end_dt = false;
            
            foreach ($lines as $line){
                
                if(preg_match('/^DTSTART;VALUE=DATE:([0-9]+)/', $line, $matches)){
                    $fmt = substr($matches[1], 0, 4) . "-" . substr($matches[1], 4, 2) . "-" . substr($matches[1], 6, 2);
                    $start_dt = date('Y-m-d', strtotime($fmt));
                }
                
                if(preg_match('/^DTEND;VALUE=DATE:([0-9]+)/', $line, $matches)){
                    $fmt = substr($matches[1], 0, 4) . "-" . substr($matches[1], 4, 2) . "-" . substr($matches[1], 6, 2);
                    $end_dt = date('Y-m-d', strtotime($fmt));
                }
                if(preg_match('/^SUMMARY[^:]*:(.*)/', $line, $matches)){
                    $name = trim($matches[1]);
                }
            }
            
            if(empty($start_dt) || empty($end_dt)){
                continue;
            }
            //DB_DataObject::DebugLevel(1);
            //var_dump($start_dt); var_dump($end_dt); exit;
            
            for ($i = strtotime($start_dt); $i < strtotime($end_dt) ; $i += (60 * 60 * 24)) {
                
                $d = DB_DataObject::Factory('core_holiday');
                $d->country = $country;
                $d->holiday_date = date('Y-m-d', $i);
                if (!$d->count()) {
                    $d->insert();
 
                } else {
                    $d->find(true);
                    $dd = clone($d);
                    $d->name = $name;
                    $d->update($dd);
                 }
                
                
            }
            
        }

    }
    function isHoliday($country, $date)
    {
        $d = DB_DataObject::Factory('core_holiday');
        $d->country = $country;
        $d->holiday_date = $date;
        return $d->count();
    }
    
}

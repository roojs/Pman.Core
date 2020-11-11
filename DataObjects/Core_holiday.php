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
        'hk' => 'http://www.1823.gov.hk/common/ical/gc/en.ics'
    );
    
     
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
        
        
        
        $data = file_get_contents("http://www.1823.gov.hk/common/ical/gc/en.ics");
        
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
                
            }
            
            if(empty($start_dt) || empty($end_dt)){
                continue;
            }
            
            //var_dump($start_dt); var_dump($end_dt); exit;
            
            for ($i = strtotime($start_dt); $i < strtotime($end_dt) ; $i += (60 * 60 * 24)) {
                
                $d = DB_DataObject::Factory('core_holiday');
                $d->country = $country;
                $d->holiday_date = date('Y-m-d', $i);
                if (!$d->count()) {
                    $d->insert();
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

<?php
/**
 * Table Definition for core_notify_recur
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_notify_recur extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_notify_recur';    // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $person_id;                       // int(11)  not_null
    
    public $dtstart;                         // datetime(19)  not_null binary
    public $dtend;                           // datetime(19)  not_null binary
    public $tz;                              // real(6)  not_null
    
    public $updated_dt;                      // datetime(19)  not_null binary
    
    public $last_applied_dt;                 // datetime(19)  not_null binary
//    public $max_applied_dtl
    public $freq; //  varchar(8) NOT NULL;
    public $freq_day; // text NOT NULL;
    public $freq_hour; // text
    
    public $onid;                            // int(11)  not_null
    public $ontable;                         // string(128)  not_null
    public $last_event_id;                   // int(11)  
    public $method;                         // string(128)  not_null
    
    
    ###END_AUTOCODE
    
    
    /*
      freq =  DAILY | YEARLY | MONTHLY
        *
        *        THESE ARE EXCLUSIVE..
        *        freq_day =  1,2,3,4,5" - day number.. or dayofmonth USES TIME FROM DTSTART (unless hours are speced.)
        *        >> must..
        *        freq_hourly = 'what hours' << OR IF EMPTY USES TIME FROM DTSTART
        *
    /* the code above is auto generated do not remove the tag below */
    
    
    function notifytimesRange($advance) {
        
        $start = date('Y-m-d H:i:s', max(strtotime("NOW - 24 HOURS"), strtotime($this->dtstart)));
        $end  = date('Y-m-d H:i:s', min(strtotime("NOW  + $advance DAYS"), strtotime($this->dtend)));
    
    }
    
    function notifytimes($advance)
    {
        
        // make a list of datetimes when notifies need to be generated for.
        // it starts 24 hours ago.. or when dtstart
        
        list($start, $end) = $this->notifytimesRange($advance);
        
        if (strtotime($start) > strtotime($end)) {
            return array(); // no data..
        }
        
        
        
        switch($this->freq) {
            case 'HOURLY':
                
                
                
                // happens every day based on freq_hour.
                $hours = explode(',', $this->freq_hour);
                for ($day = date('Y-m-d', strtotime($start));
                        strtotime($day) < strtotime($end);
                        $day = date('Y-m-d', strtotime("$day + 1 DAY")))
                {
                    foreach($hours as $h) {
                        $hh = strpos($h,":") > 0 ? $h : "$H:00";
                        $ret[] = $day . ' ' . $hh;
                    }
                }
                return $this->applyTimezoneToList($ret);
                
            case 'DAILY':
                $hours = explode(',', $this->freq_hour);
                if (!$hours) {
                    $hours = array(date('H:i', strtotime($this->dtstart)));
                }
                
                $days = explode(','. $this->freq_day);
                
                for ($day = date('Y-m-d', strtotime($start));
                        strtotime($day) < strtotime($end);
                        $day = date('Y-m-d', strtotime("$day + 1 DAY")))
                {
                    // skip days not accounted for..
                    if (!in_array(date('N', strtotime($day)), $days)) {
                        continue;
                    }
                    
                    foreach($hours as $h) {
                        $hh = strpos($h,":") > 0 ? $h : "$H:00";
                        $ret[] = $day . ' ' . $hh;
                    }
                }
                
                return $this->applyTimezoneToList($ret);
                
                
            case 'MONTHLY': // ignored..
            case 'YEARLY': // ignored..
                break;
            
        }
         
    }
    function applyTimezoneToList($ar)
    {
        $ret = array();
        
        $tz = explode($this->tz, ":");
        if ($tz < 0) {
            
        }
        $append = ($tz[0] < 0) ? " - " : " + ";
        
        $append .= abs($tz[0]) . " HOURS";
        if (!empty($tz[1])) {
            $append .= $tz[1] . " MINUTES";
        }
        
        
        foreach($ar as $a) {
            $ret[] = date('Y-m-d H:i', strtotime($a . $append));
            
        }
        return $ret;
        
        
    }
    
    function generateNotifications(){
        //$this->notifytimes(2);
        DB_DataObject::debugLevel(1);
        $w = DB_DataObject::factory('core_notify_recur');
        $w->findAll();
        $test = $w->fetchAll();
        
        //$test = $this->notifytimes(2);
        foreach($test as $item){
            error_log($item);
        }
        
    }
    
}

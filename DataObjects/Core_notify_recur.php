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
        $ret = array();
        $hours = array_unique(json_decode($this->freq_hour));
        $days = json_decode($this->freq_day);
        foreach($days as $d){
            foreach($hours as $h){
                $ret[] = date('Y-m-d', strtotime($d)) . ' ' . $h;
            }
        }
        return $this->applyTimezoneToList($ret);
    }
    function applyTimezoneToList($ar)
    {
        $ret = array();
        foreach($ar as $a) {
            $date = new DateTime($a);
            $date->setTimezone(new DateTimeZone($this->tz));
            $ret[] = $date->format('Y-m-d H:i');
        }
        return $ret;
    }
    
    function generateNotifications(){
        //$this->notifytimes(2);
        //DB_DataObject::debugLevel(1);
        
        $w = DB_DataObject::factory($this->tableName());
        //$this->notifytimes(2);
        $w->find();
        //$test = $w->fetchAll();
        
        //$test = $this->notifytimes(2);
        
        //$test = array();
        while($w->fetch()){
            
            $notifytime = $w->notifyTimes(2);
            var_dump($notifytime);
//            $this->id = $w->id;
//            $this->person_id = $w->person_id;
//            $this->dtstart = $w->dtstart;
//            $this->dtend = $w->dtend;
//            $this->tz = $w->tz;
//            $this->updated_dt = $w->updated_dt;
//            $this->last_applied_dt = $w->last_applied_dt;
//            $this->freq = $w->freq;
//            $this->freq_day = $w->freq_day;
//            $this->freq_hour = $w->freq_hour;
//            $this->onid = $w->onid;
//            $this->ontable = $w->ontable;
//            $this->last_event_id = $w->last_event_id;
//            $this->method = $w->method;
            //$this->dtstart = $w->dtstart;
            //$this->dtend = $w->dtend;
            //$this = clone($w);
            //$w->notifytimes(2);
            //var_dump($w->notifytimes(2));
        }
//        foreach($test as $item){
//            error_log($item);
//        }
        
    }
    
}

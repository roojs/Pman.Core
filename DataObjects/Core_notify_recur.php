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
    public $recur_id;                       //INT(11) not_null
    
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
        
        $start = date('Y-m-d H:i:s', max(strtotime("NOW"), strtotime($this->dtstart)));
        $end  = date('Y-m-d H:i:s', min(strtotime("NOW + $advance DAYS"), strtotime($this->dtend)));
        
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
                $date = new DateTime(date('Y-m-d', strtotime($d)) . ' ' . $h, new DateTimeZone($this->tz));
                $date->setTimezone(new DateTimeZone('Asia/Hong_Kong'));
                $ret[] = $date->format('Y-m-d H:i:s');
            }
        }
        return $ret;
    }
    
    function generateNotifications(){
        
        //DB_DataObject::debugLevel(1);
        $w = DB_DataObject::factory($this->tableName());
        $w->find();
        
        while($w->fetch()){
            $notifytimes = $w->notifyTimes(2);
            $newSearch = DB_DataObject::factory('core_notify');
            $newSearch->whereAdd( 'act_start > NOW() and act_start < NOW() + INTERVAL 2 DAY');
            $newSearch->recur_id = $w->id;
            $old = $newSearch->fetchAll('act_start', 'id');
            // returns array('2012-12-xx'=>12, 'date' => id....)
            
 
            foreach($notifytimes as $time){
                if (strtotime($time) < time()) {
                    continue;
                }
                if (isset($old[$time])) {
                    // we already have it...
                    unset($old[$time]);
                    continue;
                }
                
                // do not have a notify event... creat it..
                $add = DB_DataObject::factory('core_notify');
                $add->setFrom(array(
                    "recur_id" => $w->id,
                    "act_start" => $time,
                    "act_when" => $time,
                    "person_id" => $w->person_id,
                    "onid" => $w->onid,
                    "ontable" => $w->ontable
                ));
                $add->insert();
            }
            foreach($old as $date => $id ) {
                 $del = DB_DataObject::factory('core_notify');
                 $del->get($id);
                 $del->delete();
            }
 
        }
    }
    
}

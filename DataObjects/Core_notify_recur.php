<?php
/**
 * Table Definition for core_notify_recur
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

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
    public $method;                         // depricated.
    public $method_id;                         // string(128)  not_null
    
    public $recur_id;                       //INT(11) not_null

    
    ###END_AUTOCODE
    //NOTE recur_id and method are depricated.
    
    
    /*
      freq =  DAILY | YEARLY | MONTHLY
        *
        *        THESE ARE EXCLUSIVE..
        *        freq_day =  1,2,3,4,5" - day number.. or dayofmonth USES TIME FROM DTSTART (unless hours are speced.)
        *        >> must..
        *        freq_hourly = 'what hours' << OR IF EMPTY USES TIME FROM DTSTART
        *
    /* the code above is auto generated do not remove the tag below */
    
    function applyFilters($q, $au, $roo)
    {
        
        if (isset($q['query']['person_id_name']) ) {
            $this->whereAdd( "join_person_id_id.name LIKE '{$this->escape($q['query']['person_id_name'])}%'");
             
        }
    }
    
    function method()
    {
        $e = DB_DataObject::Factory('core_enum');
        $e->get($this->method_id);
        return $e;
    }
    
    function person()
    {
        $p = DB_DAtaObject::factory('core_person');
        $p->get($this->person_id);
        return $p;
    }

    function generateNotifications()
    {
        //DB_DataObject::debugLevel(1);
        $w = DB_DataObject::factory($this->tableName());
        $w->find();
        
        while($w->fetch()){
            $w->generateNotificationsSingle();
        
        }
    }
    
    function beforeDelete($dependants_array, $roo)
    {
        $n = DB_DataObject::Factory("core_notify");
        $n->ontable = 'core_notify_recur';
        $n->onid = $this->id;
        $n->whereAdd('act_start > NOW() OR act_when > NOW()');
        $n->delete(DB_DATAOBJECT_WHEREADD_ONLY);
    }

    function notifytimesRange($advance) {
        
        $start = date('Y-m-d H:i:s', max(strtotime("NOW"), strtotime($this->dtstart)));
        $end  = min( new DateTime("NOW + $advance DAYS"),  new DateTime($this->dtend ) )->format('Y-m-d H:i:s');
        
        return array($start, $end);
    }

    function notifytimes($advance)
    {
        // make a list of datetimes when notifies need to be generated for.
        // it starts 24 hours ago.. or when dtstart
        
        list($start, $end) = $this->notifytimesRange($advance);
        //var_dump(array($start, $end));
        //print_r($this);
        
        if (strtotime($start) > strtotime($end)) {
            return array(); // no data..
        }
        $ret = array();
        $hours = empty($this->freq_hour) ? array() : array_unique(json_decode($this->freq_hour));
        $days = empty($this->freq_day) ? array() : json_decode($this->freq_day);
        
        //days to use are = MON FRI SUN
        
        //ARE there events on these day in advance days in the future?
        //TODAY = 25th of may (FRI)
        //TODAY+1  = 26th  (SAT)
        //TODAY +2 = 27th = SUN (2== advance)
        //foreeach day in the future upto >>> advance <<< days?
            
         // - does an event occur on this day?
        //    -YES - then we will generate an event for it.
         //   -NO nothing happens..
        $usedays = array();
        for (  $i =0; $i < $advance +1; $i++) {
            $ut = strtotime("NOW + $i DAYS");
            $day = strtoupper(date("D", $ut));
            if (in_array($day, $days)) {
                $usedays[] = date("Y-m-d", $ut);
            }
        }
               
        //print_r($this);
        
        
        foreach($usedays as $d){
            foreach($hours as $h){
                $date = new DateTime($d. ' ' . $h);
                $ret[] = $date->format('Y-m-d H:i:s');
            }
        }
        return $ret;
    }

    function generateNotificationsSingle()
    {
        $notifytimes = $this->notifyTimes(2);
        
        $newSearch = DB_DataObject::factory('core_notify');
        $newSearch->whereAdd( 'act_start > NOW()');
        $newSearch->ontable = 'core_notify_recur';
        $newSearch->onid = $this->id;
        $old = $newSearch->fetchAll('act_start', 'id');
        // returns array('2012-12-xx'=>12, 'date' => id....)

        
        foreach($notifytimes as $time){
            
            if (strtotime($time) < time()) { // should not happen, just in case...
               continue;
            }

            if (isset($old[$time])) {
                // we already have it...
                $oo = DB_DataObject::Factory('core_notify');
                $oo->get($old[$time]);
                $oc = clone($oo);
                $oo->person_id = $this->person_id;
                $oo->update($oc);
                
                unset($old[$time]);
                continue;
            }
            
            // do not have a notify event... creat it..
            $add = DB_DataObject::factory('core_notify');
            $add->setFrom(array(
                "act_start" => $time,
                "act_when" => $time,
                "person_id" => $this->person_id,
                "onid" => $this->id,
                "ontable" => 'core_notify_recur',
                'evtype' => 'core_notify_recur::recurCall'
            ));
            $add->insert();
        }
        foreach($old as $date => $id ) {
            $del = DB_DataObject::factory('core_notify');
            $del->get($id);
            $del->delete();
        }

    }

    function setFromRoo($req)
    {
        $ret = $this->setFrom($req);
        if (isset($req['dtstart_day'])) {
            $this->dtstart = date('Y-m-d 00:00:00', strtotime($req['dtstart_day']));
        }
        if (isset($req['dtend_day'])) {
            $this->dtend = date('Y-m-d 00:00:00', strtotime($req['dtend_day']));
        }
        return $ret;
    }

    function toRooSingleArray($authUser, $request) 
    {
        $ret = $this->toArray();
        $ret['dtstart_day'] = date('Y-m-d', strtotime($this->dtstart));
        $ret['dtend_day'] = date('Y-m-d', strtotime($this->dtend));

        return $ret;
    }

    function onUpdate($old, $request,$roo)
    {
        $this->generateNotificationsSingle();
        
    }

    function onInsert($request,$roo)
    {
        $this->generateNotificationsSingle();
        
    }

    /**
     * call from NotifySend
     * make email for notify with evtype = 'core_notify_recur::recurCall' and ontable = 'core_notify_recur'
     */
    function recurCall($person, $last_sent_date, $notify_object, $force)
    {
        // invalid medium
        if(($ar = $this->getTableAndMethodFromMedium($this->medium)) === false) {
            return false;
        }

        // if it fails... - it should not!!!! - config should solve this..
        $object = DB_DataObject::factory($ar[0]);
         
        $method = $ar[1];

        try {
            $class = get_class($object);
            
            $reflectionMethod = ReflectionMethod::createFromMethodName($class . '::' . $method);

            if(!$reflectionMethod->isStatic() && empty($this->onid)) {
                // onid is empty but the method is not static
                return false;
            }

            if($reflectionMethod->isStatic() && !empty($this->onid)) {
                // onid is not empty but the method is static
                return false;
            }

        }
        catch (ReflectionException $e)
        {
            // method does not exist
            return false;
        }

        // empty onid => call the static method from medium
        if(empty($this->onid)) {
            return $class::$method($person, $last_sent_date, $notify_object, $force);
        }

        // non-empty onid => call the instance method from medium on object with id 'onid'

        if(empty($object->get($this->onid))) {
            // no such object with id 'onid'
            return false;
        }

        return $object->$method($person, $last_sent_date, $notify_object, $force);
    }

    /**
     * get table and method from medium
     * 
     * @param string $medium medium
     * @return array|boolean return array of table name and method name if valid, else return false
     */
    function getTableAndMethodFromMedium($medium)
    {
        $res = false;
        if(strpos($medium, '::') !== false) {
            $res = explode("::", $medium);
        }
        else if(strpos($medium, ':') !== false) {
            $res = explode(":", $medium);
        }
        else {
            return false;
        }

        if(count($res) != 2) {
            return false;
        }

        return $res;
    }
    
}

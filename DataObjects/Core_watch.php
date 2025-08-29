<?php
/**
 * Table Definition for core_watch
 *
 * works with 'core_notify'
 *
 * any object can call
 *   $watch->notify($ontable, $onid)
 *
 *   in which case it should create a core_notify event.
 *
 *
 * Should 'event' trigger this..
 *   -> eg. somebody makes a 'EDIT' on 'person'
 *   -> a watch exists for
 *        ontable=person,
 *        onid = -1 <<-- every entry..
 *        person_id -> who is goes to.
 *        event = CRUD (eg. shortcut for edit/create/delete)
 *        medium = "REVIEW" << eg. review needed..
 *        
 *
 *  if onid == 0 then it will monitor all changes to that table..
 *
 *
 * 
 * 
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_watch extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_watch';                      // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $ontable;                         // string(128)  not_null
    public $onid;                            // int(11)  not_null
    public $person_id;                       // int(11)  not_null
    public $event;                           // string(128)  not_null
    public $medium;                          // string(128)  not_null
    public $active;                          // int(11)  not_null

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    /** make sure there is a watch for this user.. */
    
    
    function applyFilters($q,$au, $roo)
    {
        if (!empty($q['_list_actions'])) {
            $this->listActions($roo,$q);
        }
        //die("apply filters");
        if (!empty($q['_split_event_name'])) {
            $this->selectAdd("
                
                substr( event, 1, LOCATE( '.',event) -1) as event_left,
                substr( event,   LOCATE( '.',event) +1) as event_right,
                (SELECT
                    display_name FROM core_enum where etype = '{$this->escape($q['_split_event_name'])}'
                    AND name = substr( event,   LOCATE( '.',event) +1)
                ) as event_right_display_name
                             
            ");
            
            
            
        }

        if(!empty($q['_watchable_events'])) {
            $ff = HTML_FlexyFramework::get();

            
            $events = array();

            foreach(explode(",", $ff->enable) as $module) {
                $fn = $ff->rootDir."/Pman/$module/watchable_events.json";
                if (!file_exists($fn) || !is_readable($fn)) {
                    continue;
                }
                
                $arr = json_decode(file_get_contents($fn));
                if(is_null($arr) || !is_array($arr)) {
                    continue;
                }
                foreach($arr as $event) {
                    $ar = explode(":", $event);
                    if(!empty($q['_watchable_events_table']) && $ar[0] != $q['_watchable_events_table']) {
                        // only accept event from request table
                        continue;
                    }

                    $events[] = array(
                        'table' => $ar[0],
                        'action' => $ar[1]
                    );
                }
            }

            $roo->jdata($events);
        }

        if(!empty($q['_watchable_actions'])) {
            $ff = HTML_FlexyFramework::get();
            
            $actions = array();

            foreach(explode(",", $ff->enable) as $module) {
                
                $fn = $ff->rootDir."/Pman/$module/watchable_actions.json";
                if (!file_exists($fn) || !is_readable($fn)) {
                    continue;
                }
                
                $arr = json_decode(file_get_contents($fn));
                if(is_null($arr) || !is_array($arr)) {
                    continue;
                }
                     
                foreach($arr as $action) {
                    
                    $method = $this->actionAsReflection($action, $roo);
                    if ($method === false) {
                        continue;  
                    }
                    
                    if(!$method->isStatic() && !empty($q['_watchable_static_actions'])) {
                        // get an instance method but a statis method is required
                        continue;
                    }
                    if($method->isStatic() && !empty($q['_watchable_instance_actions'])) {
                        // get a static method but an instance method is required
                        continue;
                    }

                     

                    $actions[] = array(
                        'action' => $action
                    );
                }
                  
            }

            $roo->jdata($actions);
        }
        
    }
    // used to verify watchable actions, and get the callable one..
    function actionAsReflection($str, $roo)
    {
        $ar = $this->getTableAndMethodFromMedium($str);
        if ($ar === false) {   
            return false;
        }

        PEAR::staticPushErrorHandling(PEAR_ERROR_RETURN);
        $object = DB_DataObject::factory($ar[0]);
        if(PEAR::isError($object)) {
            // table does not exist
            false;
        }
        PEAR::staticPopErrorHandling();

        
        if (!method_exists($object, $ar[1])) {
            if (!$roo) {
                return;
            }
            $roo->jerr("method does not exist: {$ar[1]}");
        }
        $class = get_class($object);
        
        $method = new ReflectionMethod($class. $ar[1]);
        return $method;
        
        
    }
    
    

    function beforeInsert($request, $roo)
    {
        if(isset($request['delay_value']) && isset($request['delay_unit'])) {
            switch($request['delay_unit']) {
                case 'days':
                    $this->no_minutes = $request['delay_value'] * 1440;
                    break;
                case 'hours':
                    $this->no_minutes = $request['delay_value'] * 60;
                    break;
                case 'minutes':
                    $this->no_minutes = $request['delay_value'];
                    break;
            }
        }
        if(!empty($request['_copy'])) {
            $cw = DB_DataObject::factory('core_watch');

            if(!$cw->get($request['_copy'])) {
                $roo->jerr('No watch with id ' . $request['_copy']);
            }
            
            $new = DB_DataObject::factory('core_watch');
            $new->setFrom($cw->toArray());
            $new->person_id = 0;
            $new->insert();

            $roo->jok('DONE');
        }
    }

    function  beforeUpdate($old, $request, $roo)
    {
        if(isset($request['delay_value']) && isset($request['delay_unit'])) {
            switch($request['delay_unit']) {
                case 'days':
                    $this->no_minutes = $request['delay_value'] * 1440;
                    break;
                case 'hours':
                    $this->no_minutes = $request['delay_value'] * 60;
                    break;
                case 'minutes':
                    $this->no_minutes = $request['delay_value'];
                    break;
            }
        }
    }

    function onUpdate($old, $request,$roo, $event)
    {
        // becomes inactive
        if($old->active != $this->active && !$this->active) {
            // delete pending notifications
            DB_DataObject::factory('core_notify')->query(
                "DELETE FROM
                    core_notify
                WHERE
                    watch_id = {$this->id}
                AND
                    event_id < 1
            ");
        }
    }
    
    function toRooSingleArray($au,$q)
    {
        $ret = $this->toArray();

        $ret['delay_value'] = empty($ret['no_minutes']) ? 0 : $ret['no_minutes'];
        $ret['delay_unit'] = 'minutes';

        if($ret['no_minutes'] >= 1440 && $ret['no_minutes'] % 1440 == 0) {
            $ret['delay_value'] = $ret['no_minutes'] / 1440;
            $ret['delay_unit'] = 'days';
        }
        else if ($ret['no_minutes'] >= 60 && $ret['no_minutes'] % 60 == 0) {
            $ret['delay_value'] = $ret['no_minutes'] / 60;
            $ret['delay_unit'] = 'hours';
        }

        if (empty($q['_split_event_name'])) {
            return $ret;
        }
        $bits = explode('.', $this->event);
        $ret['event_left'] = $bits[0];
        $ret['event_right'] = $bits[1];
        // check core enu.
        if (!empty($ret['event_right'])) {
            $ce = DB_DataObject::factory('core_enum')->lookupObject($q['_split_event_name'], $ret['event_right']);
            $ret['event_right_display_name'] = $ce->display_name;
        }
        
        return $ret;
        
        
    }

    function postListFilter($ar, $au, $req)
    {
        foreach($ar as &$v) {
            $v['delay_value'] = empty($v['no_minutes']) ? 0 : $v['no_minutes'];
            $v['delay_unit'] = 'minutes';
    
            if($v['no_minutes'] >= 1440 && $v['no_minutes'] % 1440 == 0) {
                $v['delay_value'] = $v['no_minutes'] / 1440;
                $v['delay_unit'] = 'days';
            }
            else if ($v['no_minutes'] >= 60 && $v['no_minutes'] % 60 == 0) {
                $v['delay_value'] = $v['no_minutes'] / 60;
                $v['delay_unit'] = 'hours';
            }
        }

        return $ar;
    }
    
    function listActions($roo, $q) {
        
        //print_r($q);
        $d = DB_DataObject::Factory($q['on_table']);
        $ret = array();
        
        foreach(get_class_methods($d) as $m) {
            //var_dump($m);
            if (!preg_match('/^notify/', $m)) {
                continue;
            }
            $ret[] = array(
                'display_name' => preg_replace('/^notify/', '' , $m),
                'name' => $q['on_table'] .':'. $m
            );
        }
        $roo->jdata($ret);
    }
    
    /**
     *
     * Create a watch...
     *
     */
    
    
    
    
    function ensureNotify(  $ontable, $onid, $person_id, $whereAdd)
    {
        //DB_DAtaObject::debugLevel(1);
        $w = DB_DataObject::factory('core_watch');
        $w->person_id = $person_id;
        if (empty($w->person_id)) {
            return;
        }
        
        $nw = clone($w);
        $w->whereAdd($whereAdd);
        
        
        if ($w->count()) {
            return;
        }
        $nw->ontable = $ontable;
        $nw->onid = $onid;
        
        $nw->medium = 'email';
        $nw->active = 1;
        $nw->insert();
         
    }
    /**
     * Generate a notify event based on watches (matching whereAdd)
     *
     * Usage: $core_watch->notify('mtrack_repos', 1, false, date()
     *
     * This can match any 'event' type - eg. it can be blank etc...
     *   Generally used by non-event driven notifications, like our
     *   Daily commit message.
     *
     *  @param string $ontable - the database table that has been updated/changed etc.
     *  @param int    $onid    - the id of the row changed
     *  @param string  $whereAdd (optiona) - a DB whereAdd() condition to filter the search for watches
     *  @param datetime    $when   (default now)  - date/time to create the notification for (Eg. end of day..)
     *  @param string $to_ontable  - notify event create on this table, rather than watch table.
     *  @param string $to_id  - notify event create on this id, rather than watch id.
     *  
     * 
     */
    function notify($ontable , $onid, $whereAdd = false, $when=false, $to_ontable=false, $to_onid=false)
    {
        $w = DB_DataObject::factory('core_watch');   
        if ($whereAdd !== false) { 
            $w->whereAdd($whereAdd  );
        }
        $w->active =1;
        
        $w->whereAdd('onid = 0 OR onid='. ((int) $onid));
       
        
        $w->ontable = $ontable;
        //$w->selectAdd();
        //$w->selectAdd('distinct(person_id) as person_id');
        
        foreach($w->fetchAll() as $w) { 
            if (!$w->person_id) { // no people??? bugs in watch table
                continue;
            }
         
            $nn = DB_DataObject::Factory('core_notify');
            $nn->ontable = $to_ontable === false ? $ontable : $to_ontable;
            $nn->onid = $to_onid === false ?  $onid : $to_onid;
            $nn->evtype = $w->medium;
            $nn->person_id = $w->person_id;
            
            $nf = clone($nn);
            $nf->whereAdd("sent < '2000-01-01'");
            if ($nf->count()) {
                // we have a item in the queue for that waiting to be sent..
                continue;
            }
            $nn->act_start( date("Y-m-d H:i:s", $when !== false ? strtotime($when) : time()) );
            $nn->insert();
        }
          
    }
    // static really...
    /**
     *
     * This get's called by roo->addEvent()
     *
     * And searches for matching '$watch->event' == $event->action
     *  along with id/table etc..
     *
     * it's basic usage is to fill in core_notify after an event has happend.
     *
     * We can also use it to notify other modules if something has happend.
     *  = eg. mtrack_ticket * watch will notify mtrack_jira::
     *
     * @param Pman_Core_DataObject_Events $event - the Pman event dataobject that was created
     * 
     */
    
    function notifyEvent($event, $now = false)
    {
        //print_r($event);
        //DB_DataObject::DebugLevel(1);
        // see if there are any watches on events..
        // notify everyone flagged except the person doing it...
        // this is very basic logic... -
        //    if more intelligence is needed...
        //    then it 'rules' will have to be added by the watched object....
        //    anyway leave that for another day..
        if (empty($event->action)) {
            return;
        }

        $w = DB_DataObject::factory('core_watch');
        $w->ontable = $event->on_table;
        $w->whereAdd('onid = 0 OR onid='. ((int) $event->on_id));
       
        $w->event  = $event->action;
        $w->active = 1;
        
        // not sure why this is here... - it breaks on the reader article -> 
        // if ($event->person_id) {
        //     $w->whereAdd('person_id != '. (int) $event->person_id);
        // }
 
        $watches = $w->fetchAll();
        
        //print_R($watches); 
        
        $nn = DB_DataObject::Factory('core_notify');
        $nn->ontable    = $event->on_table;
        $nn->onid       = $event->on_id;
        
        foreach($watches as $watch) {
            $n = clone($nn);
            
            // this is used by the commit code to monitor repos?
            // 
            
            $method = $this->actionAsReflection($watch->medium, false);
             
            // this only works with static methods,
            // otherwise only a notification will be created.
            if (!$watch->person_id && $method && $method->isStatic()) {  
                if(($dom = $this->getTableAndMethodFromMedium($watch->medium)) === false) {
                    // invalid medium
                    continue;
                }
                // in some scenarios (like watching for new articles)
                // we need to create a core, notify on the medium..
                // in which case we set the  set $nn->evtype = medium..
                // in that case - just let the called method generate the notify..
                
                //print_R($dom);
                $do = DB_DataObject::factory($dom[0]);
                if (!method_exists($do,$dom[1])) {
                    continue;
                }
                //echo "calling {$watch->medium}\n";
                // the triggered method, can either do something
                // or modify the notify event..
                if ($do->{$dom[1]}($event, $n, $watch) !== false) {
                    //echo "method did not return false?";
                    continue;
                }
                  
            }
            
            $n->person_id = $watch->person_id;
            $n->watch_id =  $watch->id;
            $n->evtype   = $watch->medium;

            $n->trigger_person_id = $event->person_id;
            $n->trigger_event_id = $event->id;
            
            // does this watch already have a flag...
            $nf = clone($n);
            $nf->whereAdd("sent < '2000-01-01'");
            //$nf->whereAdd('sent < act_when');
            if ($nf->count()) {
                // we have a item in the queue for that waiting to be sent..
                continue;
            }

            //echo "inserting notify?";
            $n->act_start( empty($n->act_start) ?
                (
                    empty($now) ?
                    $n->sqlValue("NOW()" . (empty($watch->no_minutes) ? "" : " + INTERVAL {$watch->no_minutes} MINUTE"))
                    :
                    $n->sqlValue($now . (empty($watch->no_minutes) ? "" : " + INTERVAL {$watch->no_minutes} MINUTE"))
                )
                    : $n->act_start );
            $n->insert();
        }
         
    }
    function initDatabase($roo, $data) {
        foreach($data as $d) {
            $dd = $d;
            if (isset($dd['active'])) {
                unset($dd['active']);
            }
            $t = DB_DataObject::Factory($this->tableName());
            $t->setFrom($dd);
            if ($t->find(true)) {
                continue;
            }
            $t = DB_DataObject::Factory($this->tableName());
            $t->setFrom($d);
            $t->insert();
            
            
        }
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

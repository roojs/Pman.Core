<?php
/**
 *
 * Archive for core notify
 */
  
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_notify_archive extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_notify_archive';  // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $recur_id;                        // int(11) not_null
    public $act_when;                        // datetime(19)  not_null multiple_key binary
    public $onid;                            // int(11)  not_null
    public $ontable;                         // string(128)  not_null
    public $person_id;                       // int(11)  not_null
    public $msgid;                           // string(128)  not_null
    public $sent;                            // datetime(19)  not_null binary
    public $event_id;                        // int(11)  
    public $watch_id;                        // int(11)  
    public $trigger_person_id;                 // int(11)
    public $trigger_event_id;              // int(11)  
    public $evtype;                         // event type (or method to call)fall
    public $act_start;
    public $person_table;
    public $to_email;
 
 
    function archive()
    {
        
        $start_time = date("Y-m-d H:i:s");
        echo "$start_time : Start  archive onid: $onid\r\n";

        $pe = DB_DataObject::factory('core_notify');
        $pe->whereAdd('act_when < NOW() - INTERVAL 6 MONTH');
        $pe->orderBy('id ASC');
        $pe->limit(1000);
        $ids = $pe->fetchAll('id');
        if (empty($ids)) {
            return;
        }
        
        $pe = DB_DataObject::factory('core_notify');
        $pe->whereAddIn('id', $ids , 'int');
        $eids = array_unique($pe->fetchAll('event_id'));
                      
        
        
        $p = DB_DataObject::Factory('core_notify');
        //$p->query("DROP TEMPORARY TABLE IF EXISTS $temp_table");
      
        $p->query("BEGIN");

        

                           
        $p->query("
              REPLACE INTO 
                  core_notify_archive 
              SELECT * from 
                  core_notify 
              WHERE 
                  id in (" . implode(',', $ids) . ")
             ");
        
         
        
        $p->query("
              REPLACE INTO
                  core_events_archive
              SELECT * from 
                  Events 
              WHERE 
                  id 
                 IN 
                    (
                     ". implode(',', $ids) . "
                     )
             ");
             
     
        // looping seems to be far quicker than IN() or any other version..
        foreach($ids as $id) {     
            $p->query("                        
                      DELETE FROM  
                          Events 
                      WHERE   
                          id = {$id}
            ");
        }
                   
        $p->query("                        
                  DELETE FROM  
                      pressrelease_notify                        
                  WHERE  
                      ontable = 'pressrelease_entry' 
                  AND 
                      onid = $onid
              ");       
        $p->query("
              UPDATE 
                  pressrelease_entry 
              SET 
                  archived_dist_summary_all =   
                  (            
                      SELECT 
                         count(id) 
                      FROM   
                          pressrelease_notify_archive 
                      WHERE
                          onid = $onid
                      AND
                          ontable = 'pressrelease_entry'
                      AND
                          evtype = 'MAIL'
                  ),
                  archived_dist_summary_complete = 
                  (            
                      SELECT 
                          count(id)                       
                      FROM   
                          pressrelease_notify_archive 
                      WHERE
                          onid = $onid
                      AND
                         ontable = 'pressrelease_entry'
                      AND
                         sent < NOW()
                      AND
                        event_id > 0
                      AND
                        evtype = 'MAIL' 
                  ),
                  archived_dist_summary_fail = 
                  (            
                      SELECT 
                          count(id)  
                      FROM   
                          pressrelease_notify_archive 
                      WHERE
                          onid = $onid
                      AND
                          ontable = 'pressrelease_entry'
                      AND
                          event_id > 0
                      AND
                          evtype = 'MAIL'
                      AND
                          msgid = ''     
                  ),
                  archived_dist_open_summary = 
                  (            
                      SELECT 
                          count(id) 
                      FROM   
                          pressrelease_notify_archive
                      WHERE
                          onid = $onid
                      AND
                          ontable = 'pressrelease_entry'
                      AND
                          evtype = 'MAIL'
                      AND
                          is_open = 1 
                  )    
              WHERE
                   id = $onid
             ");        
                           
        $p->query("COMMIT");
        
        
        // 100000 in (10.52 sec) (249 days)
        // 500000 in (11-35 sec) (150 days)
        
        $p = DB_DataObject::factory('Events');
        $p->query("
            DELETE FROM
                 pressrelease_notify_archive 
            WHERE
                act_start < NOW() - INTERVAL 2 YEAR
            ORDER BY
                id ASC
            LIMIT
                50000
        ");
                
        
        
        //$p->query("DROP TEMPORARY TABLE IF EXISTS $temp_table");
        
        $end_time = date("Y-m-d H:i:s");
        echo "$end_time : Finish archive onid: $onid\r\n";    
        
        
        
    }
 
 
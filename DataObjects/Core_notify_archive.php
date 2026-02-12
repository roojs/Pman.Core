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
 
 
    function archive($months)
    {
        
        $start_time = date("Y-m-d H:i:s");
        echo "$start_time : Start archive core notify\r\n";

        $pe = DB_DataObject::factory('core_notify');
        $pe->whereAdd("act_when < NOW() - INTERVAL {$months} MONTH");
        $pe->orderBy('id ASC');
        $pe->limit(100000);
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
                 IN   (". implode(',', $eids) . ")
             ");
             
     
        // looping seems to be far quicker than IN() or any other version..
        foreach($ids as $eid) {     
            $p->query("                        
                      DELETE FROM  
                          Events 
                      WHERE   
                          id = {$eid}
            ");
        }
                   
        $p->query("                        
                  DELETE FROM  
                      core_notify                        
                  WHERE
                      id in (" . implode(',', $ids) . ")
              ");       
                    
        $p->query("COMMIT");
        
        
        // 100000 in (10.52 sec) (249 days)
        // 500000 in (11-35 sec) (150 days)
        
        
        
        
        //$p->query("DROP TEMPORARY TABLE IF EXISTS $temp_table");
        
        $end_time = date("Y-m-d H:i:s");
        echo "$end_time : Finish archive on core noify\r\n";    
        
        
        
    }

    /**
     * Delete old rows that failed to be delivered (no msgid), older than $months.
     * Default 6 months. One batch per call (50000).
     */
    function deleteOldFailed($months = 6)
    {
        $months = (int) $months;
        $tn = $this->tableName();
        $this->query("
            DELETE FROM
                {$tn}
            WHERE
                (msgid IS NULL OR msgid = '' OR LENGTH(msgid) = 0)
            AND
                act_when < NOW() - INTERVAL {$months} MONTH
            ORDER BY
                id ASC
            LIMIT
                50000
        ");
    }
 
}
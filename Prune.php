<?php


/**
 * Description of Prune
 *
 * @author chris
 */

require_once 'Pman.php';
class Pman_Core_Prune extends Pman
{
    //put your code here
    static $cli_desc = "Core Prune -- remove old event data (6 months is normally a good idea).";
    static $cli_opts = array(
        'months' => array(
            'desc' => 'How many months',
            //'default' => 0,
            'short' => 'm',
            'min' => 1,
            'max' => 1,
            
        )
    );
    var $cli = false;
    
    function getAuth() {
        
         
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->cli)) {
            $this->cli = true;
            return true;
        }
        
 //        return true;// for test only
        return false;
    }
    
    function get($m="", $opts=array())
    {
         // prune irrelivant stuff..
       
        
        
        $this->prune((int)$opts['months']);
    }
    
    function prune($inM)
    {
        // 40 seconds ? to delete 100K records..
       // DB_DataObject::debugLevel(1);
       /*
        $f = DB_DataObject::Factory('Events');
        $f->query("
            DELETE FROM Events where 
                  event_when < NOW() - INTERVAL {$inM} MONTH
                  AND
                  action != 'NOTIFY'
                  LIMIT 100000
        ");
        */
        // notificication events occur alot - so we should trash them more frequently..
      /*  $f = DB_DataObject::Factory('reader_article');
        $f->query("
            DELETE FROM Events where 
                  event_when < NOW() - INTERVAL 1 MONTH
                  AND
                  action IN ('NOTIFY')
                  LIMIT 100000
        ");
        */
        // rather than deleting them all, it's probably best to just delete notify events that occured to often.
        // eg. when we tried to deliver multiple times without success...
        /*
         *
         SELECT on_id, on_table, min(id) as min_id, max(id) as max_id, count(*) as mm FROM Events
         WHERE action = 'NOTIFY' and event_when < NOW() - INTERVAL 1 WEEK GROUP BY  on_id, on_table HAVING  mm > 2 ORDER BY mm desc;
         */
        
        //DB_DataObject::debugLevel(1);
        $f = DB_DataObject::Factory('Events');
        $before = $f->count();

        $f = DB_DataObject::Factory('Events');
        $f->selectAdd();
        $f->selectAdd("on_id, on_table, min(id) as min_id, max(id) as max_id, count(*) as mm");
        $f->whereAdd("action = 'NOTIFY' and event_when < NOW() - INTERVAL 1 WEEK");
        $f->groupBy('on_id, on_table');
        $f->having("mm > 2");
        $f->orderBy('mm desc') ;
        $f->limit(10000);
        $ar = $f->fetchAll();

        foreach($ar as $f) {
            $q = DB_DataObject::Factory('Events');
            $q->query("DELETE FROM Events where 
                  action = 'NOTIFY'
                  AND
                  on_id = {$f->on_id}
                  AND
                  on_table = '{$q->escape($f->on_table)}'
                  AND
                  id >  {$f->min_id}  -- allow the first one to stay....
                  AND
                  id <= {$f->max_id}
            ");
        }
        
        $f = DB_DataObject::Factory('Events');
        $after = $f->count();
        echo "DELETED : " . ($before - $after) . " records\n";

        // just delete all files for events after 6 months?
        // probably ok - as we only use them to debug (and if we have a backup working - they will be there)
        
        $ce = DB_DataObject::Factory('core_events_archive');
        $ce->deleteUserFiles($inM);
        
    }
}
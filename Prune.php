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
            
        ),
        'test' => array(
            'desc' => 'what to test? Events, Notify',
            'default' => '',
            'short' => 't',
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
       
        $f = DB_DataObject::Factory('Events');
        $this->events_before = $f->count();
        

        if (!empty($opts['test'])) {
            $m = "prune{$opts['test']}";
            if (!method_exists($this, $m)) {
                die("invalid test method $m\n");
            }
            $this->$m((int)$opts['months']);
            die("done\n");

        }
      

        $this->prune((int)$opts['months']);
    }
    var $events_before = 0;

    function prune($inM)
    {
      
        
        //DB_DataObject::debugLevel(1);
       
      
        $this->pruneEventDupes();
        $this->archiveNotify($inM);
        $this->archiveEvents($inM);
    }
     

    }
    function pruneEventDupes($inM)
    {
        /// deletes events on 'NOTIFY' that are dupes..
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
                  id != {$f->min_id} 
                  
            ");
        }
    }
        // clean up archiver 
    
    function pruneNotify($inM)
    {
        $f = DB_DataObject::Factory('core_notify');
        $nbefore = $f->count();
        $cn  = DB_DataObject::Factory('core_notify_archive');
        $cn->archive($inM);
        $f = DB_DataObject::Factory('core_notify');
        $nafter = $f->count();
        
        echo "DELETED : " . ($nbefore - $nafter) . "/{$nbefore} core_notify records\n";
    }
    function pruneEvents($inM)
    {
        
        
        $ce = DB_DataObject::Factory('core_events_archive');
        $ce->moveToArchive($inM);
        $ce->deleteUserFiles($inM);
       
        $f = DB_DataObject::Factory('Events');
        $after = $f->count();
        echo "DELETED : " . ($this->events_before - $after) . "/{$this->events_before} events records\n";

    }
}
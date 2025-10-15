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
      
        
        //DB_DataObject::debugLevel(1);
        $f = DB_DataObject::Factory('Events');
        $before = $f->count();
        
        
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
        
        
        // clean up archiver 
        
        $f = DB_DataObject::Factory('core_notify');
        $nbefore = $f->count();
        $cn  = DB_DataObject::Factory('core_notify_archive');
        $cn->archive($inM);
        $f = DB_DataObject::Factory('core_notify');
        $nafter = $f->count();
        
        echo "DELETED : " . ($nbefore - $nafter) . "/{$nbefore} core_notify records\n";

        
        
        $ce = DB_DataObject::Factory('core_events_archive');
        $ce->moveToArchive($inM);
        $ce->deleteUserFiles($inM);
       
        $f = DB_DataObject::Factory('Events');
        $after = $f->count();
        echo "DELETED : " . ($before - $after) . "/{$before} events records\n";

    }
}
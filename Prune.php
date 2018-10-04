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
        
        DB_DataObject::debugLevel(1);
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
                  id >= {$f->min_id}
                  AND
                  id <= {$f->max_id}
            ");
        }
        
        

        
        
        
        // pruning is for our press project - so we do not clean up dependant tables at present..
        
        if (function_exists('posix_getpwuid')) {
            $uinfo = posix_getpwuid( posix_getuid () ); 
         
            $user = $uinfo['name'];
        } else {
            $user = getenv('USERNAME'); // windows.
        }
        
        $ff = HTML_Flexyframework::get()->Pman;
        
        $y = date("Y");
        $m = date("m");
        $rootDir = $ff['storedir'].'/_events_/'.$user;
        
        $dirs = array_filter(glob($rootDir."/*"), 'is_dir');
        foreach($dirs as $d){
            $mdirs = array_filter(glob($d."/*"), 'is_dir');
            foreach($mdirs as $md){
                $dirDate = str_replace($rootDir."/", '', $md);
                if(strtotime($dirDate."/01") < strtotime("now - {$inM} months")){
                    //echo "remove $md\n";
                    $this->delTree($md);
                      //  echo $md . " is removed. \n";
                    
                }
            }
        }
        
        exit;
    }
    
    function delTree($dir)
    {
        $files = array_diff(scandir($dir), array('.','..'));
        echo "$dir : Removing " . count($files) . " files\n";
        clearstatcache();
        foreach ($files as $file){
            if (!file_exists("$dir/$file")) {
                continue;
            }
            if (is_dir("$dir/$file")) {
                $this->delTree("$dir/$file");
                continue;
            }
            unlink("$dir/$file");
        }
        return rmdir($dir); 
    }
    
}

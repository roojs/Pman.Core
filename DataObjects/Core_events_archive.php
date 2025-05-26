<?php
/**
 * Table Definition for core_events_archive
 */
require_once 'Events.php';

class Pman_Core_DataObjects_Core_events_archive extends Pman_Core_DataObjects_Events
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_events_archive';    // table name
    
    function deleteUserFiles($months)
    {
        
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
    function archiveEvents($ids)
    {
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
                   
    }
    
    
    function moveToArchive($month)
    {
        $month = inval($month);
        if ($month < 3) {
            return;
        }
        $e = DB_DataObject::factory('Events');
        $e->whereAdd("event_when < NOW() - INTERVAL {$month} MONTH");
        $e->orderBy('id ASC');
        $e->limit(10000);
        $ids = $e->fetchAll('id');
        $this->archiveEvents($ids);
        
    }
         
        
    
}
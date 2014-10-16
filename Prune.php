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
    static $cli_desc = "COre Prune -- remove old event data (6 months is normally a good idea).";
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
    
    function get($m="", $opts)
    {
        
        $this->prune((int)$opts['months']);
    }
    
    function prune($inM)
    {
        // 40 seconds ? to delete 100K records..
        //DB_DataObject::debugLevel(1);
        $f = DB_DataObject::Factory('reader_article');
        $f->query("
            DELETE FROM Events where 
                  event_when < NOW() - INTERVAL {$inM} MONTH
                  LIMIT 100000
        ");
        // pruning is for our press project - so we do not clean up dependant tables at present..
        
        
        
        $ff = HTML_Flexyframework::get()->Pman;
        
        $y = date("Y");
        $m = date("m");
        $rootDir = $ff['storedir'].'/rss';
        
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

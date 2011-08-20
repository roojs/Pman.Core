<?php



require_once 'Pman.php';
class Pman_Core_UpdateDatabase extends Pman
{
    
    static $cli_desc = "Update SQL - Beta";
 
 
  
    var $cli = false;
    function getAuth() {
        
        
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->cli)) {
            $this->cli = true;
            return true;
        }
        
        parent::getAuth(); // load company!
        $au = $this->getAuthUser();
        if (!$au || $au->company()->comptype != 'OWNER') {
            $this->jerr("Not authenticated", array('authFailure' => true));
        }
        $this->authUser = $au;
        return true;
    }
     
    function get()
    {
        $this->importSQL();
        die("done");
    }
     /**
     * imports SQL files from all DataObjects directories....
     * 
     * except any matching /migrate/
     */
    function importSQL()
    {
        
        $ff = HTML_Flexyframework::get();
        
        $url = parse_url($ff->DB_DataObject['database']);
        // hide stuff for web..
        
        require_once 'System.php';
        $cat = System::which('cat');
        $mysql = System::which('mysql');
        
        $ar = $this->modulesList();
        
           
            $mysql_cmd = $mysql .
                ' -h ' . $url['host'] .
                ' -u' . escapeshellarg($url['user']) .
                (!empty($url['pass']) ? ' -p' . escapeshellarg($url['pass'])  :  '') .
                ' ' . basename($url['path']);
        echo $mysql_cmd . "\n" ;
        
        
        
        
        foreach($ar as $fl) {
            
            $fd = $this->rootDir. "/Pman/$m/DataObjects/$fl";
            
            foreach(glob($fd.'/*.sql') as $f) {
                
                $fn = "$fd/$f";
                
                if (preg_match('/migrate/i', $f)) { // skip migration scripts at present..
                    continue;
                }
                
                $cmd = $cat . ' ' . escapeshellarg($fn) . " | $mysql_cmd -f ";
                
                echo $cmd. ($cli ? "\n" : "<BR>\n");
                
                passthru($cmd);
            
                
            }
        }
        
        
        
    }
    
}
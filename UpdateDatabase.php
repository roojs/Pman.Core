<?php



require_once 'Pman.php';
class Pman_Core_UpdateDatabase extends Pman
{
    
    static $cli_desc = "Update SQL - Beta";
 
class Pman_Core_RunGenerator extends Pman
{     
    
  
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
        
    }
     /**
     * imports SQL files from all DataObjects directories....
     * 
     * except any matching /migrate/
     */
    function importSQL()
    {
        $options = &PEAR::getStaticProperty('DB_DataObject','options');
        
        $ff = HTML_Flexyframework::get();
        
        $url = parse_url($options['database']);
        // hide stuff for web..
        $cli = $options['cli'];
        if (!$cli) {
            $url['pass'] = '*****';
            $url['user'] = '*****';
            $url['host'] = '*****';
        }
        
        require_once 'System.php';
        $cat = System::which('cat');
        $mysql = System::which('mysql');
        print_r($options['mods'] );
        foreach($this->modsql as $m => $fl)
        {
            if ($cli && isset($options['database_'. $m])) {
                $url = parse_url($options['database_'.$m]);
            }
            
            $mysql_cmd = $mysql .
                ' -h ' . $url['host'] .
                ' -u' . escapeshellarg($url['user']) .
                (!empty($url['pass']) ? ' -p' . escapeshellarg($url['pass'])  :  '') .
                ' ' . basename($url['path']);
           
            echo $mysql_cmd . "\n" ;
            
            if (!empty($options['mods'] ) && !in_array($m,  $options['mods'] )) {
                continue;
            }
            
            foreach($fl as $f) {
                $fn = $ff->page->rootDir. "/Pman/$m/DataObjects/$f";
                if (preg_match('/migrate/i', $f)) { // skip migration scripts at present..
                    continue;
                }
                
                $cmd = $cat . ' ' . escapeshellarg($fn) . " | $mysql_cmd -f ";
                echo $cmd. ($cli ? "\n" : "<BR>\n");
                if ($cli) {
                    passthru($cmd);
                }
                
            }
        }
        
        
        
    }
    
}
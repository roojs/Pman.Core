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
         
    }
    function output() {
        return '';
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
        
        $this->{'import' . $url['scheme']}($url);
        
    }
    function importmysql($url)
    {
        
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
        
        
        
        
        foreach($ar as $m) {
            
            $fd = $this->rootDir. "/Pman/$m/DataObjects";
            
            foreach(glob($fd.'/*.sql') as $fn) {
                
                 
                if (preg_match('/migrate/i', basename($fn))) { // skip migration scripts at present..
                    continue;
                }
                // .my.sql but not .pg.sql
                if (preg_match('/\.[a-z]+\.sql/i', basename($bfn))
                    && !preg_match('/\.my\.sql/i', basename($bfn))
                ) { // skip migration scripts at present..
                    continue;
                }
                $cmd = "$mysql_cmd -f < " . escapeshellarg($fn) ;
                
                echo $cmd. ($this->cli ? "\n" : "<BR>\n");
                
                passthru($cmd);
            
                
            }
        }
        
        
        
    }
    /**
     * postgresql import..
     */
    function importpgsql($url)
    {
        
        // hide stuff for web..
        
        require_once 'System.php';
        $cat = System::which('cat');
        $psql = System::which('psql');
        
        $ar = $this->modulesList();
        if (!empty($url['pass'])) { 
            putenv("PGPASSWORD=". $url['pass']);
        }
           
        $psql_cmd = $psql .
            ' -h ' . $url['host'] .
            ' -U' . escapeshellarg($url['user']) .
             ' ' . basename($url['path']);
        echo $psql_cmd . "\n" ;
        
        
        
        
        foreach($ar as $m) {
            
            $fd = $this->rootDir. "/Pman/$m/DataObjects";
            
            foreach(glob($fd.'/*.sql') as $bfn) {
                
                 
                if (preg_match('/migrate/i', basename($bfn))) { // skip migration scripts at present..
                    continue;
                }
                
                // files ending in .pg.sql are native postgres files..
                $fn = preg_match('/\.pg\.sql$/', $bfn) ? false : $this->convertToPG($bfn);
                
                $cmd = "$psql_cmd -f  " . escapeshellarg($fn ? $fn : $bfn) . ' 2>&1' ;
                
                echo "$bfn:   $cmd ". ($this->cli ? "\n" : "<BR>\n");
                
                
                $res = `$cmd`;
                
                if ($fn) {
                    unlink($fn);
                }
            }
        }
        
    }
    /**
     * simple regex based convert mysql to pgsql...
     */
    function convertToPG($src)
    {
        $fn = $this->tempName('sql');
        
        $ret = array( ); // pad it a bit.
        $extra = array("", "" );
        
        $tbl = false;
        foreach(file($src) as $l) {
            $l = trim($l);
            
            if (!strlen($l) || $l[0] == '#') {
                continue;
            }
            $m = array();
            if (preg_match('#create\s+table\s+([a-z0-9_]+)#i',  $l, $m)) {
                $tbl = $m[1];
               // $extra[]  =   "drop table {$tbl};";
             }
            // autoinc
            if ($tbl && preg_match('#auto_increment#i',  $l, $m)) {
                $l = preg_replace('#auto_increment#i', "default nextval('{$tbl}_seq')", $l);
                $extra[]  =   "create sequence {$tbl}_seq;";
              
            }
            $m = array();
            if (preg_match('#alter\s+table\s+([a-z0-9_]+)\s+add\s+index\s+([^(]+)(.*)$#i',  $l, $m)) {
               $l = "CREATE INDEX  {$m[1]}_{$m[2]} ON {$m[1]} {$m[3]}";
             }
            // ALTER TABLE core_event_audit ADD     INDEX looku
            // CREATE INDEX 
            
            // basic types..
            $l = preg_replace('#int\([0-9]+\)#i', 'INT', $l);
            
            $l = preg_replace('# datetime #i', ' TIMESTAMP WITHOUT TIME ZONE ', $l);
            $l = preg_replace('# blob #i', ' TEXT ', $l);
             $l = preg_replace('# longtext #i', ' TEXT ', $l);
            //$l = preg_match('#int\([0-9]+\)#i', 'INT', $l);
                            
            $ret[] = $l;
            
            
            
            
            
            
            
        }
        $ret = array_merge($extra,$ret);
        
        //echo implode("\n", $ret); //exit;
        file_put_contents($fn, implode("\n", $ret));
        
        return $fn;
    }
                
    
}
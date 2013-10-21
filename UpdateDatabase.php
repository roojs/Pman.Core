<?php

/**
 *
 * This applies database files from
 * a) OLD - {MODULE}/DataObjects/XXXX.{dbtype}.sql
 *
 * b) NEW - {MODULE}/sql/XXX.sql (SHARED or translable)
 *  and {MODULE}/{dbtype}/XXX.sql (SHARED or translable)
 *
 *
 */

require_once 'Pman.php';
class Pman_Core_UpdateDatabase extends Pman
{
    
    static $cli_desc = "Update SQL - Beta (it will run updateData of all modules)";
 
    static $cli_opts = array(
        'source' => array(
            'desc' => 'Source directory for json files.',
            'short' => 'f',
            'default' => '',
            'min' => 1,
            'max' => 1,
        ),
        'prefix' => array(
            'desc' => 'prefix for the passwrod',
            'short' => 'p',
            'default' => '',
            'min' => 1,
            'max' => 1,
        ),
        'name' => array(
            'desc' => 'name of the company',
            'short' => 'n',
            'default' => '',
            'min' => 1,
            'max' => 1,
        ),
        'comptype' => array(
            'desc' => 'the type of company',
            'short' => 't',
            'default' => '',
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
        
        parent::getAuth(); // load company!
        $au = $this->getAuthUser();
        if (!$au || $au->company()->comptype != 'OWNER') {
            $this->jerr("Not authenticated", array('authFailure' => true));
        }
        $this->authUser = $au;
        return true;
    }
     
    function get($args, $opt)
    {
        if($args == 'Person'){
            if(empty($opt['source']) || empty($opt['prefix'])){
                die("Missing Source directory for person json files or prefix for the passwrod! Try -f [JSON file path] -p [prefix] \n");
            }
            if (!file_exists($opt['source'])) {
                die("can not found person json file : {$opt['source']} \n");
            }
            
            $persons = json_decode(file_get_contents($opt['source']),true);
            
            DB_DataObject::factory('person')->importFromArray($this, $persons, $opt['prefix']);
            die("DONE! \n");
        }
        
        if($args == 'Company'){
            if(empty($opt['name']) || empty($opt['comptype'])){
                die("Missing company name or type! Try --name=[the name of company] -- comptype=[the type of company] \n");
            }
            
            DB_DataObject::factory('companies')->initCompanies($this, $opt['name'], $opt['comptype']);
            
            die("DONE! \n");
        }
        
        $this->importSQL();
        $this->runUpdateModulesData();
         
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
    
    /**
     * mysql - does not support conversions.
     * 
     *
     */
    
    
    function importmysqldir($dburl, $dir)
    {
        
        require_once 'System.php';
        $cat = System::which('cat');
        $mysql = System::which('mysql');
        
       
           
        $mysql_cmd = $mysql .
            ' -h ' . $url['host'] .
            ' -u' . escapeshellarg($dburl['user']) .
            (!empty($dburl['pass']) ? ' -p' . escapeshellarg($dburl['pass'])  :  '') .
            ' ' . basename($dburl['path']);
        echo $mysql_cmd . "\n" ;
       
       
        foreach(glob($dir.'/*.sql') as $fn) {
                
                 
                if (preg_match('/migrate/i', basename($fn))) { // skip migration scripts at present..
                    continue;
                }
                // .my.sql but not .pg.sql
                if (preg_match('#\.[a-z]{2}\.sql#i', basename($fn))
                    && !preg_match('#\.my\.sql#i', basename($fn))
                ) { // skip migration scripts at present..
                    continue;
                }
                $cmd = "$mysql_cmd -f < " . escapeshellarg($fn) ;
                
                echo $cmd. ($this->cli ? "\n" : "<BR>\n");
                
                passthru($cmd);
            
                
        }
       
        
        
    }
    
    
    
    function importmysql($dburl)
    {
        
        // hide stuff for web..
        $ar = $this->modulesList();
        
         
        
        // old -- DAtaObjects/*.sql
        
        foreach($ar as $m) {
            
            $fd = $this->rootDir. "/Pman/$m/DataObjects";
            
            $this->importmysqldir($dburl, $fd);
            
            // new -- sql directory..
            // new style will not support migrate ... they have to go into mysql-migrate.... directories..
            // new style will not support pg.sql etc.. naming - that's what the direcotries are for..
            
            $this->importmysqldir($dburl, $this->rootDir. "/Pman/$m/sql");
            $this->importmysqldir($dburl, $this->rootDir. "/Pman/$m/mysql");
              
            
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
                if (preg_match('#\.[a-z]{2}\.sql#i', basename($bfn))
                    && !preg_match('#\.pg\.sql#i', basename($bfn))
                ) { // skip migration scripts at present..
                    continue;
                }
                // files ending in .pg.sql are native postgres files..
                $fn = preg_match('#\.pg\.sql$#', basename($bfn)) ? false : $this->convertToPG($bfn);
                
                $cmd = "$psql_cmd  < " . escapeshellarg($fn ? $fn : $bfn) . ' 2>&1' ;
                
                echo "$bfn:   $cmd ". ($this->cli ? "\n" : "<BR>\n");
                
                
                passthru($cmd);
                
                if ($fn) {
                    unlink($fn);
                }
            }
            
            
            
            $fd = $this->rootDir. "/Pman/$m/sql";
            // sql directory  - we try to convert..
            foreach(glob($fd.'/*.sql') as $bfn) {
                $fn =  $this->convertToPG($bfn);
                $cmd = "$psql_cmd  < " . escapeshellarg($fn) ;
                echo $cmd. ($this->cli ? "\n" : "<BR>\n");
                passthru($cmd);
            }
            
            // postgres specific directory..
            
            $fd = $this->rootDir. "/Pman/$m/pgsql";
            
            foreach(glob($fd.'/*.sql') as $fn) {
                $cmd = "$psql_cmd   < " . escapeshellarg($fn) ;
                echo $cmd. ($this->cli ? "\n" : "<BR>\n");
                passthru($cmd);
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
    
    function runUpdateModulesData()
    {
        // runs core...
        $this->updateData(); 
        $modules = array_reverse($this->modulesList());
        
        // move 'project' one to the end...
        
        foreach ($modules as $module){
            $file = $this->rootDir. "/Pman/$module/UpdateDatabase.php";
            if($module == 'Core' || !file_exists($file)){
                continue;
            }
            require_once $file;
            $class = "Pman_{$module}_UpdateDatabase";
            $x = new $class;
            if(!method_exists($x, 'updateData')){
                continue;
            };
            $x->updateData();
        }
                
    }
    
    
    function updateDataEnums()
    {
        $enum = DB_DataObject::Factory('core_enum');
        $enum->initEnums(
            array(
                array(
                    'etype' => '',
                    'name' => 'COMPTYPE',
                    'display_name' =>  'Company Types',
                    'is_system_enum' => 1,
                    'cn' => array(
                        array(
                            'name' => 'OWNER',
                            'display_name' => 'Owner',
                            'seqid' => 999, // last...
                            'is_system_enum' => 1,
                        )
                        
                    )
                ),
                array(
                    'etype' => '',
                    'name' => 'HtmlEditor.font-family',
                    'display_name' =>  'HTML Editor font families',
                    'is_system_enum' => 1,
                    'cn' => array(
                        array(
                            'name' => 'Helvetica,Arial,sans-serif',
                            'display_name' => 'Helvetica',
                            
                        ),
                        
                        array(
                            'name' => 'Courier New',
                            'display_name' => 'Courier',
                             
                        ),
                        array(
                            'name' => 'Tahoma',
                            'display_name' => 'Tahoma',
                            
                        ),
                        array(
                            'name' => 'Times New Roman,serif',
                            'display_name' => 'Times',
                           
                        ),
                        array(
                            'name' => 'Verdana',
                            'display_name' => 'Verdana',
                            
                        ),
                        
                            
                        
                    )
                ),
            )
        ); 
        
    }
    function updateDataGroups()
    {
         
        $groups = DB_DataObject::factory('groups');
        $groups->initGroups();
        
        $groups->initDatabase($this,array(
            array(
                'name' => 'bcc-email', // group who are bcc'ed on all requests.
                'type' => 0, // system
            ),
            
        ));
        
    }
    
    function updateDataCompanies()
    {
         
        // fix comptypes enums..
        $c = DB_DataObject::Factory('Companies');
        $c->selectAdd();
        $c->selectAdd('distinct(comptype) as comptype');
        $c->whereAdd("comptype != ''");
        
        $ctb = array();
        foreach($c->fetchAll('comptype') as $cts) {
            
            
            
           $ctb[]= array( 'etype'=>'COMPTYPE', 'name' => $cts, 'display_name' => ucfirst(strtolower($cts)));
        
        }
         $c = DB_DataObject::Factory('core_enum');
         
        $c->initEnums($ctb);
        //DB_DataObject::debugLevel(1);
        // fix comptypeid
        $c = DB_DataObject::Factory('Companies');
        $c->query("
            UPDATE Companies 
                SET
                    comptype_id = (SELECT id FROM core_enum where etype='comptype' and name=Companies.comptype)
                WHERE
                    comptype_id = 0
                    AND
                    LENGTH(comptype) > 0
                  
                  
                  ");
         
        
        
    }
    
    function updateData()
    {
        // fill i18n data..
        
        $this->updateDataEnums();
        $this->updateDataGroups();
        $this->updateDataCompanies();
        
       
         
       
        
        
    }
    
    
}
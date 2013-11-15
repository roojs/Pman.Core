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
        ),
        'init' => array(
            'desc' => 'Initialize the database (pg only supported)',
            'short' => 'i',
            'default' => '',
            'min' => 1,
            'max' => 1,
        ),
        
    );
    
    static function cli_opts()
    {
        
        $ret = self::$cli_opts;
        $ff = HTML_FlexyFramework::get();
        $a = new Pman();
        $mods = $a->modulesList();
        foreach($ar as $m) {
            
            $fd = $ff->rootDir. "/Pman/$m/UpdateDatabase.php";
            if (!file_exists($fd)) {
                continue;
            }
            
            
        }
        
        
        return self::$cli_opts;
    }
    
    var $cli_options = false;
    
    
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
        
        $this->cli_options = $opt;
        
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
        echo "Import MYSQL :: $dir\n";
        
        
        require_once 'System.php';
        $cat = System::which('cat');
        $mysql = System::which('mysql');
        
       
           
        $mysql_cmd = $mysql .
            ' -h ' . $dburl['host'] .
            ' -u' . escapeshellarg($dburl['user']) .
            (!empty($dburl['pass']) ? ' -p' . escapeshellarg($dburl['pass'])  :  '') .
            ' ' . basename($dburl['path']);
        //echo $mysql_cmd . "\n" ;
       
       
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
                if (!strlen(trim($fn))) {
                    continue;
                }
                
                $cmd = "$mysql_cmd -f < " . escapeshellarg($fn) ;
                
                echo basename($dir).'/'. basename($fn) .    '::' .  $cmd. ($this->cli ? "\n" : "<BR>\n");
                
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
    function importpgsql($dburl)
    {
        
        // hide stuff for web..
        
        
       
        
        $ar = $this->modulesList();
       
        foreach($ar as $m) {
            
            // if init has been called
            // look in pgsql.ini
            if (!empty($this->cli_options['init'])) {
                $this->importpgsqldir($dburl, $this->rootDir. "/Pman/$m/pgsql.init");
                
            }
            
            
            
            $fd = $this->rootDir. "/Pman/$m/DataObjects";
            
            $this->importpgsqldir($dburl, $fd);
            
            // new -- sql directory..
            // new style will not support migrate ... they have to go into mysql-migrate.... directories..
            // new style will not support pg.sql etc.. naming - that's what the direcotries are for..
            
            $this->importpgsqldir($dburl, $this->rootDir. "/Pman/$m/sql");
            $this->importpgsqldir($dburl, $this->rootDir. "/Pman/$m/pgsql");
            
            
            
            if (!empty($this->cli_options['init']) && file_exists($this->rootDir. "/Pman/$m/pgsql.initdata")) {
                HTML_FlexyFramework::get()->generateDataobjectsCache(true);
                
                $this->importpgsqldir($dburl, $this->rootDir. "/Pman/$m/pgsql.initdata");
                $this->fixSequencesPgsql();
                
            }
              
            
        }
       
          
    }
    function importpgsqldir($url, $dir, $disable_triggers = false)
    {
        require_once 'System.php';
        $cat = System::which('cat');
        $psql = System::which('psql');
        
         
        if (!empty($url['pass'])) { 
            putenv("PGPASSWORD=". $url['pass']);
        }
           
        $psql_cmd = $psql .
            ' -h ' . $url['host'] .
            ' -U' . escapeshellarg($url['user']) .
             ' ' . basename($url['path']);
        
        
        echo $psql_cmd . "\n" ;
        echo "scan : $dir\n";
        foreach(glob($dir.'/*.sql') as $bfn) {


            if (preg_match('/migrate/i', basename($bfn))) { // skip migration scripts at present..
                continue;
            }
            if (preg_match('#\.[a-z]{2}\.sql#i', basename($bfn))
                && !preg_match('#\.pg\.sql#i', basename($bfn))
            ) { // skip migration scripts at present..
                continue;
            }
            $fn = false;

            if (!preg_match('/pgsql/', basename($dir) )) {
                 if ( !preg_match('#\.pg\.sql$#', basename($bfn))) {
                    $fn = $this->convertToPG($bfn);
                }
            }

            // files ending in .pg.sql are native postgres files.. ## depricated


            $cmd = "$psql_cmd  < " . escapeshellarg($fn ? $fn : $bfn) . ' 2>&1' ;

            echo "$bfn:   $cmd ". ($this->cli ? "\n" : "<BR>\n");


            passthru($cmd);

            if ($fn) {
                unlink($fn);
            }
        }

              
             
        
    }
    /**
     * simple regex based convert mysql to pgsql...
     */
    function convertToPG($src)
    {
        //echo "Convert $src\n";
               
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
             }
            if (preg_match('#create\s+table\s+\`([a-z0-9_]+)\`#i',  $l, $m)) {
                $tbl = 'shop_' . strtolower($m[1]);
                $l = preg_replace('#create\s+table\s+\`([a-z0-9_]+)\`#i', "CREATE TABLE {$tbl}", $l);
            }
            if (preg_match('#\`([a-z0-9_]+)\`#i',  $l, $m) && !preg_match('#alter\s+table\s+#i',  $l)) {
                $l = preg_replace('#\`([a-z0-9_]+)\`#i', "{$m[1]}_name", $l);
            }
            // autoinc
            if ($tbl && preg_match('#auto_increment#i',  $l, $m)) {
                $l = preg_replace('#auto_increment#i', "default nextval('{$tbl}_seq')", $l);
                $extra[]  =   "create sequence {$tbl}_seq;";
              
            }
            
            if (preg_match('#alter\s+table\s+(\`[a-z0-9_]+\`)#i',  $l, $m)){
                $l = preg_replace('#alter\s+table\s+(\`[a-z0-9_]+\`)#i', "ALTER TABLE {$tbl}", $l);
            }
            
            // enum value -- use the text instead..
            
            if ($tbl && preg_match('#([\w]+)\s+(enum\([\w|\W]+\))#i',  $l, $m)) {
                $l = preg_replace('#enum\([\w|\W]+\)#i', "TEXT", $l);
            }
            // ignore the alter enum
            if ($tbl && preg_match('#alter\s+table\s+([\w|\W]+)\s+enum\([\w|\W]+\)#i',  $l, $m)) {
                continue;
            }
            
            // UNIQUE KEY .. ignore
            if ($tbl && preg_match('#UNIQUE KEY#i',  $l, $m)) {
                $last = array_pop($ret);
                $ret[] = trim($last, ",");
                continue;
            }
            
            if ($tbl && preg_match('#RENAME\s+TO#i',  $l, $m)) {
                continue;
            }
            
            if ($tbl && preg_match('#change\s+column#i',  $l, $m)) {
                continue;
            }
            
            // INDEX lookup ..ignore
            if ($tbl && preg_match('#INDEX lookup+([\w|\W]+)#i',  $l, $m)) {
               $last = array_pop($ret);
               $ret[] = trim($last, ",");
               continue;
               
            }
            
            // CREATE INDEX ..ignore
            if (preg_match('#alter\s+table\s+([a-z0-9_]+)\s+add\s+index\s+#i',  $l, $m)) {
//               $l = "CREATE INDEX  {$m[1]}_{$m[2]} ON {$m[1]} {$m[3]}";
                continue;
             }
             
            // basic types..
            $l = preg_replace('#int\([0-9]+\)#i', 'INT', $l);
            
            $l = preg_replace('# datetime#i', ' TIMESTAMP WITHOUT TIME ZONE', $l);
            $l = preg_replace('# blob#i', ' TEXT', $l);
            $l = preg_replace('# longtext#i', ' TEXT', $l);
            $l = preg_replace('# tinyint#i', ' INT', $l);
            
            $ret[] = $l;
            
        }
        
        $ret = array_merge($extra,$ret);
//        echo implode("\n", $ret); exit;
        
        file_put_contents($fn, implode("\n", $ret));
        
        return $fn;
    }
    
    function runUpdateModulesData()
    {
        HTML_FlexyFramework::get()->generateDataobjectsCache(true);
               
        
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
        
        $c = DB_DataObject::Factory('I18n');
        $c->buildDB();
         
       
        
        
    }
    function fixSequencesPgsql()
    {
        DB_DataObject::debugLevel(1);
        $cs = DB_DataObject::factory('core_enum');
         $cs->query("
               SELECT  'SELECT SETVAL(' ||
                         quote_literal(quote_ident(nspname) || '.' || quote_ident(S.relname)) ||
                        ', MAX(' || quote_ident(C.attname)|| ') )  FROM ' || nspname || '.' || quote_ident(T.relname)|| ';' as cmd 
                FROM pg_class AS S,
                    pg_depend AS D,
                    pg_class AS T,
                    pg_attribute AS C,
                    pg_namespace AS NS
                WHERE S.relkind = 'S'
                    AND S.oid = D.objid
                    AND D.refobjid = T.oid
                    AND D.refobjid = C.attrelid
                    AND D.refobjsubid = C.attnum
                    AND NS.oid = T.relnamespace
                ORDER BY S.relname;     
        ");
        while ($cs->fetch()) {
            $cmds[] = $cs->cmd;
        }
        foreach($cmds as $cmd) {
            $cs = DB_DataObject::factory('core_enum');
            $cs->query($cmd);
        }
        
         
        
        
    }
    
}
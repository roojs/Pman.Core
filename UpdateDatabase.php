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
      
        'prefix' => array(
            'desc' => 'prefix for the password (eg. fred > xxx4fred - prefix is xxx4)',
            'short' => 'p',
            'default' => '',
            'min' => 1,
            'max' => 1,
        ),
        'data-only' => array(
            'desc' => 'only run the updateData - do not run import the tables and procedures.',
            'short' => 'p',
            'default' => '',
            'min' => 1,
            'max' => 1,
            
        ),
        'add-company' => array(
            'desc' => 'add a company name of the company',
            'short' => 'n',
            'default' => '',
            'min' => 1,
            'max' => 1,
        ),
        'add-company-with-type' => array(
            'desc' => 'the type of company (default OWNER)',
            'short' => 't',
            'default' => 'OWNER',
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
        'only-module-sql' => array(
            'desc' => 'Only run sql import on this modules - eg. Core',
            'default' => '',
            'min' => 1,
            'max' => 1,
        ),
        'only-module-sql-table' => array(
            'desc' => 'Only run sql import on this table - eg. core_domain',
            'default' => '',
            'min' => 1,
            'max' => 1,
        ),
        'skip-mysql-checks' => array(
            'desc' => 'Skip mysql checks',
            'default' => '',
            'min' => 1,
            'max' => 1,
        ),
        'skip-email-import' => array(
            'desc' => 'Skip email import',
            'default' => '',
            'min' => 1,
            'max' => 1,
        ),
        'procedures-only' => array(
            'desc' => 'Only import procedures (not supported by most modules yet) - ignores sql directory',
            'default' => '',
            'min' => 1,
            'max' => 1,
        ),
        'disable-create-triggers' => array(
            'desc' => 'So not create the mysql triggers',
            'default' => '',
            'min' => 1,
            'max' => 1,
        ),
        
        'json-person' => array(
            'desc' => 'Person JSON file',
            'default' => '',
            'min' => 1,
            'max' => 1,
            
        ),
        'debug' => array(
            'desc' => 'Debug the database',
            'short' => 'D',
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
        foreach($mods as $m) {
            
            $fd = $ff->rootDir. "/Pman/$m/UpdateDatabase.php";
            if (!file_exists($fd)) {
                continue;
            }
            
            require_once $fd;
            
            $cls = new ReflectionClass('Pman_'. $m . '_UpdateDatabase');
            $ar = $cls->getStaticProperties();
            if (isset($ar['cli_opts'])) {
                  
                $ret = array_merge($ret, $cls->getStaticPropertyValue('cli_opts'));
            }
        }
        
        return $ret;
    }
    
    var $opts = false;
    var $disabled = array();
    
    
    var $cli = false;
    
    var $local_base_url = false;
    
    var $emailTemplates = array(
        'EVENT_ERRORS_REPORT' => array(
            'bcc_group' => 'Empty Group',
            'test_class' => 'Pman/Admin/Report/SendEventErrors',
            'to_group' => 'Administrators',
            'active' => 1,
            'description' => '9.2 System Error Messages',
            'template_dir' => '/Pman/Admin/templates/mail/'
        ),
         'ADMIN_PASSWORD_RESET' => array(
            'bcc_group' => 'Administrators',
            'test_class' => 'Pman/Core/DataObjects/Core_person',
            'to_group' => '',
            'active' => 1,
            'description' => '9.1 Management System Password Reset',
            'template_dir' => '/Pman/Core/templates/mail/'
 
        )
    );
    
    var $enums = array(
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
        )
       
    );
    
    
    var $required_extensions = array(
        'curl',
        'gd',
        'mbstring'
    );
    
    function getAuth() {
        
        
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->cli)) {
            $this->cli = true;
            return true;
        }
        
        parent::getAuth(); // load company!
        $au = $this->getAuthUser();
        if (!$au || $au->company()->comptype != 'OWNER') {
            $this->jerror("LOGIN-NOAUTH", "Not authenticated", array('authFailure' => true));
        }
        $this->authUser = $au;
        return true;
    }
    
    function get($args, $opts=array())
    {
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'onPearError'));
   
        $this->checkSystem();
        
        $this->verifyExtensions($this->required_extensions);
        
        if (class_exists('PDO_DataObjects_Introspection')) {
            PDO_DataObject_Introspection::$cache = array();
        }
        echo "Generate DB　cache\n";
        HTML_FlexyFramework::get()->generateDataobjectsCache(true);
        echo "Generated DB　cache\n";
        $ff = HTML_FlexyFramework::get();
        
        if(!isset($ff->Pman) || !isset($ff->Pman['local_base_url'])){
            die("Please setup Pman[local_base_url]\n");
        }
        
        $this->local_base_url = $ff->Pman['local_base_url'];
        
        if(!empty($ff->Core_Notify)){
//            require_once 'Pman/Core/NotifySmtpCheck.php';
//            $x = new Pman_Core_NotifySmtpCheck();
//            $x->check();
        }
        
        $this->disabled = explode(',', $ff->disable);
        
        //$this->fixSequencesPgsql();exit;
        $this->opts = $opts;
        
        if (!empty($opts['debug'])) {
             DB_DataObject::DebugLevel($opts['debug']);
        }
        
        // ask all the modules to verify the opts
        echo "Checking options\n";
        $this->checkOpts($opts);
        
        $response = $this->curl("http://localhost{$this->local_base_url}/Core/UpdateDatabase/VerifyExtensions");
        $json = json_decode($response, true);
        
        if(empty($json['data']) || $json['data'] != 'DONE'){
            echo "\nError: Missing php extensions:\n";
            print_r($response);
            echo "Please install the above extensions and restart the apache.\n";
            sleep(5);
            
            //exit;
        }
        
        echo "Checking Setup Requirements\n";
        require_once 'Pman/Core/UpdateDatabase/VerifyConfig.php';
        $sq = new Pman_Core_UpdateDatabase_VerifyConfig();
        $ret = $sq->get($args, $opts);
        
        if(!empty($ret)){
            echo implode("\n", $ret) . "\n";
            sleep(30);
        }
        
        // do this first, so the innodb change + utf8 fixes column max sizes
        
        // this will trigger errors about freetext indexes - we will have to remove them manually.?
        // otherwise we need to do an sql query to find them, then remove them (not really worth it as it only affects really old code..)
        echo "Run extensions\n";

        $this->runExtensions(); 

        
        if (empty($opts['data-only'])) {
            echo "Import SQL\n";
            $this->importSQL();
        }
        if (!empty($opts['only-module-sql'])) {
            return;
        }
        
        echo "run Update Modules Data\n";

        $this->runUpdateModulesData();
        
        if (!empty($opts['add-company']) && !in_array('Core', $this->disabled)) {
            // make sure we have a good cache...?
           
            DB_DataObject::factory('core_company')->initCompanies($this, $opts);
        }
        
        $this->runExtensions();
        
        $this->clearApacheDataobjectsCache();
        
        $this->clearApacheAssetCache();
        
        
        
        
        
        
    }
    
    function output() {
        echo "\nUpdate Completed SUCCESS\n";
        return '';
    }
     /**
     * imports SQL files from all DataObjects directories....
     * 
     * except any matching /migrate/
     */
    function importSQL($modules = false)
    {
        
        // loop through all the modules, and see if they have a importSQL method?
        
        
        $ff = HTML_Flexyframework::get();
        
        $dburl = parse_url($ff->database); // used to be DB_DataObject['database'] - but not portable to PDO
        
        //$this->{'import' . $url['scheme']}($url);
        
        $dbtype = $dburl['scheme'];
        
        
        $dirmethod = 'import' . $dburl['scheme'] . 'dir';
        
        
       
        
        $ar = !empty($modules) ? $modules : $this->modulesList();
        
        
        foreach($ar as $m) {
            
            if(in_array($m, $this->disabled)){
                echo "module $m is disabled \n";
                continue;
            }
            
            //echo "Importing SQL from module $m\n";
            if (!empty($this->opts['only-module-sql']) && $m != $this->opts['only-module-sql']) {
                continue;
            }
            
            
            // check to see if the class has
            
            
            
            $file = $this->rootDir. "/Pman/$m/UpdateDatabase.php";
            if($m != 'Core' && file_exists($file)){
                
                require_once $file;
                $class = "Pman_{$m}_UpdateDatabase";
                $x = new $class;
                if(method_exists($x, 'importModuleSQL')){
                    echo "Importing SQL from module $m using Module::importModuleSQL\n";
                    $x->opts = $this->opts;
                    $x->rootDir = $this->rootDir;
                    $x->importModuleSQL($dburl);
                    continue;
                }
            };

            echo "Importing SQL from module $m\n";
            
            
            // if init has been called
            // look in pgsql.ini
            if (!empty($this->opts['init'])) {
                $this->{$dirmethod}($dburl, $this->rootDir. "/Pman/$m/{$dbtype}.init");
                
            }
            
            
            
            $fd = $this->rootDir. "/Pman/$m/DataObjects";
            
            $this->{$dirmethod}($dburl, $fd);
            
            
            // new -- sql directory..
            // new style will not support migrate ... they have to go into mysql-migrate.... directories..
            // new style will not support pg.sql etc.. naming - that's what the direcotries are for..
            $dbdir = $dbtype == 'mysqli' ? 'mysql' : $dbtype;
            
            $this->{$dirmethod}($dburl, $this->rootDir. "/Pman/$m/sql");
            $this->{$dirmethod}($dburl, $this->rootDir. "/Pman/$m/{$dbdir}");
            
           
            
            if (!empty($this->opts['init']) && file_exists($this->rootDir. "/Pman/$m/{$dbtype}.initdata")) {
                if (class_exists('PDO_DataObjects_Introspection')) {
                    PDO_DataObject_Introspection::$cache = array();
                }
                HTML_FlexyFramework::get()->generateDataobjectsCache(true);
                
                $this->{$dirmethod}($dburl, $this->rootDir. "/Pman/$m/{$dbtype}.initdata");
                $this->{'fixSequences'. $dbtype}();
                
            }
              
            
        }
        
        
    }
    
    
    
     
    /** -------------- code to handle importing a whole directory of files into the database  -------  **/
    
    
    function importpgsqldir($url, $dir, $disable_triggers = false)
    {
        $ff = HTML_FlexyFramework::get();
        
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
        
        if (is_file($dir)) {
            $files = array($dir);

        } else {
        
        
            $files = glob($dir.'/*.sql');
            uksort($files, 'strcasecmp');
        }
        //$lsort = create_function('$a,$b','return strlen($a) > strlen($b) ? 1 : -1;');
        //usort($files, $lsort);
        
        
        foreach($files as $bfn) {


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

            echo "$bfn:   $cmd ". ($ff->cli ? "\n" : "<BR>\n");

            passthru($cmd);

            if ($fn) {
                unlink($fn);
            }
        }

              
             
        
    }
    
    
    /**
     * mysql - does not support conversions.
     * 
     *
     */
    function importmysqlidir($dburl, $dir) {
        return $this->importmysqldir($dburl, $dir);
    }
    
    function importmysqldir($dburl, $dir)
    {
        
        $this->fixMysqlInnodb(); /// run once 
        
        echo "Import MYSQL :: $dir\n";
        
        
        require_once 'System.php';
        $cat = System::which('cat');
        $mysql = System::which('mysql');
        
       
           
        $mysql_cmd = $mysql .
            ' -h ' . $dburl['host'] .
            (empty($dburl['port']) ? '' : " -P{$dburl['port']} ") .
            ' -u' . escapeshellarg($dburl['user']) .
            (!empty($dburl['pass']) ? ' -p' . escapeshellarg($dburl['pass'])  :  '') .
            ' ' . basename($dburl['path']);
        //echo $mysql_cmd . "\n" ;
        
        $files = glob($dir.'/*.sql');
        uksort($files, 'strcasecmp');
        
       
        foreach($files as $fn) {
                
                 
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
                if (!empty($this->opts['only-module-sql-table']) && basename($fn) != $this->opts['only-module-sql-table'].'.sql') {
                    continue;
                }
                        
                
                $cmd = "$mysql_cmd -f < " . escapeshellarg($fn) ." 2>&1" ;
                
                echo basename($dir).'/'. basename($fn) .    '::' .  $cmd. ($this->cli ? "\n" : "<BR>\n");
                
                
                $fp = popen($cmd, "r"); 
                while(!feof($fp)) 
                { 
                    // send the current file part to the browser 
                    $line = trim(fgets($fp, 1024));
                    if (empty($line)) {
                        continue;
                    }
                    $matches = array();
                    
                    if (preg_match("/Using a password on the command line interface can be insecure/", $line)) {
                        continue;
                    }
                    
                    if (!preg_match('/^ERROR\s+([0-9]+)/', $line, $matches)) {
                        echo " ---- {$line}\n"; flush();
                        continue;
                    }
                    $continue =0;
                    switch($matches[1]) {
                        case 1017: // cause by renaming table -- old one does not exist..
                        case 1050: // create tables triggers this..
                        case 1060: //    Duplicate column name
                        case 1061: // Duplicate key name - triggered by add index.. but could hide error. - unlikely though.
                        case 1091: // drop index -- name does not exist.. might hide errors..
                       // case 1118: // this is a row sze to large - Not event sure
   
                        case 1146: // drop a index on an unknown table.. - happens rarely...
                        case 1054: // Unknown column -- triggered by CHANGE COLUMN - but may hide other errrors..
                            $continue = 1;
                            break;
                        
                    }
                    if ($continue) {
                        echo " ---- {$line}\n"; flush();
                        continue;
                    }
                    // real errors...
                    // 1051: // Unknown table -- normally drop = add iff exists..
                    echo "File: $fn\n$line\n";
                    exit;
                    
                    
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
            if ($tbl && preg_match('#engine=\S+#i',  $l, $m)) {
                $l = preg_replace('#engine=\S+#i', '', $l);
                
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
    
    
    function checkOpts($opts)
    {
        
        
        foreach($opts as $o=>$v) {
            if (!preg_match('/^json-/', $o) || empty($v)) {
                continue;
            }
            if (!file_exists($v)) {
                die("File does not exist : OPTION --{$o} = {$v} \n");
            }
        }
        
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
            if(!method_exists($x, 'checkOpts')){
                continue;
            };
            $x->checkOpts($opts);
        }
                
    }
    static function jsonImportFromArray($opts)
    {
        foreach($opts as $o=>$v) {
            if (!preg_match('/^json-/', $o) || empty($v)) {
                continue;
            }
            $type = str_replace('_', '-', substr($o,5));
            
            $data= json_decode(file_get_contents($v),true);
            $pg = HTML_FlexyFramework::get()->page;
            DB_DataObject::factory($type)->importFromArray($pg ,$data,$opts);
            
        }
        
        
        
    }
    
    
    
    function runUpdateModulesData()
    {
        if (class_exists('PDO_DataObjects_Introspection')) {
            PDO_DataObject_Introspection::$cache = array();
        }
        HTML_FlexyFramework::get()->generateDataobjectsCache(true);
        
        if(!in_array('Core', $this->disabled)){
            echo "Running jsonImportFromArray\n";
            Pman_Core_UpdateDatabase::jsonImportFromArray($this->opts);
            

            echo "Running updateData on modules\n";
            // runs core...
            echo "Core\n";
            $this->updateData(); 
        }
        
        $modules = array_reverse($this->modulesList());
        
        // move 'project' one to the end...
        
        foreach ($modules as $module){
            if(in_array($module, $this->disabled)){
                continue;
            }
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
            $x->rootDir =  $this->rootDir;
            echo "$module\n";
            $x->updateData();
        }
        
    }
    
    
    function updateDataEnums()
    {
        
        HTML_FlexyFramework::get()->generateDataobjectsCache(true);
        echo "Updateting Enums\n";
        $enum = DB_DataObject::Factory('core_enum');
        //DB_DAtaObject::debugLevel(1);
        $enum->initEnums($this->enums);
             
    }
    function updateDataGroups()
    {
         
        $groups = DB_DataObject::factory('core_group');
        $groups->initGroups();
        
        $groups->initDatabase($this,array(
            array(
                'name' => 'bcc-email', // group who are bcc'ed on all requests.
                'type' => 0, // system
                'is_system' => 1,
                'display_name' => 'Standard BCC Group'
            ),
            array(
                'name' => 'system-email-from',
                'type' => 0, // system
                'is_system' => 1,
                'display_name' => 'Standard System Email From Group'
            ),
            array(
                'name' => 'core-person-signup-bcc',
                'type' => 0, // system
                'is_system' => 1,
                'display_name' => 'Standard Person Signup BCC Group'
            ),
            array(
                'name' => 'Empty Group', // use for no bcc emails.
                'type' => 0,
                'is_system' => 1,
                'display_name' => 'Standard Empty Group'
            )

        ));
        
    }
    
    function updateDataCompanies()
    {
         
        // fix comptypes enums..
        $c = DB_DataObject::Factory('core_company');
        $c->selectAdd();
        $c->selectAdd('distinct(comptype) as comptype');
        $c->whereAdd("
                comptype != '' 
            AND 
                comptype != 'undefined' 
            AND 
                comptype != 'undefine'
        ");
        
        $ctb = array();
        foreach($c->fetchAll('comptype') as $cts) {
            
            $ctb[]= array( 'etype'=>'COMPTYPE', 'name' => $cts, 'display_name' => ucfirst(strtolower($cts)));
        
        }
        $c = DB_DataObject::Factory('core_enum');
         
        $c->initEnums($ctb);
        //DB_DataObject::debugLevel(1);
        // fix comptypeid
        $c = DB_DataObject::Factory('core_company');
        $c->query("
            UPDATE {$c->tableName()} 
                SET
                    comptype_id = (SELECT id FROM core_enum where etype='comptype' and name={$c->tableName()}.comptype LIMIT 1)
                WHERE
                    comptype_id = 0
                    AND
                    LENGTH(comptype) > 0
                  
                  
                  ");
         
        
        
    }
    
    function updateDataEmails()
    {
        if (!empty($this->opts['skip-email-import'])) {
            return;
        }
        foreach ($this->emailTemplates as $k => $mail) {
            
            $this->initEmails(
                !empty($mail['template_dir']) ? "{$this->rootDir}{$mail['template_dir']}" : '',
                array($k => $mail),
                false
            );
        }
    }
    
    function initEmails($templateDir, $emails, $mapping = false)
    {
        HTML_FlexyFramework::get()->generateDataobjectsCache(true);

        $pg = HTML_FlexyFramework::get()->page;
        foreach($emails as $name=>$data) {
            $cm = DB_DataObject::factory('core_email');
            $update = $cm->get('name', $name);
            $old = clone($cm);
            
            if (empty($cm->bcc_group_id)) {
                if (empty($data['bcc_group'])) {
                    $this->jerr("missing bcc_group for template $name");
                }
                
                $g = DB_DataObject::Factory('core_group')->lookup('name',$data['bcc_group']);
                
                if (empty($g->id)) { // Admin group as bcc will not have any member at initialization.
                    $this->jerr("bcc_group {$data['bcc_group']} does not exist when importing template $name");
                }
                
                if (!$g->members('email') && $g->name != 'Empty Group' &&  $g->name != 'Administrators') {
                    $this->jerr("bcc_group {$data['bcc_group']} does not have any members");
                }
                
                $cm->bcc_group_id = $g->id;
            }
            // initEmails will always have the latest location of the test class - in theory the user should not be changign the value of this...
            //if (empty($cm->test_class)) {
            if (empty($data['test_class'])) {
                $this->jerr("missing test_class for template $name");
            }
            
            $cm->test_class = $data['test_class'];
            //}
            if(isset($cm->to_group_id)) {
                print_r('isset');
            }
            
            if (
                !empty($data['to_group']) &&
                (!isset($cm->to_group_id) || !empty($cm->to_group_id)) 
            ) {
                $gp = DB_DataObject::Factory('core_group')->lookup('name',$data['to_group']);
                
                if (empty($gp->id)) {
                    $this->jerr("to_group {$data['to_group']} does not exist when importing template $name");
                }
                
                $cm->to_group_id = $gp->id;
            }
            
            if(
                isset($data['active']) && !isset($cm->active)
            ) {
                $cm->active = $data['active'];
            }
            
            /*
             * Set description to email.
             * However we do not update if it is been set.
             */
            if(empty($cm->description) && !empty($data['description'])){
                $cm->description = $cm->escape($data['description']);
            }
            
            require_once $cm->test_class . '.php';
            
            $clsname = str_replace('/','_', $cm->test_class);
            try {
                $method = new ReflectionMethod($clsname , 'test_'. $name) ;
                $got_it = $method->isStatic();
            } catch(Exception $e) {
                $got_it = false;
                
            }
            if (!$got_it) {
                $this->jerr("template {$name} does not have a test method {$clsname}::test_{$name}");
            }
            if ($update) {
                $cm->update($old);
                echo "email: {$name} - checked\n";
                continue; /// we do not import the body content of templates that exist...
            } else {
                
                //$cm->insert();
            }
            
            
    //        $basedir = $this->bootLoader->rootDir . $mail_template_dir;
            
            $opts = array(
                'update' => 1,
            );
            if (!empty($templateDir)) {
                $opts['file'] = $templateDir. $name .'.html';
            }
            if (!empty($data['raw_content'])) {
                $opts['raw_content'] = $data['raw_content'];
                $opts['name'] = $name;
            }
            if (!empty($data['master'])) {
                $opts['master'] = $templateDir . $master .'.html';
            }
            require_once 'Pman/Core/Import/Core_email.php';
            $x = new Pman_Core_Import_Core_email();
            
            $x->updateOrCreateEmail('', $opts, $cm, $mapping);
            
            echo "email: {$name} - CREATED\n";
        }
    }
    
    
    function updateData()
    {
        // fill i18n data..
        if (class_exists('PDO_DataObjects_Introspection')) {
            PDO_DataObject_Introspection::$cache = array();
        }
        HTML_FlexyFramework::get()->generateDataobjectsCache(true);
        
        $this->updateDataEnums();
        $this->updateDataGroups();
        $this->updateDataCompanies();
        
        $this->updateDataEmails();
        
        $c = DB_DataObject::Factory('I18n');
        $c->buildDB();
    }
    
    function fixMysqlInnodb()
    {
        
        static $done_check = false;
        if ($done_check) {
            return;
        }
        
        
        if (!empty($this->opts['skip-mysql-checks'])) {
            return;
        }
        // innodb in single files is far more efficient that MYD or one big innodb file.
        // first check if database is using this format.
        $db = DB_DataObject::factory('core_enum');
        $db->query("show variables like 'innodb_file_per_table'");
        $db->fetch();
        if ($db->Value == 'OFF') {
            die("Error: set innodb_file_per_table = 1 in my.cnf (or run with --skip-mysql-checks\n\n");
        }
        
        $db = DB_DataObject::factory('core_enum');
        $db->query("select version() as version");
        $db->fetch();
        
        if (version_compare($db->version, '5.7', '>=' )) {
                
            $db = DB_DataObject::factory('core_enum');
            $db->query("show variables like 'sql_mode'");
            $db->fetch();
            
            $modes = explode(",", $db->Value);
            
            // these are 'new' problems with mysql.
            if(
                    in_array('NO_ZERO_IN_DATE', $modes) ||
                    in_array('NO_ZERO_DATE', $modes) ||
                    in_array('STRICT_TRANS_TABLES', $modes) || 
                    !in_array('ALLOW_INVALID_DATES', $modes)
            ){
                die("Error: set sql_mode include 'ALLOW_INVALID_DATES', remove 'NO_ZERO_IN_DATE' AND 'STRICT_TRANS_TABLES' AND 'NO_ZERO_DATE' in my.cnf\n\n".
                    "Recommended line: \n\nsql_mode = ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION,ALLOW_INVALID_DATES\n\n"
                );
            }
        }
        
        $done_check = true;;

 
        
        
        
        
        
    }
    
    
    /** ------------- schema fixing ... there is an issue with data imported having the wrong sequence names... --- */
    
    function fixSequencesMysql()
    {
        // not required...
    }
    
    function fixSequencesPgsql()
    {
     
     
        //DB_DataObject::debugLevel(1);
        $cs = DB_DataObject::factory('core_enum');
        $cs->query("
         SELECT
                    'ALTER SEQUENCE '||
                    CASE WHEN strpos(seq_name, '.') > 0 THEN
                        min(seq_name)
                    ELSE 
                        quote_ident(min(schema_name)) ||'.'|| quote_ident(min(seq_name))
                    END 
                    
                    ||' OWNED BY '|| quote_ident(min(schema_name)) || '.' ||
                    quote_ident(min(table_name)) ||'.'|| quote_ident(min(column_name)) ||';' as cmd
             FROM (
                      
                       SELECT 
                     n.nspname AS schema_name,
                     c.relname AS table_name,
                     a.attname AS column_name, 
                     regexp_replace(regexp_replace(d.adsrc, E'nextval\\\\(+[''\\\"]*', ''),E'[''\\\"]*::.*\$','') AS seq_name 
                 FROM pg_class c 
                 JOIN pg_attribute a ON (c.oid=a.attrelid) 
                 JOIN pg_attrdef d ON (a.attrelid=d.adrelid AND a.attnum=d.adnum) 
                 JOIN pg_namespace n ON (c.relnamespace=n.oid)
                 WHERE has_schema_privilege(n.oid,'USAGE')
                   AND n.nspname NOT LIKE 'pg!_%' escape '!'
                   AND has_table_privilege(c.oid,'SELECT')
                   AND (NOT a.attisdropped)
                   AND d.adsrc ~ '^nextval'
              
             ) seq
             WHERE
                 CASE WHEN strpos(seq_name, '.') > 0 THEN
                     substring(seq_name, 1,strpos(seq_name,'.')-1)
                ELSE
                    schema_name
                END = schema_name
             
             GROUP BY seq_name HAVING count(*)=1
             ");
        $cmds = array();
        while ($cs->fetch()) {
            $cmds[] = $cs->cmd;
        }
        foreach($cmds as $cmd) {
            $cs = DB_DataObject::factory('core_enum');
            echo "$cmd\n";
            $cs->query($cmd);
        }
        $cs = DB_DataObject::factory('core_enum');
         $cs->query("
               SELECT  'SELECT SETVAL(' ||
                         quote_literal(quote_ident(nspname) || '.' || quote_ident(S.relname)) ||
                        ', MAX(' || quote_ident(C.attname)|| ')::integer )  FROM ' || nspname || '.' || quote_ident(T.relname)|| ';' as cmd 
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
                ORDER BY S.relname   
        ");
         $cmds = array();
        while ($cs->fetch()) {
            $cmds[] = $cs->cmd;
        }
        foreach($cmds as $cmd) {
            $cs = DB_DataObject::factory('core_enum');
            echo "$cmd\n";
            $cs->query($cmd);
        }
       
    }
    
    var $extensions = array(
        'EngineCharset',
        'Links',
    );
    
    function runExtensions()
    {
        
        $ff = HTML_Flexyframework::get();
        
        $dburl = parse_url($ff->database);
        
        $dbtype = $dburl['scheme'];
        $dbtype  = ($dbtype == 'mysqli') ? 'mysql' : $dbtype;
        
        foreach($this->extensions as $ext) {
       
            $scls = ucfirst($dbtype). $ext;
            $cls = __CLASS__ . '_'. $scls;
            $fn = implode('/',explode('_', $cls)).'.php';
            
            if (!file_exists(__DIR__.'/UpdateDatabase/'. $scls .'.php')) {
                return;
            }
            echo "Running : {$fn}\n";
            require_once $fn;
            $c = new $cls();
            if (method_exists($c, 'run')) {
                $c->run();
            }
            
        }
        
    }
    
    
    function checkSystem($req = false, $pref = false)
    {
        // most of these are from File_Convert...
        
        // these are required - and have simple dependancies.
        require_once 'System.php';
        $req = $req !== false ? $req : array( 
            'convert',
            'grep',
            'pdfinfo',
            'pdftoppm',
            'rsvg-convert',  //librsvg2-bin
            'strings',
            'oathtool',
            'gifsicle', // used for gif conversions
        );
         
          
        // these are prefered - but may have complicated depenacies
        $pref = $pref !== false ? $pref :  array(
            'abiword',
            //'faad',
            'ffmpeg',
            'html2text', // not availabe in debian squeeze
            'pdftocairo',  //poppler-utils - not available in debian squeeze.

            //'lame',
            'ssconvert',
            'unoconv',
            'wkhtmltopdf',
            'xvfb-run',
        );
        $res = array();
        $fail = false;
        foreach($req as $r) {
            if (!System::which($r)) {
                $res[] = $r;
            }
            $fail = true;
        }
        if ($res) {
            die("Missing these programs - need installing\n" . implode("\n",$res). "\n");
        }
        foreach($pref as $r) {
            if (!System::which($r)) {
                $res[] = $r;
            }
            $fail = true;
        }
        if ($res) {
            echo "WARNING: Missing these programs - they may need installing\n". implode("\n",$res);
            sleep(5);
        }
        
        
    }
    
    function clearApacheDataobjectsCache()
    {
        
        // this needs to clear it's own cache along with remote one..
  
        $url = "http://localhost{$this->local_base_url}/Core/RefreshDatabaseCache";
        
        echo "Clearing Database Cache : http://localhost{$this->local_base_url}/Core/RefreshDatabaseCache\n";
        
        $response = $this->curl($url);
        
        $json = json_decode($response, true);
        
        if(empty($json['data']) || $json['data'] != 'DONE'){
            echo "fetching $url\n";
            echo "GOT:" . $response. "\n";
            echo "Clear DataObjects Cache failed\n";
            exit;
        }
        
    }
    
    
    function clearApacheAssetCache()
    {
        echo "Clearing Asset Cache : http://localhost{$this->local_base_url}/Core/Asset\n";
        $response = $this->curl(
            "http://localhost{$this->local_base_url}/Core/Asset",
            array( '_clear_cache' => 1 ,'returnHTML' => 'NO' ),
            'POST'
        );
        $json = json_decode($response, true);
        
        if(empty($json['success']) || !$json['success']) {
            echo $response. "\n";
            echo "CURL Clear Asset cache failed\n";
            exit;
        }
        
    }
    
    
    function curl($url, $request = array(), $method = 'GET') 
    {
        if($method == 'GET'){
            $request = http_build_query($request);
            $url = $url . "?" . $request;  
        }
        
        $ch = curl_init($url);
        
        if ($method == 'POST') {
            
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array("Content-Type: application/x-www-form-urlencoded", "Content-Length: " . strlen($request)));
            
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        
        curl_close($ch);
        
        return $response;
    }
    
    static function verifyExtensions($extensions)
    {
        $error = array();
        
        foreach ($extensions as $e){
            
            if(empty($e) || extension_loaded($e)) {
                continue;
            }
            
            $error[] = "Error: Please install php extension: {$e}";
        }
        
        if(empty($error)){
           return true; 
        }
        $ff = HTML_FLexyFramework::get();
        
        $ff->page->jerr(implode('\n', $error));
    }
    
}

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
        'procedures-only' => array(
            'desc' => 'Only import procedures (not supported by most modules yet) - ignores sql directory',
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
            
            $ret = array_merge($ret, $cls->getStaticPropertyValue('cli_opts'));
            
            
        }
        
        return $ret;
    }
    
    var $opts = false;
    var $disabled = array();
    
    
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
     
    function get($args, $opts)
    {
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'onPearError'));
   
        $ff = HTML_FlexyFramework::get();
        
        $this->disabled = explode(',', $ff->disable);
        
        //$this->fixSequencesPgsql();exit;
        $this->opts = $opts;
        
        // ask all the modules to verify the opts
        
        $this->checkOpts($opts);
        
        if (empty($opts['data-only'])) {
            $this->importSQL();
        }
        if (!empty($opts['only-module-sql'])) {
            return;
        }
        
        $this->runUpdateModulesData();
        
        
        if (!empty($opts['add-company']) && !in_array('Core', $this->disabled)) {
            // make sure we have a good cache...?
           
            DB_DataObject::factory('companies')->initCompanies($this, $opts);
        }
        
        $this->runExtensions();
         
         
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
        
        // loop through all the modules, and see if they have a importSQL method?
        
        
        $ff = HTML_Flexyframework::get();
        
        $dburl = parse_url($ff->DB_DataObject['database']);
        
        //$this->{'import' . $url['scheme']}($url);
        
        $dbtype = $dburl['scheme'];
        $dirmethod = 'import' . $dburl['scheme'] . 'dir';
        
        
       
        
        $ar = $this->modulesList();
        
        
        foreach($ar as $m) {
            
            if(in_array($m, $this->disabled)){
                echo "module $m is disabled \n";
                continue;
            }
            
            echo "Importing SQL from module $m\n";
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
            
            $this->{$dirmethod}($dburl, $this->rootDir. "/Pman/$m/sql");
            $this->{$dirmethod}($dburl, $this->rootDir. "/Pman/$m/{$dbtype}");
            
            
            
            if (!empty($this->opts['init']) && file_exists($this->rootDir. "/Pman/$m/{$dbtype}.initdata")) {
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
    
    
    function importmysqldir($dburl, $dir)
    {
        
        $this->fixMysqlInnodb(); /// run once 
        
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
                
                $cmd = "$mysql_cmd -f < " . escapeshellarg($fn) ;
                
                echo basename($dir).'/'. basename($fn) .    '::' .  $cmd. ($this->cli ? "\n" : "<BR>\n");
                
                passthru($cmd);
            
                
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
            echo "$module\n";
            $x->updateData();
        }
                
    }
    
    
    function updateDataEnums()
    {
        
        $enum = DB_DataObject::Factory('core_enum');
        //DB_DAtaObject::debugLevel(1);
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
            array(
                'name' => 'system-email-from',
                'type' => 0, // system
            ),
            array(
                'name' => 'core-person-signup-bcc',
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
                    comptype_id = (SELECT id FROM core_enum where etype='comptype' and name=Companies.comptype LIMIT 1)
                WHERE
                    comptype_id = 0
                    AND
                    LENGTH(comptype) > 0
                  
                  
                  ");
         
        
        
    }
    
    
    function initEmails($templateDir, $emails)
    {
      
        $pg = HTML_FlexyFramework::get()->page;
        foreach($emails as $name=>$data) {
            $cm = DB_DataObject::factory('core_email');
            $update = $cm->get('name', $name);
            $old = clone($cm);
            
            if (empty($cm->bcc_group)) {
                if (empty($data['bcc_group'])) {
                    $this->jerr("missing bcc_group for template $name");
                }
                $g = DB_DataObject::Factory('Groups')->lookup('name',$data['bcc_group']);
                
                if (!$g) {
                    $this->jerr("bcc_group {$data['bcc_group']} does not exist when importing template $name");
                }
                if (!$g->members('email')) {
                      $this->jerr("bcc_group {$data['bcc_group']} does not have any members");
                }
                
                
                $cm->bcc_group = $g->id;
            }
            if (empty($cm->test_class)) {
                if (empty($data['test_class'])) {
                    $this->jerr("missing test_class for template $name");
                }
                $cm->test_class = $cm;
            }
            require_once $cm->test_class . '.php';
            $clsname = str_replace('/','_', $cm->test_class);
            $method = new ReflectionMethod($clsname , 'test_'. $name) ;
            if (!$method->isStatic()) {
                $this->jerr("template {$name} does not have a test method {$clsname}::test_{$name}");
            }
            if ($up) {
                $cm->update($old);
                continue; /// we do not import the body content of templates that exist...
            } else {
                $cm->insert();
            }
            
            
    //        $basedir = $this->bootLoader->rootDir . $mail_template_dir;
            
            $opts = array(
                'update' => 1,
                'file' => $mail_template_dir. $name .'.html'
            );
            
            if (!empty($data['master'])) {
                $opts['master'] = $mail_template_dir . $master .'.html';
            }
            require_once 'Pman/Core/Import/Core_email.php';
            $x = new Pman_Core_Import_Core_email();
            $x->get('', $opts);
        }
    }
    
    
    function updateData()
    {
        // fill i18n data..
        HTML_FlexyFramework::get()->generateDataobjectsCache(true);
        $this->updateDataEnums();
        $this->updateDataGroups();
        $this->updateDataCompanies();
        
        $c = DB_DataObject::Factory('I18n');
        $c->buildDB();
         
       
        
        
    }
    
    function fixMysqlInnodb()
    {
        
        static $done_check = false;
        if ($done_check) {
            return;
        }
        // innodb in single files is far more efficient that MYD or one big innodb file.
        // first check if database is using this format.
        $db = DB_DataObject::factory('core_enum');
        $db->query("show variables like 'innodb_file_per_table'");
        $db->fetch();
        if ($db->Value == 'OFF') {
            die("Error: set innodb_file_per_table = 1 in my.cnf\n\n");
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
        
        $dburl = parse_url($ff->DB_DataObject['database']);
        
        $dbtype = $dburl['scheme'];
       
        foreach($this->extensions as $ext) {
       
            $scls = ucfirst($dbtype). $ext;
            $cls = __CLASS__ . '_'. $scls;
            $fn = implode('/',explode('_', $cls)).'.php';
            if (!file_exists(__DIR__.'/UpdateDatabase/'. $scls .'.php')) {
                return;
            }
            require_once $fn;
            $c = new $cls();
            
        }
        
    }
}
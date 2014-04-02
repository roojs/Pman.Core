<?php
 
 
/**
 * 
 * Generate DataObjects...
 * 
 * This does not generate ini files any more - as that is done on the fly by the framework.
 * 
 * note - we write to a temporary directory first...
 * 
 * 
 */
 
require_once 'DB/DataObject/Generator.php';


/** basic thing now works... 
* 
* it needs a bit more intelligence to work out what to do...
* 
* 
* Basically we need to build up all the formats for each db column
* then 
*   - overlay any mapping stuff.
*   
*   - overlay user defined settings
*   = write it out to file..
*   
*  Strucutres:
$this->def['order'][$table][] = $t->name;
$this->def['readers'][$table][$t->name] = $reader;
$this->def['colmodels'][$table][$t->name] = $colmodel;
$this->def['forms'][$table][$t->name] = $form;
* 
*   Readers
*       readersDef[table.col]
* 
* 
* 
* **/



class Pman_Core_Generator extends DB_DataObject_Generator
{
    
     
    function getAuth()
    {
         
        die("do not use this directly.. - use Core/RunGenerator");  
        
    }
   
    // inherrited..
    // $tablekeys
    // $tables
    // $_definitions
    /**
     * def[order]  
     *      [tablename] => array( list of columns ones with '-' indicate lookup
     *    [readers]
     *       [tablename][colname] -> reader foramt
     *    [forms]
     *        [tablename][colname] => xtype / name etc...
     *    [readerArgs]
     *        [tablename] => data for reader args (eg. id / total prop etc.)
     *  readers =>
     *         [tablename] => array of cols with types
     *  forms =>
     *        [tablename] -> array of cols
     * 
     */ 
    var $def;
    
      
    var $page = false; // page container when run from cli.
    
    // dont do usual stuff!!!
    var $rootDir = '';
    var $tablekeys = array();
    
    var $overwrite = array(); // default dont overwrite any of the files..
    //  array('master', 'corejs', 'corephp', 'index', 'Roo')
    // and ('js-?????' where ??? is the table name) <- for all the generated js classes.
    // we always overwrite the definition!!!
    // set to array('all') to overwrite everything!!!
    
    function start($cli=false, $mods='', $overwrite=array())
    {
        
        $ff = HTML_Flexyframework::get();
        $this->scanModules();
        //echo '<PRE>'; print_r($this->modtables); exit;
        
        $options = &PEAR::getStaticProperty('DB_DataObject','options');
        
        
        $proj = 'pman'; //ucfirst(basename($options['database']));
        // we are going to generate all of the code into a temporay foldler..
        $user = posix_getpwuid(posix_getuid());
        
        $options['rootDir'] = ini_get('session.save_path').'/temp_'. $proj.'_'. $user['name'];
        $options['cli'] = $cli;
        $options['mods'] = empty($mods) ? array() : explode('/',$mods);
       
        if (!file_exists($options['rootDir'])) {
            mkdir($options['rootDir'], 0775, true);
        }
        
        $this->rootDir = $options['rootDir'];
        $options['schema_location'] =  $this->rootDir .'/'.$proj.'/DataObjects';
        $options['class_location'] = $this->rootDir .'/'.$proj.'/DataObjects';
        $options['require_prefix'] =    $proj . '/DataObjects/';
        $options['class_prefix'] =    $proj . '_DataObjects_';
       //  print_r($this);exit;
       
       
        $standard_database = $options['database'];
       
       
         
       
       
       
       
       
       
        parent::start();
        
        $this->scanModules();
        require_once 'System.php';
        $diff = System::which('diff');
        // now for each of the directories copy/show diffs..
        echo $cli ? '' : '<PRE>';
        $flist =   $overwrite;
        foreach($this->modtables as $m=>$ar) {
            if ($options['database'] !=  $standard_database) {
                $options['database'] =  $standard_database ;
                
                parent::start();
            }
            
            $options['database'] =  $standard_database ;
            if (isset($options['database_'. $m])) {
                $options['database'] =  $options['database_'. $m];
                //var_dump($url);exit;
                
                // start again?
                parent::start();
            }
            
            
            if (!empty($options['mods'] ) && !in_array($m,  $options['mods'] )) {
                continue;
            }
            // this happens when we have no database tables from a module,
            // but module code has been defined.
            if (!file_exists($options['rootDir'].'/'.$m)) {
                continue;
            }
            foreach(scandir($options['rootDir'].'/'.$m) as $f) {
                
                echo "SCAN {$options['rootDir']} $f\n";
                
                if (!strlen($f) || $f[0] == '.') {
                    continue;
                }
                // does it exist!!!
                $src = $options['rootDir']."/$m/$f";
                $tg = $ff->page->rootDir."/Pman/$m/DataObjects/$f";
                if (preg_match('/\.js$/', $f)) {
                    $tg = $ff->page->rootDir."/Pman/$m/$f";
                }
                
                if (!file_exists($tg) || !filesize($tg) ) {
                  
                    if ($cli && file_exists($tg) || in_array($f, $flist) || in_array('_all_', $flist )) {
                        echo "COPY $src $tg" . ($cli ? "\n" : "<BR>");
                        copy($src, $tg);
                        continue;
                    }
                    echo "!!!!MISSING!!! $tg" . ($cli ? "\n" : "<BR>");
                    
                    continue;
                }
                // always copy readers and ini file.=  nope - not on live..
                if ($cli && in_array($f, $flist) || in_array('_all_', $flist )) {
                    
                   //|| $f=='pman.ini' || preg_match('/\.js$/', $f))) {
                    echo "COPY $src $tg". ($cli ? "\n" : "<BR>");
                    copy($src, $tg);
                    continue;
                }
                
                // diff the two..
                $cmd = "$diff -u -w ". escapeshellarg($tg) . ' ' . escapeshellarg($src);
                 
                $out = array(); $ret = 0;
                exec($cmd, $out, $ret);
                if ($ret ==0) { // files match..
                    continue;
                }
                // var_dump($ret);
                echo "\n" .implode( "\n" , $out) . "\n";
               
                
            }
            
            
        }
        
        
        
        
    }
     
    /**
     * Scan the folders for DataObjects
     * - Use the list of php files in DataObjects folders 
     *   to determine which module owns which database table.
     * 
     */
    
    
    function scanModules()
    {
        
        $options = &PEAR::getStaticProperty('DB_DataObject','options');
        if (isset($options['modtables'])) {
            $this->modtables = $options['modtables'];
            $this->modmap = $options['modmap'];
            $this->modsql = $options['modsql'];
            return;
        }
        
        $ff = HTML_Flexyframework::get();
        
        $top = $ff->page->rootDir .'/Pman';
        $this->modtables = array();
        $this->modmap = array();
        $this->modmapsql = array();
        
        foreach(scandir($top) as $m) {
            
            if (!strlen($m) || 
                    $m[0] == '.' || 
                    !is_dir($top .'/'.$m) || 
                    !file_exists($top .'/'.$m.'/DataObjects')
                ) {
                continue;
            }
            $this->modtables[$m] = array();
            $this->modsql[$m] = array();
            foreach(scandir($top .'/'.$m.'/DataObjects') as $f) {
                if (!strlen($f) ||   $m[0] == '.') {
                    continue;
                }
                if (preg_match('/\.sql$/', $f))  {
                    $this->modsql[$m][] = $f;
                }
                                
                if (preg_match('/\.php$/', $f))  {
                    $tn = strtolower(preg_replace('/\.php$/', '', $f));
                    $this->modtables[$m][] = $tn;
                    $this->modmap[$tn] = $m;
                    continue;
                }
            }
        }
        $options['modtables'] = $this->modtables;
        $options['modmap'] = $this->modmap;
        $options['modsql'] = $this->modsql;
       // print_r($options);
        
    }
    /**
     * 
     * this is run first, so picks up any missing dataobject files..
     */
    
    function generateDefinitions()
    {
        if (!$this->tables) {
            $this->debug("-- NO TABLES -- \n");
            return;
        }
        if (!isset($this->modmap)) {
            $this->scanModules();
        }
         $options = &PEAR::getStaticProperty('DB_DataObject','options');
        $builder_options = PEAR::getStaticProperty('Pman_Builder','options');
        $ignore = empty($builder_options['skip_tables']) ? array() : $builder_options['skip_tables'];
        
         $mods = $options['mods'];
        $inis = array();
        $this->_newConfig = '';
        foreach($this->tables as $this->table) {
            
            $tn  = strtolower($this->table);
            //print_r($this->modmap);//[$tn]);//
            
            
            
            if (!isset($this->modmap[$tn])) {
                
                if (in_array($this->table, $ignore)) {
                    continue;
                }
                if (empty($mods)) {
                
                
                   die("No existing DataObject file found for table {$this->table} 
            
- either add it to Pman_Builder[skip_tables] or\n
- run generator and specify that module..
- create an empty file in the related Module/DataObjects directory
eg. 
touch Pman/????/DataObjects/".ucfirst($this->table).".php
   
   ");
                }
                // use mods to determine where it should output to..
                //var_dump($mods);exit;
                $this->modmap[$tn] = $mods[0];
                
                
            }
            $mod = $this->modmap[$tn];
            $inis[$mod] = isset($inis[$mod]) ? $inis[$mod] : '';
            
            
            $this->_newConfig = '';
            $this->_generateDefinitionsTable();
            
            
            $inis[$mod] .= $this->_newConfig;
        }
        return; // we do not generate in ifiles any more..
         
    }
    
    function generateClasses() 
    {
      // print_R($this->modmap);
       // die("generateClasses");
        $options = &PEAR::getStaticProperty('DB_DataObject','options');
        
        $ff = HTML_Flexyframework::get();
        
        $rd = $options['rootDir'];
        $mods = $options['mods'];
        $this->_extends = 'DB_DataObject';
        $this->_extendsFile = 'DB/DataObject.php';
        $cli = $options['cli'];

        foreach($this->tables as $this->table) {
            
            $this->table        = trim($this->table);
            $tn  = strtolower($this->table);
            $mod = $this->modmap[$tn];
            
             if (!empty($mods) && !in_array($mod, $mods)) {
                continue;
            }
            
            $clean_table = preg_replace('/[^A-Z0-9]+/i','_',ucfirst(trim($this->table)));
            
            $this->classname    = 'Pman_'.$mod . '_DataObjects_'. $clean_table; // replace odd chars?
           
           
            $outfilename    = $rd.'/'.$mod.'/'. $clean_table .'.php';
            $orig           = $ff->page->rootDir .'/Pman/'.$mod.'/DataObjects/'.  $clean_table.'.php';
            
           
                // file_get_contents???
            
            $oldcontents = file_exists($orig) ? file_get_contents($orig) : '';
            
             
            echo "GENERATE: " .   $this->classname  . ($cli ? "\n" : "<BR>");
            
            $out = $this->_generateClassTable($oldcontents);
            
            // get rid of static GET!!!
            $out = preg_replace('/(\n|\r\n)\s*function staticGet[^\n]+(\n|\r\n)/s', '', $out);
            $out = preg_replace('#/\* Static get \*/#s', '', $out);
              
            if (!file_exists(dirname($outfilename))) {
                mkdir(dirname($outfilename), 0755, true);
            }
           // $this->debug( "writing $this->classname\n");
            //$tmpname = tempnam(session_save_path(),'DataObject_');
            file_put_contents($outfilename, $out);
            
        }
    }
    
    
        
   // function generateDefinitions() { }
    ////function generateForeignKeys() { }
   // function generateClasses() { }
   
      
     
     
   
    function parseConfig()
    {
         $options = &PEAR::getStaticProperty('DB_DataObject','options');
        
        if (isset($options['modtables'])) {
            $this->modtables = $options['modtables'];
            $this->modmap = $options['modmap'];
            $this->modsql = $options['modsql'];
        }
        
        $ff = HTML_Flexyframework::get();
        $dirs = array($ff->page->rootDir.'/Pman/DataObjects'); // not used anymore!
        foreach($this->modtables as $m=>$ts) {
            $dirs[] = $ff->page->rootDir.'/Pman/'.$m.'/DataObjects';
        }
        
         //echo '<PRE>';print_R($ini);//exit;
        
        
         
    }
     
        //var_dump($table);
        //print_r( $this->def['readers'][$table]);
       // print_r( $this->def['colmodels'][$table]);
        //print_r($this->def['readers'][$table]); exit;
        
      
       
    function writeFileEx($n, $f, $str) 
    {
        if (file_exists($f)) {
            // all - will not overwrite stuff.. (only being specific willl)
            if (!in_array($n, $this->overwrite)) {
                $this->writeFile($f.'.generated',$str);
                return;
            }
        }
        $this->writeFile($f,$str);
        
        
    }
    function writeFile($f, $str)
    {
        require_once 'System.php';
        System::mkdir(array('-p', dirname($f)));
        // overwrite???
        echo "write: $f\n";
        file_put_contents($f, $str);
    } 
   
}


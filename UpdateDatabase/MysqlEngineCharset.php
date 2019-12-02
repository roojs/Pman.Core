<?php
/**
  fixes character set and engine=InnoDB.. in Mysql
  more efficent 
 */

class Pman_Core_UpdateDatabase_MysqlEngineCharset {
    
    var $dburl;
    var $schema = array();
    var $links = array();
    var $views = array();
    
    function __construct()
    {
        // this might get run before we have imported the database
        // and hence not have any db.
        $this->loadIniFiles(); //?? shared???
        
        try {
            $dbo = DB_DataObject::factory('core_enum');
        } catch(PDO_DataObject_Exception_InvalidConfig $e) {
            echo "SKipping MysqlEngineCharse - no database yet\n";
            return;
        }
        
        
        if (is_a($dbo, 'PDO_DataObject')) {
            
            $this->views = $dbo->generator()->introspection()->getListOf('views');
        } else {
            $db = DB_DataObject::factory('core_enum')->getDatabaseConnection();
            $this->views = $db->getListOf( 'views');  // needs updated pear... 
        }
        
        // update the engine first - get's around 1000 character limit on indexes..cd
        // however - Innodb does not support fulltext indexes, so this may fail...
        $this->updateEngine(); 
        
        $this->updateCharacterSet();
        
        
    }
    
    function loadIniFiles()
    {
        // will create the combined ini cache file for the running user.
        
        $ff = HTML_FlexyFramework::get();
        $ff->generateDataobjectsCache(true);
        $this->dburl = parse_url($ff->database);
        
         
        $dbini = 'ini_'. basename($this->dburl['path']);
        
        
        $iniCache = isset( $ff->PDO_DataObject) ?  $ff->PDO_DataObject['schema_location'] : $ff->DB_DataObject[$dbini];
        if (!file_exists($iniCache)) {
            return;
        }
        
        $this->schema = parse_ini_file($iniCache, true);
        $this->links = parse_ini_file(preg_replace('/\.ini$/', '.links.ini', $iniCache), true);
        

        
    }
   
    function updateCharacterSet()
    {
        $views = $this->views;
        
        
        foreach (array_keys($this->schema) as $tbl){
            
            if(strpos($tbl, '__keys') !== false ){
                continue;
            }
            
            if(in_array($tbl , $views)) {
                continue;
            }
            
            $ce = DB_DataObject::factory('core_enum');
            
            $ce->query("
                SELECT
                        CCSA.character_set_name csname,
                        CCSA.collation_name collatename
                FROM
                        information_schema.`TABLES` T,
                        information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` CCSA
                WHERE
                        CCSA.collation_name = T.table_collation
                    AND
                        T.table_schema = DATABASE() -- COLLATE utf8_general_ci
                    AND
                        T.table_name = '{$tbl}' -- COLLATE utf8_general_ci
            ");
                     
            if (!$ce->fetch()) {
                continue;
            }
            
            if($ce->csname == 'utf8' && $ce->collatename == 'utf8_general_ci'){
                echo "utf8: SKIP $tbl\n";
                continue;
            }
            // this used to be utf8_unicode_ci
            //as the default collation for stored procedure parameters is utf8_general_ci and you can't mix collations.
            
            $ce = DB_DataObject::factory('core_enum');
            // not sure why, but convert to does not actually change the 'charset=' bit..
            $ce->query("ALTER TABLE $tbl CHARSET=utf8");
            $ce->query("ALTER TABLE {$tbl} CONVERT TO CHARACTER SET  utf8 COLLATE utf8_general_ci");
            echo "utf8: FIXED {$tbl}\n";
            
        }
    }
    function updateEngine()
    {
        $db = DB_DataObject::factory('core_enum');
        $db->query("show variables like 'innodb_file_per_table'");
        $db->fetch();
        
        $pg = HTML_FlexyFramework::get()->page;
        
        if (empty($pg->opts['skip-mysql-checks'])) {
            if ($db->Value == 'OFF') {
                die("Error: set innodb_file_per_table = 1 in my.cnf\n\n");
            }
            
        }
        
        
        // get a list of table views...
        // innodb in single files is far more efficient that MYD or one big innodb file.
        // first check if database is using this format.
        
        
        
        $views = $this->views;
        
        
        foreach (array_keys($this->schema) as $tbl){
            
            if(strpos($tbl, '__keys') !== false ){
                continue;
            }
            
            if(in_array($tbl , $views)) {
                continue;
            }
            
            $ce = DB_DataObject::factory('core_enum');
            
            $ce->query("
                select
                    engine
                from
                    information_schema.tables
                where
                    table_schema= DATABASE()
                    and
                    table_name = '{$tbl}'
            ");

            if (!$ce->fetch()) {
                continue;
            }
            //AWS is returning captials?
            $engine = isset($ce->engine) ? $ce->engine : $ce->ENGINE;
            
            if($engine == 'InnoDB' ){
                echo "InnoDB: SKIP $tbl\n";
                continue;
            }
            if($engine == 'ndbcluster' ){
                echo "ndbcluster: SKIP $tbl\n";
                continue;
            }
            
            // should really determine if we are running in cluster ready ...
            
            // this used to be utf8_unicode_ci
            //as the default collation for stored procedure parameters is utf8_general_ci and you can't mix collations.
            
            $ce = DB_DataObject::factory('core_enum');
            $ce->query("ALTER TABLE $tbl ENGINE=InnoDB");
            echo "InnoDB: FIXED {$tbl}\n";
            
        }
    }
    
}


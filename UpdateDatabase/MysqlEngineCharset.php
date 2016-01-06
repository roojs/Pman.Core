<?php
/**
  fixes character set and engine=InnoDB.. in Mysql
  more efficent 
 */

class Pman_Core_UpdateDatabase_MysqlEngineCharset {
    
    var $dburl;
    var $schema;
    var $links;
    
    function __construct()
    {
          
        $this->loadIniFiles(); //?? shared???
        $this->updateCharacterSet();
        $this->updateEngine();
        
        
    }
    
    function loadIniFiles()
    {
        // will create the combined ini cache file for the running user.
        
        $ff = HTML_FlexyFramework::get();
        $ff->generateDataobjectsCache(true);
        $this->dburl = parse_url($ff->database);
        
        $dbini = 'ini_'. basename($this->dburl['path']);
        
        
        $iniCache = $ff->DB_DataObject[$dbini];
        
        $this->schema = parse_ini_file($iniCache, true);
        $this->links = parse_ini_file(preg_replace('/\.ini$/', '.links.ini', $iniCache), true);
        

        
    }
   
    function updateCharacterSet()
    {
        foreach (array_keys($this->schema) as $tbl){
            
            if(strpos($tbl, '__keys') !== false ){
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
                        T.table_schema = '{$ce->database()}' -- COLLATE utf8_general_ci
                    AND
                        T.table_name = '{$tbl}' -- COLLATE utf8_general_ci
            ");
                     
            $ce->fetch();
            
            if($ce->csname == 'utf8' && $ce->collatename == 'utf8_general_ci'){
                echo "$tbl is Already utf8 \n";
                continue;
            }
            // this used to be utf8_unicode_ci
            //as the default collation for stored procedure parameters is utf8_general_ci and you can't mix collations.
            
            $ce = DB_DataObject::factory('core_enum');
            $ce->query("ALTER TABLE {$tbl} CONVERT TO CHARACTER SET  utf8 COLLATE utf8_general_ci");
            echo "FIXED utf8 on {$tbl}\n";
            
        }
    }
    function updateEngine()
    {
        foreach (array_keys($this->schema) as $tbl){
            
            if(strpos($tbl, '__keys') !== false ){
                continue;
            }
            
            $ce = DB_DataObject::factory('core_enum');
            
            $ce->query("select engine from information_schema.tables where table_schema='hydra' and table_name = 'core_enum'");

            $ce->fetch();
            
            if($ce->engine == 'InnoDB' ){
                echo "SKIP engine on $tbl - already InnoDB\n";
                continue;
            }
            // this used to be utf8_unicode_ci
            //as the default collation for stored procedure parameters is utf8_general_ci and you can't mix collations.
            
            $ce = DB_DataObject::factory('core_enum');
            $ce->query("ALTER TABLE $tbl ENGINE=InnoDB");
            echo "FIXED engine on {$tbl}\n";
            
        }
    }
    
}


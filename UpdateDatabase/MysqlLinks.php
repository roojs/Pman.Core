<?php
/**
 * our standard code relies on links.ini files for the relationships in mysql.
 *
 * as we use 'loose' relationships - eg. we allow '0' as a missing link mysql FORIEGN KEYS do not really work.
 *
 * There are a couple of ideas behind this code.
 *
 * a) put the relationships in the table comments FK(col=table:col,col=table:col)
 *  -- we can not put it in the column comments as there is no clean way to update column comments.
 *  -- This can be used by external programs to extract the Relationships.
 *
 * b) generate triggers? to protect against updates to the database..
 *
 *  -- stored procedures are named
 *     {tablename}_before_{insert|delete|update}
 *     
 *  
 *   initial code will auto generate triggers
 *   -- how to add User defined modifications to triggers?
 *   -- we can CALL a stored procedure..?
 *   -- {tablename}_trigger_{optional_string}_before_delete_{column_name}(NEW.column)
 *   -- {tablename}_trigger_{optional_string}_before_update_{column_name}(OLD.column, NEW.column}
 *   -- {tablename}_trigger_{optional_string}_before_insert_{column_name}(OLD.column}
 *
 *
 * ------- Importing with triggers disabled.
 *
 *  SET @DISABLE_TRIGGER=1; (or anything you like except NULL) 
 *  do imports
 * SET @DISABLE_TRIGGER=NULL;
 *
 * ------ Call a method disabling a particular set of triggers
 *  SET @DISABLE_TRIGGER_the_table_name=1; (or anything you like except NULL) 
 *  do action
 *  SET @DISABLE_TRIGGER_the_table_name=NULL;*
 */

class Pman_Core_UpdateDatabase_MysqlLinks {
    
    var $dburl;
    var $schema;
    var $links = array();
    
    function __construct($onlyConstruct = false)
    {
        if($onlyConstruct) {
            return;
        }
        die('tset');
          
        $this->loadIniFiles();
        $this->updateTableComments();
       
        $ff = HTML_FlexyFramework::get();
        if (empty($ff->Pman['enable_trigger_tests'])) {
            return;
        }
        if (!empty($ff->page->opts['disable-create-triggers'])) {
            return;
        }
            
            // note we may want to override some of these... - to do special triggers..
            // as you can only have one trigger per table for each action.
            
        $this->createDeleteTriggers();
        $this->createInsertTriggers();
        $this->createUpdateTriggers();
        
        
        
    }
    
    function loadIniFiles()
    {
        // will create the combined ini cache file for the running user.
        
        $ff = HTML_FlexyFramework::get();
        $ff->generateDataobjectsCache(true);
        $this->dburl = parse_url($ff->database);
        
        $dbini = 'ini_'. basename($this->dburl['path']);
        
        
        $iniCache = isset( $ff->PDO_DataObject) ?  $ff->PDO_DataObject['schema_location'] : $ff->DB_DataObject[$dbini];
        
        if (strpos($iniCache, PATH_SEPARATOR) !== false) {
            echo "SKIP links code - cached ini file has not been created\n";
            return;
        }
        $this->schema = parse_ini_file($iniCache, true);
        $this->links = parse_ini_file(preg_replace('/\.ini$/', '.links.ini', $iniCache), true);
        
        $lcfg = &$this->links;
        $cfg = empty($ff->DB_DataObject) ? array() : $ff->DB_DataObject;
        
        if (!empty($cfg['table_alias'])) {
            $ta = $cfg['table_alias'];
            foreach($lcfg  as $k=>$v) {
                $kk = $k;
                if (isset($ta[$k])) {
                    $kk = $ta[$k];
                    if (!isset($lcfg[$kk])) {
                        $lcfg[$kk] = array();
                    }
                }
                foreach($v as $l => $t_c) {
                    $bits = explode(':',$t_c);
                    $tt = isset($ta[$bits[0]]) ? $ta[$bits[0]] : $bits[0];
                    if ($tt == $bits[0] && $kk == $k) {
                        continue;
                    }
                    
                    $lcfg[$kk][$l] = $tt .':'. $bits[1];
                    
                    
                }
                
            }
        }
         
        
    }
    function updateTableComments()
    {
        foreach($this->links as $tbl =>$map) {
            $this->updateTableComment($tbl, $map);
            
        }
        
        
    }
    
    function updateTableComment($tbl, $map)
    {
         
        
        if (!isset($this->schema[$tbl])) {
            echo "Skip $tbl = table does not exist in schema\n";
            return;
        }
        
        
        $q = DB_DAtaObject::factory('core_enum');
        $q->query("SELECT
                     TABLE_COMMENT
                    FROM
                        information_schema.TABLES
                    WHERE
                        TABLE_SCHEMA = DATABASE()
                        AND
                        TABLE_NAME = '{$q->escape($tbl)}'
        ");
        $q->fetch();
        $tc = $q->TABLE_COMMENT;
        //echo "$tbl: $tc\n\n";
        if (!empty($q->TABLE_COMMENT)) {
            //var_dump($tc);
            $tc = trim(preg_replace('/FK\([^)]+\)/', '' , $q->TABLE_COMMENT));
            //var_dump($tc);exit;
            // strip out the old FC(....) 
                        
        }
        $fks = array();
        foreach($map as $k=>$v) {
            $fks[] = "$k=$v";
        }
        $fkstr = $tc . ' FK(' . implode("\n", $fks) .')';
        if ($q->TABLE_COMMENT == $fkstr) {
            return;
        }
        
        $q = DB_DAtaObject::factory('core_enum');
        $q->query("ALTER TABLE `$tbl` COMMENT = '{$q->escape($fkstr)}'");
        
        
        
    }
    
    function createDeleteTriggers()
    {
        
        // this should only be enabled if the project settings are configured..
        
       
        
        // delete triggers on targets -
        // if you delete a company, and a person points to it, then it should fire an error...
        
        
        
        
        // create a list of source/targets from $this->links
        
                
        $revmap = array();
        foreach($this->links as $tbl => $map) {
            if (!isset($this->schema[$tbl])) {
                continue;
            }
            foreach($map as $k =>$v) {
                list ($tname, $tcol) = explode(':', $v);
                
                
                if (!isset($revmap[$tname])) {
                    $revmap[$tname] = array();
                }
                $revmap[$tname]["$tbl:$k"] = "$tname:$tcol";
            }
        }
        
        
        
        
        foreach($revmap as $target_table => $sources) {
            
            
            // throw example.. UPDATE `Error: invalid_id_test` SET x=1;
            
            if (!isset($this->schema[$target_table])) {
                echo "Skip $target_table  = table does not exist in schema\n";
                continue;
            }
        
            
            
            $q = DB_DataObject::factory('core_enum');
            $q->query("
                DROP TRIGGER IF EXISTS `{$target_table}_before_delete` ;
            ");
            
            $trigger = "
             
            CREATE TRIGGER `{$target_table}_before_delete`
                BEFORE DELETE ON `{$target_table}`
            FOR EACH ROW
            BEGIN
                DECLARE mid INT(11);
                IF (@DISABLE_TRIGGER IS NULL AND @DISABLE_TRIGGER_{$target_table} IS NULL ) THEN  
               
            ";
            foreach($sources as $source=>$target) {
                list($source_table , $source_col) = explode(':', $source);
                list($target_table , $target_col) = explode(':', $target);
                $err = substr("Failed Delete {$target_table} refs {$source_table}:{$source_col}", 0, 64);
                $trigger .="
                    SET mid = 0;
                    IF OLD.{$target_col} > 0 THEN 
                        SELECT count(*) into mid FROM {$source_table} WHERE {$source_col} = OLD.{$target_col} LIMIT 1;
                        IF mid > 0 THEN   
                           UPDATE `$err` SET x = 1;
                        END IF;
                    END IF;
                ";
            }
            
            $ar = $this->listTriggerFunctions($tbl, 'delete');
            foreach($ar as $fn=>$col) {
                $trigger .= "
                    CALL $fn( OLD.{$col});
                ";
            }
            
            $trigger .= "
                END IF;
            END 
           
            ";
            
            //DB_DAtaObject::debugLevel(1);
            $q = DB_DataObject::factory('core_enum');
            $q->query($trigger);
             echo "CREATED TRIGGER {$target_table}_before_delete\n";
        }
        
        
        // inserting - row should not point to a reference that does not exist...
        
        
        
        
    }
    function createInsertTriggers()
    {
        foreach($this->links as $tbl => $map) {
            if (!isset($this->schema[$tbl])) {
                continue;
            }
            
            $q = DB_DataObject::factory('core_enum');
            $q->query("
                DROP TRIGGER IF EXISTS `{$tbl}_before_insert` ;
            ");
            
            $trigger = "
             
            CREATE TRIGGER `{$tbl}_before_insert`
                BEFORE INSERT ON `{$tbl}`
            FOR EACH ROW
            BEGIN
               DECLARE mid INT(11);
                IF (@DISABLE_TRIGGER IS NULL AND @DISABLE_TRIGGER_{$tbl} IS NULL ) THEN 
               
            ";
            $has_checks=  false;
            $errs = array();
            foreach($map as $source_col=>$target) {
                // check that source_col exists in schema.
                if (!isset($this->schema[$tbl][$source_col])) {
                    $errs[] = "SOURCE MISSING: $source_col => $target";
                    continue;
                }
                
                
                $source_tbl = $tbl;
                list($target_table , $target_col) = explode(':', $target);
                
                if (!isset($this->schema[$target_table])) {
                    // skip... target table does not exist
                    $errs[] = "TARGET MISSING: $source_col => $target";
                    continue;
                }
                
                
                $err = substr("Fail: INSERT referenced {$tbl}:{$source_col}", 0, 64);
                $trigger .="
                    SET mid = 0;
                    if NEW.{$source_col} > 0 THEN
                        SELECT {$target_col} into mid FROM {$target_table} WHERE {$target_col} = NEW.{$source_col} LIMIT 1;
                        IF mid < 1 THEN
                            UPDATE `$err` SET x = 1;
                        END IF;
                       
                    END IF;
                ";
                $has_checks=  true;
                
                
            }
            
            $ar = $this->listTriggerFunctions($tbl, 'insert');
            foreach($ar as $fn=>$col) {
                $trigger .= "
                    CALL $fn( NEW.{$col});
                ";
                $has_checks=  true;
            }
            
            $trigger .= "
                END IF;
            END 
           
            ";
            
            if (!$has_checks) {
                echo "SKIP TRIGGER {$tbl}_before_insert (missing " . implode(", ", $errs) . ")\n";
                continue;
            }
            //echo $trigger; exit;
            //DB_DAtaObject::debugLevel(1);
            $q = DB_DataObject::factory('core_enum');
            $q->query($trigger);
            echo "CREATED TRIGGER {$tbl}_before_insert\n";
            
            
            
            
            
            
            
        }
        
        
        
    }
     function createUpdateTriggers($target_table = '')
    {
        foreach($this->links as $tbl => $map) {
            if (!isset($this->schema[$tbl])) {
                continue;
            }
            
            $q = DB_DataObject::factory('core_enum');
            $q->query("
                DROP TRIGGER IF EXISTS `{$tbl}_before_update` ;
            ");
            
            $trigger = "
             
            CREATE TRIGGER `{$tbl}_before_update`
                BEFORE UPDATE ON `{$tbl}`
            FOR EACH ROW
            BEGIN
               DECLARE mid INT(11);
               IF (@DISABLE_TRIGGER IS NULL AND @DISABLE_TRIGGER_{$tbl} IS NULL ) THEN  
               
            ";
            $has_checks=  false;
            $errs = array();
            foreach($map as $source_col=>$target) {
                // check that source_col exists in schema.
                if (!isset($this->schema[$tbl][$source_col])) {
                    $errs[] = "SOURCE MISSING: $source_col => $target";
                    continue;
                }
                
                
                $source_tbl = $tbl;
                list($target_table , $target_col) = explode(':', $target);
                
                if (!isset($this->schema[$target_table])) {
                    // skip... target table does not exist
                    $errs[] = "TARGET MISSING: $source_col => $target";
                    continue;
                }
                
                $err = substr("Fail: UPDATE referenced {$tbl}:$source_col", 0, 64);
                $trigger .="
                    SET mid = 0;
                    if NEW.{$source_col} > 0 THEN
                        SELECT {$target_col} into mid FROM {$target_table} WHERE {$target_col} = NEW.{$source_col} LIMIT 1;
                        IF mid < 1 THEN
                            UPDATE `$err` SET x = 1;
                        END IF;
                       
                    END IF;
                ";
                $has_checks=  true;
            }
            $ar = $this->listTriggerFunctions($tbl, 'update');
            foreach($ar as $fn=>$col) {
                $trigger .= "
                    CALL $fn(OLD.{$col}, NEW.{$col});
                ";
                $has_checks=  true;
            }
            
            $trigger .= "
                END IF;
            END 
           
            ";
            if (!$has_checks) {
                echo "SKIP TRIGGER {$tbl}_before_update (missing " . implode(", ", $errs) . ")\n";
                continue;
            }
            
            //echo $trigger; exit;
            //DB_DAtaObject::debugLevel(1);
            $q = DB_DataObject::factory('core_enum');
            $q->query($trigger);
            echo "CREATED TRIGGER {$tbl}_before_update\n";
            
            
        }
        
        
        
    }
    /**
     * check the information schema for any methods that match the trigger criteria.
     *   -- {tablename}_trigger_{optional_string}_before_delete_{column_name}(NEW.column)
     *   -- {tablename}_trigger_{optional_string}_before_update_{column_name}(OLD.column, NEW.column}
     *   -- {tablename}_trigger_{optional_string}_before_insert_{column_name}(OLD.column}
     *
     *
     */
    // type = update/insert/delete
    
    function listTriggerFunctions($table, $type)
    {
        static $cache = array();
        if (!isset($cache[$table])) {
            $cache[$table] = array();
            $q = DB_DAtaObject::factory('core_enum');
            $q->query("SELECT
                            SPECIFIC_NAME
                        FROM
                            information_schema.ROUTINES
                        WHERE
                            ROUTINE_SCHEMA = '{$q->escape($q->database())}'
                            AND
                            ROUTINE_NAME LIKE '" . $q->escape("{$table}_trigger_")  . "%'
                            AND
                            ROUTINE_TYPE = 'PROCEDURE'
                            
            ");
            while ($q->fetch()) {
                $cache[$table][] = $q->SPECIFIC_NAME;
            }
            
        }
        // now see which of the procedures match the specification..
        $ret = array();
        foreach($cache[$table] as $cname) {
            $bits = explode("_before_{$type}_", $cname);
            if (count($bits) < 2) {
                continue;
            }
            $ret[$cname] = $bits[1];
        }
        return $ret;
    }
        
    
    
}


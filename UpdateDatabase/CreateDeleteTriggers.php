<?php

/**
 * Create delete triggers for database tables
 * 
 * This script creates delete triggers that prevent deletion of records
 * that are referenced by other tables, maintaining referential integrity.
 * 
 * Usage:
 *   php press.php Core/UpdateDatabase/CreateDeleteTriggers                    # Create triggers for all tables
 *   php press.php Core/UpdateDatabase/CreateDeleteTriggers -t table_name      # Create trigger for specific table only
 * 
 * @author System
 */

require_once 'Pman/Core/Cli.php';

class Pman_Core_UpdateDatabase_CreateDeleteTriggers extends Pman_Core_Cli
{
    static $cli_desc = "Create delete triggers for database tables to maintain referential integrity";
    static $cli_opts = array(
        'table' => array(
            'desc' => 'Create delete trigger for this table only',
            'default' => '',
            'short' => 't',
            'min' => 0,
            'max' => 1,
        )
    );
    
    var $dburl;
    var $schema;
    var $links = array();
    var $target_table = '';
    
    function getAuth() 
    {
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->cli)) {
            return true;
        }
        return false;
    }
    
    function get($m="", $opts=array())
    {
        $this->target_table = !empty($opts['table']) ? $opts['table'] : '';
        $this->loadIniFiles();
        $this->createDeleteTriggers();
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
            
            // If specific table requested, skip others
            if (!empty($this->target_table) && $target_table !== $this->target_table) {
                continue;
            }
            
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
            
            $ar = $this->listTriggerFunctions($target_table, 'delete');
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
        
        if (!empty($this->target_table)) {
            echo "Completed creating delete trigger for table: {$this->target_table}\n";
        } else {
            echo "Completed creating delete triggers for all tables\n";
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

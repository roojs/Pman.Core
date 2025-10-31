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
require_once 'Pman/Core/UpdateDatabase/MysqlLinks.php';

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
    
    var $mysqlLinks;
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
        // Create MysqlLinks instance to reuse its methods
        // We'll manually call loadIniFiles() to avoid constructor side effects
        $this->mysqlLinks = new Pman_Core_UpdateDatabase_MysqlLinks();
        $this->createDeleteTriggers();
    }
    
    function createDeleteTriggers()
    {
        // Reuse the createDeleteTriggers logic from MysqlLinks
        // but add target_table filtering for CLI option
        
        // create a list of source/targets from $this->mysqlLinks->links
        $revmap = array();
        foreach($this->mysqlLinks->links as $tbl => $map) {
            if (!isset($this->mysqlLinks->schema[$tbl])) {
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
            
            // If specific table requested, skip others (target_table option only for CreateDeleteTriggers)
            if (!empty($this->target_table) && $target_table !== $this->target_table) {
                continue;
            }
            
            // throw example.. UPDATE `Error: invalid_id_test` SET x=1;
            
            if (!isset($this->mysqlLinks->schema[$target_table])) {
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
            
            $ar = $this->mysqlLinks->listTriggerFunctions($target_table, 'delete');
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
}

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
    
    var $dburl;
    var $schema;
    var $links = array();
    var $target_table = '';
    var $mysqlLinks;
    
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
        $this->mysqlLinks = new Pman_Core_UpdateDatabase_MysqlLinks(true);
        $this->target_table = !empty($opts['table']) ? $opts['table'] : '';
        $this->mysqlLinks->loadIniFiles();
        $this->mysqlLinks->createDeleteTriggers($this->target_table);
    }
}

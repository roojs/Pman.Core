<?php

/**
 * Create triggers for database tables
 * 
 * This script creates triggers for database tables to maintain referential integrity.
 * 
 * Usage:
 *   php press.php Core/UpdateDatabase/CreateTriggers                    # Create triggers for all tables
 *   php press.php Core/UpdateDatabase/CreateTriggers -t table_name      # Create trigger for specific table only
 * 
 * @author System
 */

require_once 'Pman/Core/Cli.php';
require_once 'Pman/Core/UpdateDatabase/MysqlLinks.php';

class Pman_Core_UpdateDatabase_CreateTriggers extends Pman_Core_Cli
{
    static $cli_desc = "Create triggers for database tables to maintain referential integrity";
    static $cli_opts = array(
        'table' => array(
            'desc' => 'Create trigger for this table only',
            'default' => '',
            'short' => 't',
            'min' => 0,
            'max' => 1,
        ),
        'debug' => array(
            'desc' => 'Enable debug mode',
            'default' => false,
            'short' => 'd',
            'min' => 0,
            'max' => 0,
        ),
    );
    
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
        $this->mysqlLinks->debug = !empty($opts['debug']) ? true : false;
        $this->target_table = !empty($opts['table']) ? $opts['table'] : '';
        $this->mysqlLinks->loadIniFiles();
        $this->mysqlLinks->createDeleteTriggers($this->target_table);
        $this->mysqlLinks->createInsertTriggers($this->target_table);
        $this->mysqlLinks->createUpdateTriggers($this->target_table);
    }
}

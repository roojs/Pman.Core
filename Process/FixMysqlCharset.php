<?php

require_once 'Pman/Core/Cli.php';

class Pman_Core_Process_FixMysqlCharset extends Pman_Core_Cli {
    
    static $cli_desc = "Base class for CLI only commands";
    static $cli_opts = array(
        'table' => array(
            'desc' => 'Database Table',
            'short' => 't',
            'min' => 1,
            'max' => 1,
            
        ),
        /*
        'field' => array(
            'desc' => 'Table Column Name',
            'short' => 'f',
            'min' => 1,
            'max' => 1,
            
        ),
        */
    );
    
    
    // bugs: 'PICUT, MAËPPE' << strips out chars..
    
    
    /*
     *
     * should we do it in PHP - not using the lating stuff?
     */
    
    function get($req , $opts=array())
    {
       // DB_DataObject::debugLevel(1);
       
        if (file_exists('/tmp/fix_mysql_charset_'. $opts['table'])) {
            echo "Conversion for {$opts['table']} has already been done - doing it again will mess things up.. - delete the /tmp/fix_mysql_charset file if you really want to do this\n\n";
            exit;
        }
        touch('/tmp/fix_mysql_charset_'. $opts['table']);
        
        $this->disableTriggers($opts['table']);
        $this->enableTriggers($opts['table']);
        
        $t = DB_DataObject::factory($opts['table']);
        $cols = $t->tableColumns();
        $t->selectAdd();
        $t->selectAdd("id");
        
        $conv = array();
        $w = array();
        foreach($cols as $k=>$v) {
            if (!($v & 2)) {
                continue;
            }
            if (($v & 4)) { // date?
                continue;
            }
            $conv[] = $k;
            $t->selectAdd($k);
            $t->selectAdd("COALESCE(convert(cast(convert({$k} using  latin1) as binary) using utf8), '') as zz_{$k}");
            $w[] = " convert(cast(convert({$k} using  latin1) as binary) using utf8) != $k";
        }
        $t->whereAdd(implode(" OR ", $w));
        //$t->whereAdd('id=4555');
       // $t->limit(100);
        $t->orderBy('id ASC');
        $all = $t->fetchAll();
        foreach($all as $t) {
            $up =false;
            $tt = clone($t);
            //print_r($tt); exit;
            foreach($conv as $k) {
               if ($t->{$k} != $t->{'zz_'.$k}) {
                    if (strpos($t->{'zz_'. $k}, '?') !== false) {
                        $up = false;
                        continue;
                    }
                
                    $t->{$k} = $t->{'zz_'.$k};
                    $up =true;
               }
               
               
            }
            if ($up) {
                echo "UPDATE $t->id\n";
                $t->_skip_write_xml= true;
                DB_DataObject::debugLevel(1);
                $t->update($tt);
                DB_DataObject::debugLevel(0);
               //  print_r($t);exit;; 
            }
        }
        
        
         
        exit;
    }
    
    var $triggers = array();
    function disabletriggers($tbl)
    {
        
        $t = DB_DataObject::factory($tbl);
         DB_DataObject::debugLevel(1);
        $t->query("SHOW TRIGGERS FROM {$t->databaseNickname()} where `table` = '{$tbl}'");
        $this->triggers = array();
        while ($t->fetch()) {
            $this->triggers[] = $t->toArray('%s', true);
            $d = DB_DataObject::factory($tbl);
            $d->query("DROP TRIGGER {$t->Trigger}");
        }
        
        exit;
    }
    /*
     [Trigger] => account_transaction_before_delete
    [Event] => DELETE
    [Table] => account_transaction
    [Statement] => BEGIN
            
            UPDATE `Error: Not allow to delete transaction` SET x = 1;

        END
    [Timing] => BEFORE
   */
    function enabletriggers($tbl)
    {
        
        
        DB_DataObject::debugLevel(1);
        foreach($this->triggers as $tr) {
            $t = DB_DataObject::factory($tbl);
            $t->query("
                CREATE TRIGGER {$tr['Trigger']} 
                {$tr['Timing']} {$tr['Event']} ON {$tbl}
                FOR EACH ROW
                {$tr['Statement']}
            ");
            
            
            
        }
        
    }
    
    
}

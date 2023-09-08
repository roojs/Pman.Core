<?php

require_once 'Pman.php';

class Pman_Core_DatabaseColumns extends Pman {
    
    
    function getAuth()
    {
        parent::getAuth(); // load company!
        $au = $this->getAuthUser();
       
        if (!$au) {  
            $this->jerror("LOGIN-NOAUTH", "Not authenticated", array('authFailure' => true));
        }
        if (!$au->pid()   ) { // not set up yet..
            $this->jerror("LOGIN-NOAUTH", "Not authenticated", array('authFailure' => true));
        }
        
        
        $this->authUser = $au;
        return true;
    }
    
    function get($table, $opts = Array()) {
        $d = DB_DAtaObject::Factory($table);
        if (method_exists($d, 'availableColumns')) {
            $cols = $d->availableColumns();
        } else {
            
            $re = $d->autoJoin();
            //echo '<PRE>';print_r($re);
            $cols = $re['cols'] ;
            
            
            $types = array();
            $tables = array();
            $schemas = array($table => $d->table());
            
            foreach($cols as $name=>$table_col) {
                list($tbl, $col) = explode('.', $table_col);
                if (!isset($schemas[$tbl])) {
                    $schemas[$tbl] = DB_DataObject::Factory($tbl)->table();
                }
                $types[$name] = $schemas[$tbl][$col];
                $tables[$name] = $tbl;
            }
             
            foreach($re['join_names'] as $c=>$f) {
                $cols[$c] = $f;
            }
            
        }
        
        
        
        foreach($cols as $c=>$f) {
            $ret[]  = array(
                'name' => $c,
                'val' => $f,
                'type' => isset($types[$c]) ? $this->typeToName($types[$c]) : -1,
                'table' => isset($tables[$c]) ? $tables[$c] : "",
            );
            
        }
        
        $this->jdata($ret);
    }
    
    function typeToName($t)
    {
        switch(true) {
            case ($t & 64): return 'text';
            case ($t & 32): return 'text';
            case ($t & 4 && $t & 8): return 'datetime';
            case ($t & 4): return 'date';
            case ($t & 8): return 'time';
            case ($t & 16): return 'bool';
            case ($t & 2): return 'varchar';
            case ($t & 1): return 'number';
                
        }
        return '??';
        
    }
}

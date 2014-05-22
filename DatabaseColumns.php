<?php

require_once 'Pman.php';

class Pman_Core_DatabaseColumns extends Pman {
    
    
    function getAuth()
    {
        parent::getAuth(); // load company!
        $au = $this->getAuthUser();
       
        if (!$au) {  
            $this->jerr("Not authenticated", array('authFailure' => true));
        }
        if (!$au->pid()   ) { // not set up yet..
            $this->jerr("Not authenticated", array('authFailure' => true));
        }
        
        
        $this->authUser = $au;
        return true;
    }
    
    function get($table) {
        $d = DB_DAtaObject::Factory($table);
        if (method_exists($d, 'availableColumns')) {
            $cols = $d->availableColumns();
        } else {
            
            $re = $d->autoJoin();
            //echo '<PRE>';print_r($re);
            $cols = $re['cols'] ;
            
            foreach($re['join_names'] as $c=>$f) {
                $cols[$c] = $f;
            }
        }
            
        foreach($cols as $c=>$f) {
            $ret[]  = array(
                'name' => $c,
                'val' => $f
            );
            
        }
        
        $this->jdata($ret);
    }
}
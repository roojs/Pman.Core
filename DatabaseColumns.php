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
        $re = $d->autoJoin();
        echo '<PRE>';print_r($re);
        $ret = array ();
        foreach($re['cols'] as $c=>$f) {
            $ret[]  = array(
                'name' => $c,
                'val' => $f
            );
            
        }
        $this->jdata($ret);
        
        
    }
}
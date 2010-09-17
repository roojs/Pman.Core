<?php

/**
 * 
 * this is technically a cli wrapper for the generator..
 * 
 * we will test it on the web initially..
 * 
 * 
 */
require_once 'Pman.php';
class Pman_Core_RunGenerator extends Pman
{     
    var $cli = false;
    function getAuth() {
        
        $o = PEAR::getStaticProperty('HTML_FlexyFramework', 'options');
        if (!empty($o['cli'])) {
            $this->cli = true;
            return true;
        }
        
        parent::getAuth(); // load company!
        $au = $this->getAuthUser();
        if (!$au || $au->company()->comptype != 'OWNER') {
            $this->jerr("Not authenticated", array('authFailure' => true));
        }
        $this->authUser = $au;
        return true;
    }
     
    function get($args)
    {
        require_once 'Pman/Core/Generator.php';
        ini_set('pcre.backtrack_limit', 2000000);
        ini_set('pcre.recursion_limit', 2000000);
        $this->init();
         
        $lastarg = $this->cli  ? array_pop($_SERVER['argv']) : '';
        if (preg_match('/RunGenerator/', $lastarg)) {
            $lastarg  = '';
        }
        $x = new Pman_Core_Generator();
       // $x->page = clone($this);
        $x->start($this->cli, $args, $lastarg);
        $ff = HTML_FlexyFramework::get();
        
        print_r($ff->DB_DataObject);
        
        
        die("done!");
    }
    
}
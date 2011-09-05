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
    static $cli_desc = "Generate DataObjects (runs updatedatabase first)";
   
    static $cli_opts = array(
        'module' => array(
            'desc' => 'Module',
            'short' => 'm',
            'default' => '',
            'min' => 0,
            'max' => 1,
            
        ),
        'overwrite' => array(
            'desc' => 'Files to Overwrite',
            'default' => '',
            'short' => 'o',
            'min' => 0,
            'max' => 999,
            
        )
        
    );
    
    var $cli = false;
    function getAuth() {
        
        
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->cli)) {
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
        HTML_FlexyFramework::run('Core/UpdateDatabase');
        
         
        
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
        
        // technically it would be good to trash the cached ini files here.. 
        // however we can not really do that, as the ownships are off..
        //we can however regen our own files..
        HTML_FlexyFramework::get()->generateDataobjectsCache(true);
        
        die("done!");
    }
    
}
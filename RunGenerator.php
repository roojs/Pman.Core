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
    static $cli_desc = "Generate DataObjects (runs updatedatabase first)  
                     does not change files, just shows you want would happen";
        
        
    static $cli_opts = array(
        'module' => array(
            'desc' => 'Module (if new tables are found, they will be put in the modules database directory',
            'short' => 'm',
            'default' => '',
            'min' => 1,
            'max' => 1,
            
        ),
        'overwrite' => array(
            'desc' => 'Files to Overwrite (use _all_ to create everything)',
            'default' => '',
            'short' => 'o',
            'min' => 1,
            'max' => -1,
            
        ),
        'noupdate' => array(
            'desc' => 'Do not update the database using sql',
            'default' => '',
            'short' => 'n',
            'min' => 1,
            'max' => -1,
            
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
            $this->jerror("LOGIN-NOAUTH", "Not authenticated", array('authFailure' => true));
        }
        $this->authUser = $au;
        return true;
    }
     
    function get($args, $opts=array())
    {
        //print_r($opts);exit;
        if (empty($opts['noupdate'])) {
            HTML_FlexyFramework::run('Core/UpdateDatabase');
        }
        
        
         
        
        require_once 'Pman/Core/Generator.php';
        ini_set('pcre.backtrack_limit', 2000000);
        ini_set('pcre.recursion_limit', 2000000);
        $this->init();
        
        $x = new Pman_Core_Generator();
       // $x->page = clone($this);
       
        
        $modules = $opts['module'];
        // overwrite can be multiple
        $overwrite = is_string($opts['overwrite']) ? array($opts['overwrite']) : $opts['overwrite'];

        $x->start($this->cli, $modules, $overwrite);
        
        // technically it would be good to trash the cached ini files here.. 
        // however we can not really do that, as the ownships are off..
        //we can however regen our own files..
        HTML_FlexyFramework::get()->generateDataobjectsCache(true);
        
        die("done!");
    }
    
}
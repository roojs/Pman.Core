<?php
/*

 Base class for CLI only commands

*/

require_once 'Pman.php';
class Pman_Core_Cli extends Pman
{
    static $cli_desc = "Base class for CLI only commands";
    
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        if (!$ff->cli) {
            die("CLI ONLY");
        }
    }
    
    
    function get()
    {
        die("this is only used as a base class for Cli based commands - extend to use.");
    }
 
    
}
<?php

/*
 *
 *
find . | grep php | awk '{ print "php admin.php Core/Process/FixCode -f " $1 }' | sh
*/

require_once 'Pman.php';

class Pman_Core_Process_FixDataObjectCtor extends Pman 
{
    static $cli_desc = "Coverts old code using replacements"; 
    
    static $cli_opts = array(
        'file' => array(
            'desc' => 'File to process',
            'short' => 'f',
            'min' => 1,
            'max' => 1,
            
        ),
        
    );
    
    function getAuth() {
        $ff = HTML_FlexyFramework::get();
        
        if (!$ff->cli) {
            die("cli only");
        }
        
    }
    
    
    
    function get($p,$opts)
    {
        $file = realpath($opts['file']);
        if (!file_exists($file) || !is_writable($file)) {
            echo "$file: NOT readable or writable\n";
            exit;
        }
        
        $c = file_get_contents($file);
        $old_c = $c;
        if (strpos($c, '<?php') === false) {
            echo "$file: NOT PHP\n";
            exit;
        }
        $c = preg_replace("/new DataObjects_([a-z_]+)/i", "DB_DataObject::factory('\\1')", $c);
        
        /// this one tends t hit a few odd comment examples..
        //$c = preg_replace("/DataObjects_([a-z_]+)::/i", "DB_DataObject::factory('\\1')->", $c);
        
        $c = preg_replace("/DB_DataObject::staticGet\(\"DataObjects_([a-z_]+)\"\s*,/i", "DB_DataObject::factory('\\1')->load(", $c);
        $c = preg_replace("/DB_DataObject::staticGet\('DataObjects_([a-z_]+)'\s*,/i", "DB_DataObject::factory('\\1')->load(", $c);

      
        $c = preg_replace("/DB_DataObject::factory\('([a-z_]+)'\)::/i", "DB_DataObject::factory('\\1')->", $c);
        $c = preg_replace("/DB_DataObjects::factory/i", "DB_DataObject::factory", $c); // typo...

        
        
        if ($old_c == $c) {
            echo "$file: SKIP NO CHANGES\n";
            exit;
        }
        echo "$file: WRITE NEW FILE\n";
        file_put_contents($file,$c);
        exit;
        
    }
    
}
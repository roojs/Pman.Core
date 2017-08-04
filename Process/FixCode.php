<?php

/*
 *
 *
find . | grep php | awk '{ print "php admin.php Core/Process/FixCode -f " $1 }' | sh
*/

require_once 'Pman.php';

class Pman_Core_Process_FixCode extends Pman 
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
    
    
    var $factory_map = array(
    
            'Person' =>'core_person' ,
            'Companies'=>'core_company' ,
            'group_members'=>'core_group_member' ,
            'group_rights'=>'core_group_right' ,
            'Groups'=>'core_group' ,
            'Office'=>'core_office' ,
            'Projects'=>'core_project' ,
            
            // timesheet project..
            'STAFF' => 'core_person',
            'timesheet' => 'timesheet_week',
            'activity' => 'timesheet_activity',
            'timesheetapprovals' => 'timesheet_week',
            'projectleaders' => 'timesheet_project_leader',
            'userprojects' => 'timesheet_user_project',
            
            
            
    );
    
    function get($p,$opts=array())
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
        foreach($this->factory_map as $from=>$to) {
            
            $c = preg_replace("/DB_DataObject::factory\((\s{0,}['\"]" . $from . "['\"]\s{0,})\)/i", "DB_DataObject::factory('{$to}')", $c);
            
//            $c = str_ireplace("DB_DataObject::factory('$from')","DB_DataObject::factory('$to')", $c);
//            $c = str_ireplace("DB_DataObject::factory(\"$from\")","DB_DataObject::factory('$to')", $c);
            
        }
        if ($old_c == $c) {
            echo "$file: SKIP NO CHANGES\n";
            exit;
        }
        echo "$file: WRITE NEW FILE\n";
        file_put_contents($file,$c);
        exit;
        
    }
    
}
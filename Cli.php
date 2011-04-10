<?php
/*

Help file for cli in this directory.
*/


class Pman_Core_Cli
{
    
    function getAuth()
    {
        return false;
    }
    function help($cli)
    {
        echo "

    $cli Core/RunGenerator

    Creates ALL database tables
    - does not change files, just shows you want would happen
        
        
    $cli Core/RunGenerator/COMPONENT

    Runs the generator for a COMPONENT (NOTE - will update that COMPONENT sql)
    - does not change files, just shows you want would happen

    
    $cli Core/RunGenerator/COMPONENT pman.ini,COMPONENT.readers.js,...

    Runs the generator for a project (NOTE - runs all the SQL updates)
    - Changes the files.

 
================================    

    $cli Core/JsCompile   build PROJECT
     
    Runs the javascript compiler - merging all the JS files so the load faster.
    Note: cfg option Pman_Builder['jspacker'] must be set to location of jstoolkit code 

================================    

    $cli Core/Notify
    
    Runs the notification tool - should be run every minute ideally.
    Sends out emails to anyone in the notification list.
    
    /etc/cron.d/pman-core-notify
     * *  * * *     www-data     /usr/bin/php /home/gitlive/web.mtrack/admin.php  Core/Notify > /dev/null
    
        
";


    }
    
    
}
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

";


    }
    
    
}
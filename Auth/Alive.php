<?php

require_once 'Pman/Core/Auth.php';

/***
* 
* Authentication - Alive - nagios call?
*
* (was Login (with a agent chec)
*
* just responds for nagios to say it's alive (not sure this is a great check - as in theory no DB calls will be done)
*
* GET only 
* 
*/



class Pman_Core_Auth_Alive extends Pman_Core_Auth
{
    function get($v, $opts=array())
    {
        if (!empty($_SERVER['HTTP_USER_AGENT']) && preg_match('/^check_http/', $_SERVER['HTTP_USER_AGENT'])) {
			die("server is alive = authFailure"); // should really use heartbeat now..
		}
        die("INVALID URL");
    }
}
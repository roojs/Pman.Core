<?php

require_once  'Pman.php';
class Pman_Core_UploadProgress extends Pman
{
  function getAuth() {
        
        $au = $this->getAuthUser();
        if (!$au) {
             $this->jerror("LOGIN-NOAUTH", "Not authenticated", array('authFailure' => true));
        }
        $this->authUser = $au;
        // check that it's a supplier!!!! 
        
        return true; 
    }
    function get($v, $opts=array())
    {
        $this->sessionState(0); // turn off the session..
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        if (  !function_exists('uploadprogress_get_info')) {
            $this->jok(false);
           }
        if (!empty($_GET['id'])) {
            $ret = uploadprogress_get_info($_GET['id']);
             
            $this->jok($ret);
        }
        $this->jerr("no data");
    }
    
}
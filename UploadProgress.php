<?php

require_once  'Pman.php';
class Pman_Core_UploadProgress extends Pman
{
  function getAuth() {
        
        $au = $this->getAuthUser();
        if (!$au) {
             $this->jerr("Not authenticated", array('authFailure' => true));
        }
        $this->authUser = $au;
        // check that it's a supplier!!!! 
        
        return true; 
    }
    function get()
    {
        
        
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        //print_r('in');
        if (  !function_exists('uploadprogress_get_info')) {
            $this->jok(false);
           }
        //print_r('in2');
        if (!empty($_GET['id'])) {
           // var_dump(uploadprogress_get_info($_GET['id']));
            $this->jok(uploadprogress_get_info($_GET['id']));
        }
        $this->jerr("no data");
    }
    
}
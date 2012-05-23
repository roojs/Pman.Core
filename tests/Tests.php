<?php

// include to make the tests run..
// this needs fixing - probably needs to point to a test framework..
$_test_dir  = dirname(__FILE__).'/home/gitlive/web.roojsolutions/';

ini_set('include_path', 
            $_test_dir  . ':' . 
            $_test_dir  .'/pearfix:' . 
            $_test_dir  .'/pear:' . 
            ini_get('include_path'));

define('DB_DATAOBJECT_NO_OVERLOAD', true);



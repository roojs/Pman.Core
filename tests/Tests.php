<?php

// include to make the tests run..
$_test_dir  = dirname(__FILE__).'/../../';

ini_set('include_path', 
            $_test_dir  . ':' . 
            $_test_dir  .'/pearfix:' . 
            $_test_dir  .'/pear:' . 
            ini_get('include_path'));

define('DB_DATAOBJECT_NO_OVERLOAD', true);



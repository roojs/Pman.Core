<?php

require_once 'Pman.php';

class Pman_Core_tests_Images extends Pman
{
     function get($v, $opts=array()) {
        // test...
        echo '<PRE>';
        require_once 'Pman/Core/Images.php';
        //init();
        
        $d = DB_DataObject::factory('pressrelease_entry');
        $d->get(2017);
        
        echo htmlspecialchars(Pman_Core_Images::replaceImageURLS($d->content));
        
        exit;
        
        
        $ret = Pman_Core_Images::replaceImageURLS('
                <img src="http://www.roojs.com/index.php/Core/Images/Thumb/200x40/34/test.png">
                <img src="http://www.roojs.com/index.php/Images/34/test.png" width="100">
                <a href="http://www.roojs.com/index.php/Images/Download/34/test.png">test</a>
                
        ', 'http://roojs.com/index.php/');
        echo htmlspecialchars($ret);
        
     }
     function output()
     {
        exit;
     }
}

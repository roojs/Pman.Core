<?php

require_once 'Pman.php';

class Pman_Core_tests_Images extends Pman
{
     function get() {
        // test...
        echo '<PRE>';
        require_once 'Pman/Core/Images.php';
        $ret = Pman_Core_Images::replaceImg('
                <img src="http://test.php/Core/Images/Thumb/200x40/34/test.png">
                <img src="http://test.php/Images/34/test.png" width="100">
                <a href="http://test.php/Images/Download/34/test.png">test</a>
                
        ');
        echo htmlspecialchars($ret);
        
     }
     function output()
     {
        exit;
     }
}

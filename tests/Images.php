<?php

require_once 'Tests.php';

require_once 'Pman/Core/Images.php';

// test...
echo '<PRE>';
Pman_Core_Images::replaceImg('
        <img src="http://test.php/Core/Images/Thumb/200x40/34/test.png">
        <img src="http://test.php/Images/34/test.png" width="100">
        <img src="http://test.php/Images/Download/34/test.png">        
        
');

<?php

/**
 *
 *  code that used to be in Pman (sendTemplate / emailTemplate)
 *
 *  usage:
 *
 *
 *  $x= new Pman_Core_Mailer($opts)
 *
 *  $opts[
       page => 
       contents
       template
       replaceImages => true|false
    ]
 *
 *  $x->asData(); // returns data needed for notify?? - notify should really
 *                  // just use this to pass around later..
 *
 *  $x->send();
 *
 */

class Pman_Core_Mailer {
    
    var $page           = false;
    var $contents       = false;
    var $template       = false;
    var $replaceImages  = false;
    
    function Pman_Core_Mailer($args) {
        foreach($args as $k=>$v) {
            // a bit trusting..
            $this->$k =  $v;
        }
    }
    
    
    
}
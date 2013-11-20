<?php

require_once 'Pman.php';

class Pman_Core_Tests_Mailer extends Pman
{
     static $cli_desc = "Send out Test emails using Mailer API";
    
    static $cli_opts = array(
        'to' => array(
            'desc' => 'TO: email address',
          //  'default' => '',
            'short' => 't',
            'min' => 1,
            'max' => 1,
            
        )
    );
    
    function getAuth()
    {
        
        if (!$this->bootLoader->cli) {
            die("NOT PUBLIC");
        }
        
    }
    
    function get($q, $opts)
    {
        // send a test email to me...
        $this->rcpts = $opts['to'];
        $this->subject = "test email";
        
        for($i = 0; $i < 10; $i++) {
            $this->test[$i] = $i;
            
        }
          require_once 'Pman/Core/Mailer.php';
        $r = new Pman_Core_Mailer(array(
            'template'=> 'test',
            'contents' => array(),
            'page' => $this,
            'attachments' => array(
                array(
               
                    'file' => '/home/alan/Documents/Nestplaytimespjadcover.pdf',
          
                    'mimetype' => 'application/pdf'
                    
                )
            )
        ));
        return $r->send();
        
        $this->sendTemplate('test',array());
        die("done?");
        
    }
    
    
    
}
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
    
    function get($q,  $opts=array())
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
               
                    'file' => '/home/alan/Documents/Nestplaytimespjadcover.pdf', // pass the file path DO NOT pass the file content
                    'name' => 'Nestplaytimespjadcover.pdf',
                    'mimetype' => 'application/pdf'
                    
                )
            )
        ));
        $res = $r->send();
        var_dump($res);
        
        die("done?");
        
    }
    
    
    
}
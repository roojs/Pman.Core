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
    
    var $page           = false; /* usually a html_flexyframework_page */
    var $contents       = false; /* object or array */
    var $template       = false; /* string */
    var $replaceImages  = false; /* boolean */
    
    function Pman_Core_Mailer($args) {
        foreach($args as $k=>$v) {
            // a bit trusting..
            $this->$k =  $v;
        }
    }
     
    /**
     * ---------------- Global Tools ---------------   
     */
    
    function toData()
    {
    
        $templateFile = $this->template;
        $args = $this->contents;
        
        $content  = clone($page);
        
        foreach((array)$args as $k=>$v) {
            $content->$k = $v;
        }
        
        $content->msgid = empty($content->msgid ) ? md5(time() . rand()) : $content->msgid ;
        
        $ff = HTML_FlexyFramework::get();
        $http_host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : 'pman.HTTP_HOST.not.set';
        if (isset($ff->Pman['HTTP_HOST'])) {
            $http_host  = $ff->Pman['HTTP_HOST'];
        }
        
        
        $content->HTTP_HOST = $http_host;
        
        
        
        // this should be done by having multiple template sources...!!!
        
        require_once 'HTML/Template/Flexy.php';
        
        $htmlbody = false;
        
        if (is_string($template->resolvePath('mail/'.$template.'.body.html')) ) {
            // then we have a multi-part email...
            
            
            $htmltemplate = new HTML_Template_Flexy(  );
            $htmltemplate->compile('mail/'. $templateFile.'.body.html');
            $htmlbody =  $template->bufferedOutputObject($content);
            
            // for the html body, we may want to convert the attachments to images.
            
            
             
        } 
        $template = new HTML_Template_Flexy( array(
                'nonHTML' => true,
        ));
        
        $template->compile('mail/'. $templateFile.'.txt');
        
        /* use variables from this object to ouput data. */
        $mailtext = $template->bufferedOutputObject($content);
        
        
        
        
        
        
        
        
        
        
        //echo "<PRE>";print_R($mailtext);
        
        /* With the output try and send an email, using a few tricks in Mail_MimeDecode. */
        require_once 'Mail/mimeDecode.php';
        require_once 'Mail.php';
        
        $decoder = new Mail_mimeDecode($mailtext);
        $parts = $decoder->getSendArray();
        if (PEAR::isError($parts)) {
            return $parts;
            //echo "PROBLEM: {$parts->message}";
            //exit;
        } 
        
        
        
        
        $parts[1]['Message-Id'] = '<' .   $content->msgid   .
                                     '@' . $content->HTTP_HOST .'>';
        
        
       // list($recipents,$headers,$body) = $parts;
        return array(
            'recipents' => $parts[0],
            'headers' => $parts[1],
            'body' => $parts[2]
        );
    }
    function send()
    {
        
        $email = $this->toData();
        if (is_a($email, 'PEAR_Error')) {
            return $email;
        }
        ///$recipents = array($this->email);
        $mailOptions = PEAR::getStaticProperty('Mail','options');
        $mail = Mail::factory("SMTP",$mailOptions);
        $headers['Date'] = date('r');
        if (PEAR::isError($mail)) {
            return $mail;
        } 
        $oe = error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
        $ret = $mail->send($email['recipents'],$email['headers'],$email['body']);
        error_reporting($oe);
       
        return $ret;
    }
    
    function htmlbodytoCID($html)
    {
        $dom = new DOMDocument;
        $dom->loadHTML($html);
        $imgs= $dom->getElementsByTagName('img');
        
        foreach ($imgs as $i=>$img) {
            $dom->
            
            
        }
        
        
    }
    function fetchImage($url)
    {
        // CACHE???
        // 2 files --- the info file.. and the actual file...
        $cache = ini_get('session.save_path').'/Pman_Core_Mailer/' . md5($url);
        
        
        $a = &new HTTP_Request($url);
        $a->sendRequest();
        echo $a->getResponseBody();
        $fn = $this->page->tempName('tmp');
        
        $data = file_get_contents($url);
        
        
        
        
        
    }
    
    
    
}
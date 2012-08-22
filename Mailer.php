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
    
    
    var $images         = array(); // generated list of cid images for sending
    
    
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
        
        $content  = clone($this->page);
        
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
        $htmltemplate = new HTML_Template_Flexy(  );
        if (is_string($htmltemplate->resolvePath('mail/'.$template.'.body.html')) ) {
            // then we have a multi-part email...
            
            
            $htmltemplate = new HTML_Template_Flexy(  );
            $htmltemplate->compile('mail/'. $templateFile.'.body.html');
            $htmlbody =  $template->bufferedOutputObject($content);
            
            // for the html body, we may want to convert the attachments to images.
            
            $this->htmlbodytoCID($htmlbody);
            
             
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
        
        
        
        
        if ($htmlbody !== false) {
            require_once 'Mail/mime.php';
            $mime = new Mail_mime(array('eol' => "\n"));
            
            $mime->setTXTBody($parts[2]);
            $mime->setHTMLBody($htmlbody);
            
            foreach($this->images as $cid=>$cdata) { 
            
                $mime->addHTMLImage(
                    $cdata['file'],
                     $cdata['mimetype'],
                    $cdata['mimetype'],
                    $cid.'.'.$cdata['ext'],
                    true,
                    $cid
                );
            }
            $parts[2] = $mime->get();
            $parts[1] = $mime->headers($parts[1]);
        
        }
        
        
        
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
            $url  = $dom->getAttribute('src');
            $conv = $this->fetchImage($url);
            $this->images[$conv['contentid']] = $conv;
            
            $url->setAttribute('src', 'cid:' . $conv['contentid']);
            
            
        }
        return $dom->saveHTML();
        
        
        
    }
    function fetchImage($url)
    {
        
        if (preg_match('#^file:///#', $url)) {
            $file = preg_replace('#^file://#', '', $url);
            require_once 'File/MimeType.php';
            $m  = new File_MimeType();
            $mt = $m->fromFilename($file);
            $ext = $m->toExt($mt); 
            
            return array(
                    'mimetype' => $mt,
                   'ext' => $ext,
                   'contentid' => md5($file),
                   'file' => $file
            );
            
        }
        
        // CACHE???
        // 2 files --- the info file.. and the actual file...
        $cache = ini_get('session.save_path').'/Pman_Core_Mailer/' . md5($url);
        if (file_exists($cache) and filemtime($cache) > strtotime('NOW - 1 WEEK')) {
            $ret =  json_decode($cache);
            $ret['file'] = $cache . '.data';
            return $ret;
        }
        if (!file_exists(dirname($cache))) {
            mkdir(dirname($cache),0666, true);
        }
        
        
        $a =  new HTTP_Request($url);
        $a->sendRequest();
        file_put_contents($cache .'.data', $a->getResponseBody());
        
        $mt = $a->getResponseHeader('Content-Type');
        
        require_once 'File/MimeType.php';
        $m  = new File_MimeType();
        $ext = $m->toExt($mt);
        
        $ret = array(
            'mimetype' => $mt,
            'ext' => $ext,
            'contentid' => md5($url)
            
        );
        
        file_put_contents($cache, json_encode($ret));
        $ret['file'] = $cache . '.data';
        return $ret;
        
    }
    
    
}
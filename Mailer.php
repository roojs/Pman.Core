<?php

/**
 *
 *  code that used to be in Pman (sendTemplate / emailTemplate)
 * 
 *  template is in template directory subfolder 'mail'
 *   
 *  eg. use 'welcome' as template -> this will use templates/mail/welcome.txt
 *  if you also have templates/mail/welcome.body.html - then that will be used as 
 *     the html body
 * 
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
       replaceImages => true|false,
       rcpts => array()   // override recipients..
    ]
 *
 *  recipents is gathered from the resulting template
 *   -- eg.
 *    To: <a>,<b>,<c>
 * 
 * 
 *  if the file     '
 * 
 * 
 *  $x->asData(); // returns data needed for notify?? - notify should really
 *                  // just use this to pass around later..
 *
 *  $x->send();
 *
 */

class Pman_Core_Mailer {
    var $debug          = false;
    var $page           = false; /* usually a html_flexyframework_page */
    var $contents       = false; /* object or array */
    var $template       = false; /* string */
    var $replaceImages  = false; /* boolean */
    var $rcpts   = false;
    var $templateDir = false;
    
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
        
        $tmp_opts = array();
        if (!empty($this->templateDir)) {
            $tmp_opts['templateDir'] = $this->templateDir;
        }
        
        
        $htmlbody = false;
        $htmltemplate = new HTML_Template_Flexy( $tmp_opts );

        if (is_string($htmltemplate->resolvePath('mail/'.$templateFile.'.body.html')) ) {
            // then we have a multi-part email...
            
            
            $htmltemplate->compile('mail/'. $templateFile.'.body.html');
            $htmlbody =  $htmltemplate->bufferedOutputObject($content);
            
            // for the html body, we may want to convert the attachments to images.
//            var_dump($htmlbody);exit;
            //$htmlbody = $this->htmlbodytoCID($htmlbody);
            
              
        }
        $tmp_opts['nonHTML'] = true;
        // $tmp_opts['force'] = true;
        $template = new HTML_Template_Flexy(  $tmp_opts );
        
        $template->compile('mail/'. $templateFile.'.txt');
        
        /* use variables from this object to ouput data. */
        $mailtext = $template->bufferedOutputObject($content);
        //print_r($mailtext);exit;
       
        
        
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
            $mime = new Mail_mime(array('eol' => "\n",
                                    'html_encoding' => 'base64',
                                    'html_charset' => 'utf-8',
                                    'text_charset' => 'utf-8',
                                    'head_charset' => 'utf-8'
                ));
            
            $mime->setTXTBody($parts[2]);
            $mime->setHTMLBody($htmlbody);
//            var_dump($mime);exit;
            foreach($this->images as $cid=>$cdata) { 
            
                $mime->addHTMLImage(
                    $cdata['file'],
                     $cdata['mimetype'],
                     $cid.'.'.$cdata['ext'],
                    true,
                    $cdata['contentid']
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
        if ($this->debug) {
            echo '<PRE>';echo htmlspecialchars(print_r($email,true));
        }
        ///$recipents = array($this->email);
        $mailOptions = PEAR::getStaticProperty('Mail','options');
        //print_R($mailOptions);exit;
        $mail = Mail::factory("SMTP",$mailOptions);
        $headers['Date'] = date('r'); 
        if (PEAR::isError($mail)) {
            return $mail;
        } 
        $rcpts = $this->rcpts == false ? $email['recipents'] : $this->rcpts;
        
        if (!empty($this->contents['bcc']) && is_array($this->contents['bcc'])) {
            $rcpts =array_merge(is_array($rcpts) ? $rcpts : array($rcpts), $this->contents['bcc']);
        }
        
        $oe = error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
        $ret = $mail->send($rcpts,$email['headers'],$email['body']);
        error_reporting($oe);
       
        return $ret;
    }
    
    function htmlbodytoCID($html)
    {
        $dom = new DOMDocument;
        $dom->loadHTML($html);
        $imgs= $dom->getElementsByTagName('img');
        
        foreach ($imgs as $i=>$img) {
            $url  = $img->getAttribute('src');
            $conv = $this->fetchImage($url);
            $this->images[$conv['contentid']] = $conv;
            
            $img->setAttribute('src', 'cid:' . $conv['contentid']);
            
            
        }
        return $dom->saveHTML();
        
        
        
    }
    function fetchImage($url)
    {
        
        if ($url[0] == '/') {
            $file = $this->page->rootDir . $url;
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
        
        //print_R($url); exit;
        
        
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
        // add user
        $cache = ini_get('session.save_path').'/Pman_Core_Mailer/' . md5($url);
        if (file_exists($cache) and filemtime($cache) > strtotime('NOW - 1 WEEK')) {
            $ret =  json_decode($cache);
            $ret['file'] = $cache . '.data';
            return $ret;
        }
        if (!file_exists(dirname($cache))) {
            mkdir(dirname($cache),0666, true);
        }
        
        require_once 'HTTP/Request.php';
        $a = new HTTP_Request($url);
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
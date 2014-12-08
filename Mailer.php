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
 *  $x= Pman_Core_Mailer(array(
       page => 
       contents
       template
       html_locale => 'en' == always use the 'english translated verison'
       replaceImages => true|false,
       locale => 'en' .... or zh_hk....
       rcpts => array()   // override recipients..
       attachments => array(
        array(
          file: 
          name : (optional) - uses basename of file
          mimetype : 
        ), 
        ......
        mail_method : (SMTP or SMTPMX)
  
    )
 *
 *  recipents is gathered from the resulting template
 *   -- eg.
 *    To: <a>,<b>,<c>
 * 
 * 
 *  if the file     '
 * 
 * 
 *  $x->toData(); // returns data needed for notify?? - notify should really
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
    var $locale = false; // eg. 'en' or 'zh_HK'
    
    
    var $html_locale = false; // eg. 'en' or 'zh_HK'
    var $images         = array(); // generated list of cid images for sending
    var $attachments = false;
    var $css_inline = false; // not supported
    var $css_embed = false; // put the css tags into the body.
    
    var $mail_method = 'SMTP';
    
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
        
        $tmp_opts = array(
           // 'forceCompile' => true,
            'site_prefix' => false,
        );
        if (!empty($this->templateDir)) {
            $tmp_opts['templateDir'] = $this->templateDir;
        }
        $fopts = HTML_FlexyFramework::get()->HTML_Template_Flexy;
        if (!empty($fopts['DB_DataObject_translator'])) {
            $tmp_opts['DB_DataObject_translator'] = $fopts['DB_DataObject_translator'];
        }
        if (!empty($fopts['locale'])) {
            $tmp_opts['locale'] = $fopts['locale'];
        }
        
        // local opt's overwrite
        if (!empty($this->locale)) {
            $tmp_opts['locale'] = $this->locale;
        }
        
        $htmlbody = false;
        $html_tmp_opts = $tmp_opts;
        $htmltemplate = new HTML_Template_Flexy( $html_tmp_opts );
        if (is_string($htmltemplate->resolvePath('mail/'.$templateFile.'.body.html')) ) {
            // then we have a multi-part email...
            
            if (!empty($this->html_locale)) {
                $html_tmp_opts['locale'] = $this->html_locale;
            }
            $htmltemplate = new HTML_Template_Flexy( $html_tmp_opts );
            
            $htmltemplate->compile('mail/'. $templateFile.'.body.html');
            $htmlbody =  $htmltemplate->bufferedOutputObject($content);
            
            $this->htmlbody = $htmlbody;
            
            // for the html body, we may want to convert the attachments to images.
//            var_dump($htmlbody);exit;
            if ($this->replaceImages) {
                $htmlbody = $this->htmlbodytoCID($htmlbody);    
            }
            if ($this->css_embed) {
                $htmlbody = $this->htmlbodyCssEmbed($htmlbody);    
              
            }
        }
        $tmp_opts['nonHTML'] = true;
        
        
        //print_R($tmp_opts);
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
        
        $isMime = false;
        
        require_once 'Mail/mime.php';
        $mime = new Mail_mime(array(
            'eol' => "\n",
            //'html_encoding' => 'base64',
            'html_charset' => 'utf-8',
            'text_charset' => 'utf-8',
            'head_charset' => 'utf-8',
        ));
        // clean up the headers...
        
        
        $parts[1]['Message-Id'] = '<' .   $content->msgid   .
                                     '@' . $content->HTTP_HOST .'>';
        
          
        if ($htmlbody !== false) {
            // got a html headers...
            
            if (isset($parts[1]['Content-Type'])) {
                unset($parts[1]['Content-Type']);
            }
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
            $isMime = true;
        }
        
        if(!empty($this->attachments)){
            //if got a attachments
            $header = $mime->headers($parts[1]);
            
            if (isset($parts[1]['Content-Type'])) {
                unset($parts[1]['Content-Type']);
            }
            
            if (!$isMime) {
            
                if(preg_match('/text\/html/', $header['Content-Type'])){
                    $mime->setHTMLBody($parts[2]);
                    $mime->setTXTBody('This message is in HTML only');
                }else{
                    $mime->setTXTBody($parts[2]);
                    $mime->setHTMLBody('<PRE>'.htmlspecialchars($parts[2]).'</PRE>');
                }
            }
            foreach($this->attachments as $attch){
                $mime->addAttachment(
                        $attch['file'],
                        $attch['mimetype'],
                        (!empty($attch['name'])) ? $attch['name'] : '',
                        true
                );
            }
            
            $isMime = true;
        }
        
        if($isMime){
            $parts[2] = $mime->get();
            $parts[1] = $mime->headers($parts[1]);
        }
//        echo '<PRE>';
//        print_r('parts');
//        print_r($parts[2]);
//        exit;
       // list($recipents,$headers,$body) = $parts;
        return array(
            'recipents' => $parts[0],
            'headers' => $parts[1],
            'body' => $parts[2],
            'mailer' => $this
        );
    }
    function send($email = false)
    {
        
        $pg = HTML_FlexyFramework::get()->page;
        
        
        $email = is_array($email)  ? $email : $this->toData();
        if (is_a($email, 'PEAR_Error')) {
            $pg->addEvent("COREMAILER-FAIL",  false, "email toData failed"); 
      
            
            return $email;
        }
        if ($this->debug) {
            echo '<PRE>';echo htmlspecialchars(print_r($email,true));
        }
        ///$recipents = array($this->email);
        $mailOptions = PEAR::getStaticProperty('Mail','options');
        //print_R($mailOptions);exit;
        
        if ($this->mail_method == 'SMTPMX' && empty($mailOptions['mailname'])) {
            $pg->jerr("Mail[mailname] is not set - this is required for SMTPMX");
            
        }
        
        $mail = Mail::factory($this->mail_method,$mailOptions);
        if ($this->debug) {
            $mail->debug = $this->debug;
        }
        
        $email['headers']['Date'] = date('r'); 
        if (PEAR::isError($mail)) {
            $pg->addEvent("COREMAILER-FAIL",  false, "mail factory failed"); 
      
            
            return $mail;
        } 
        $rcpts = $this->rcpts == false ? $email['recipents'] : $this->rcpts;
        
        if (!empty($this->contents['bcc']) && is_array($this->contents['bcc'])) {
            $rcpts =array_merge(is_array($rcpts) ? $rcpts : array($rcpts), $this->contents['bcc']);
        }
        
        $oe = error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
        $ret = $mail->send($rcpts,$email['headers'],$email['body']);
        error_reporting($oe);
        if ($ret === true) { 
            $pg->addEvent("COREMAILER-SENT",  false,
                'To: ' .  ( is_array($rcpts) ? implode(', ', $rcpts) : $rcpts ) .
                'Subject: '  . @$email['headers']['Subject']
            ); 
        }  else {
            $pg->addEvent("COREMAILER-FAIL",  false, $ret->toString());
        }
        
        return $ret;
    }
    
    function htmlbodytoCID($html)
    {
        $dom = new DOMDocument();
        // this may raise parse errors as some html may be a component..
        @$dom->loadHTML('<?xml encoding="UTF-8">' .$html);
        $imgs= $dom->getElementsByTagName('img');
        
        foreach ($imgs as $i=>$img) {
            $url  = $img->getAttribute('src');
            if (preg_match('#^cid:#', $url)) {
                continue;
            }
            $me = $img->getAttribute('mailembed');
            if ($me == 'no') {
                continue;
            }
            
            $conv = $this->fetchImage($url);
            $this->images[$conv['contentid']] = $conv;
            
            $img->setAttribute('src', 'cid:' . $conv['contentid']);
            
            
        }
        return $dom->saveHTML();
        
        
        
    }
    function htmlbodyCssEmbed($html)
    {
        $ff = HTML_FlexyFramework::get();
        $dom = new DOMDocument();
        
        // this may raise parse errors as some html may be a component..
        @$dom->loadHTML('<?xml encoding="UTF-8">' .$html);
        $links = $dom->getElementsByTagName('link');
        $lc = array();
        foreach ($links as $link) {  // duplicate as links is dynamic and we change it..!
            $lc[] = $link;
        }
        //<link rel="stylesheet" type="text/css" href="{rootURL}/roojs1/css-mailer/mailer.css">
        
        foreach ($lc as $i=>$link) {
            //var_dump($link->getAttribute('href'));
            
            if ($link->getAttribute('rel') != 'stylesheet') {
                continue;
            }
            $url  = $link->getAttribute('href');
            $file = $ff->rootDir . $url;
            
            if (!preg_match('#^http://', $url)) {
                $file = $ff->rootDir . $url;

                if (!file_exists($file)) {
                    echo $file;
                    $link->setAttribute('href', 'missing:' . $file);
                    continue;
                }
            } else {
               $file = $url;  
            }
            
            $par = $link->parentNode;
            $par->removeChild($link);
            $s = $dom->createElement('style');
            $e = $dom->createTextNode(file_get_contents($file));
            $s->appendChild($e);
            $par->appendChild($s);
            
        }
        return $dom->saveHTML();
        
        
    }
    
    
    
    function fetchImage($url)
    {
        if($this->debug) {
            echo "FETCH : $url\n";
        }
        if ($url[0] == '/') {
            $ff = HTML_FlexyFramework::get();
            $file = $ff->rootDir . $url;
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
                'mimetype'  => $mt,
                'ext'       =>   $ext,
                'contentid' => md5($file),
                'file'      => $file
            );
            
        }
        
        // CACHE???
        // 2 files --- the info file.. and the actual file...
        // add user
        // unix only...
        $uinfo = posix_getpwuid( posix_getuid () ); 
        $user = $uinfo['name']; 
        
        $cache = ini_get('session.save_path')."/Pman_Core_Mailer-{$user}/" . md5($url);
        if (file_exists($cache) and filemtime($cache) > strtotime('NOW - 1 WEEK')) {
            $ret =  json_decode(file_get_contents($cache), true);
            $ret['file'] = $cache . '.data';
            return $ret;
        }
        if (!file_exists(dirname($cache))) {
            mkdir(dirname($cache),0700, true);
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
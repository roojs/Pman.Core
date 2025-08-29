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
 *  require_once 'Pman/Core/Mailer.php';
 *  $x= new  Pman_Core_Mailer(array(
       'page' => $this,
                // if bcc is property of this, then it will be used (BAD DESIGN)
       'rcpts' => array(),    
       'template' => 'your_template',
                // must be in templates/mail direcotry..
                // header and plaintext verison in mail/your_template.txt
                // if you want a html body - use  mail/your_template.body.html
       
        // 'bcc' => 'xyz@abc.com,abc@xyz.com',  // string...
        // 'contents'  => array(),              //  << keys must be trusted
                                            // if bcc is property of contents, then it will be used (BAD DESIGN)
           
        // 'html_locale => 'en',                // always use the 'english translated verison'
        // 'cache_images => true,               // -- defaults to caching images - set to false to disable.
        // 'replaceImages => false,             // should images be replaced.
        // 'urlmap => array(                    // map urls from template to a different location.
        //      'https://www.mysite.com/' => 'http://localhost/',
        // ),
        // 'locale' => 'en',                    // .... or zh_hk....
           
        // 'attachments' => array(
        //       array(
        //        'file' => '/path/to/file',    // file location
        //        name => 'myfile.pdf',         // (optional) - uses basename of file
        //        mimetype : 
        //      ), 
        //  
        // 'mail_method' =>  'SMTP',            // or SMTPMX
  
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
    var $debug          = 0;
    var $page           = false; /* usually a html_flexyframework_page */
    var $contents       = array(); /* object or array */
    var $template       = false; /* string */
    var $replaceImages  = false; /* boolean */
    var $rcpts   = false;
    var $templateDir = false;
    var $locale = false; // eg. 'en' or 'zh_HK'
    var $urlmap = array();
    
    var $htmlbody;
    var $textbody;
    
    var $html_locale = false; // eg. 'en' or 'zh_HK'
    var $images         = array(); // generated list of cid images for sending
    var $attachments = false;
    var $css_inline = false; // put the css into the html
    var $css_embed = false; // put the css tags into the body.
    
    var $mail_method = 'SMTP';
    
    var $cache_images = true;
      
    var $bcc = false;
    
    var $body_cls = false;
    
    function __construct($args) {
        foreach($args as $k=>$v) {
            // a bit trusting..
            $this->$k =  $v;
        }
        // allow core mailer debug setting.
        $ff = HTML_FlexyFramework::get();
        
        if (!empty($ff->Core_Mailer['debug'])) {
            $this->debug = $ff->Core_Mailer['debug'];
        }
        //$this->log("URL MAP");
        //$this->log($this->urlmap);
        
    }
     
    /**
     * ---------------- Global Tools ---------------
     *
     * applies this variables to a object
     * msgid
     * HTTP_HOIST
     * 
     */
    
    function toData()
    {
        $templateFile = $this->template;
        $args = (array)$this->contents;
        $content  = clone($this->page);
        
        foreach($args as $k=>$v) {
            $content->$k = $v;
        }
        
        $content->msgid = empty($content->msgid ) ? md5(time() . rand()) : $content->msgid ;
        
        // content can override this now
        $ff = HTML_FlexyFramework::get();
        $http_host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : 'pman.HTTP_HOST.not.set';
        if (isset($ff->Pman['HTTP_HOST']) && $http_host != 'localhost') {
            $http_host  = $ff->Pman['HTTP_HOST'];
        }
        if (empty($content->HTTP_HOST )) {
            $content->HTTP_HOST = $http_host;
        }
        
        // this should be done by having multiple template sources...!!!
        
        require_once 'HTML/Template/Flexy.php';
        
        $tmp_opts = array(
           // 'forceCompile' => true,
            'site_prefix' => false,
            'multiSource' => true,
        );
        
        $fopts = HTML_FlexyFramework::get()->HTML_Template_Flexy;
        
        //print_R($fopts);exit;
        if (!empty($fopts['DB_DataObject_translator'])) {
            $tmp_opts['DB_DataObject_translator'] = $fopts['DB_DataObject_translator'];
        }
        if (!empty($fopts['locale'])) {
            $tmp_opts['locale'] = $fopts['locale'];
        }
        if (!empty($fopts['templateDir'])) {
            $tmp_opts['templateDir'] = $fopts['templateDir'];
        }
        // override.
        if (!empty($this->templateDir)) {
            $tmp_opts['templateDir'] = $this->templateDir;
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
            
            if(!empty($content->body_cls) && strlen($content->body_cls)){
                $htmlbody = $this->htmlbodySetClass($htmlbody, $content->body_cls);
            }
            
            if ($this->replaceImages) {
                $htmlbody = $this->htmlbodytoCID($htmlbody);    
            }
            
            if ($this->css_embed) {
                $htmlbody = $this->htmlbodyCssEmbed($htmlbody);
            }
            
            if ($this->css_inline && strlen($this->css_inline)) {
                $htmlbody = $this->htmlbodyInlineCss($htmlbody);
            }
            
        }
        $tmp_opts['nonHTML'] = true;
        //$tmp_opts['debug'] = true;
        
        // print_R($tmp_opts);
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
            $this->textbody = $parts[2];
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
                    $this->textbody = 'This message is in HTML only';
                }else{
                    $mime->setTXTBody($parts[2]);
                    $this->textbody = $parts[2];
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
         
        
        $ret = array(
            'recipents' => $parts[0],
            'headers' => $parts[1],
            'body' => $parts[2],
            'mailer' => $this
        );
        if ($this->rcpts !== false) {
            $ret['recipents'] =  $this->rcpts;
        }
        // if 'to' is empty, then add the recipents in there... (must be an array?
        if (!empty($ret['recipents']) && is_array($ret['recipents']) &&
                (empty($ret['headers']['To']) || !strlen(trim($ret['headers']['To'])))) {
            $ret['headers']['To'] = implode(',', $ret['recipents']);
        }
       
        
        // add bcc if necessary..
        if (!empty($this->bcc)) {
           $ret['bcc'] = $this->bcc;
        }
        return $ret;
    }
    function send($email = false)
    {
			
        $ff = HTML_FlexyFramework::get();
        
        $pg = $ff->page;
        
        $email = is_array($email)  ? $email : $this->toData();
        
        if (is_a($email, 'PEAR_Error')) {
            $pg->addEvent("COREMAILER-FAIL",  false, "email toData failed"); 
      
            
            return $email;
        }
        
        //$this->log( htmlspecialchars(print_r($email,true)));
        
        ///$recipents = array($this->email);
//        $mailOptions = PEAR::getStaticProperty('Mail','options');
        
        $mailOptions = isset($ff->Mail) ? $ff->Mail : array();
        //print_R($mailOptions);exit;
        
        if ($this->mail_method == 'SMTPMX' && empty($mailOptions['mailname'])) {
            $pg->jerr("Mail[mailname] is not set - this is required for SMTPMX");
            
        }
        
        $mail = Mail::factory($this->mail_method,$mailOptions);
        
         if ($this->debug) {
            $mail->debug = (bool) $this->debug;
        }
        
        $email['headers']['Date'] = date('r'); 
        if (PEAR::isError($mail)) {
            $pg->addEvent("COREMAILER-FAIL",  false, "mail factory failed"); 
      
            
            return $mail;
        } 
        $rcpts = $this->rcpts == false ? $email['recipents'] : $this->rcpts;
        
        
        
        // this makes contents untrustable...
        if (!empty($this->contents['bcc']) && is_array($this->contents['bcc'])) {
            $rcpts =array_merge(is_array($rcpts) ? $rcpts : array($rcpts), $this->contents['bcc']);
        }
        
        $oe = error_reporting(E_ALL & ~E_NOTICE);
        if ($this->debug) {
            print_r(array(
                'rcpts' => $rcpts,
                'email' => $email
            ));
        }
        $ret = $mail->send($rcpts,$email['headers'],$email['body']);
        error_reporting($oe);
        if ($ret === true) { 
            $pg->addEvent("COREMAILER-SENT",  false,
                'To: ' .  ( is_array($rcpts) ? implode(', ', $rcpts) : $rcpts ) .
                'Subject: '  . @$email['headers']['Subject']
            ); 
        }  else {
            $pg->addEvent("COREMAILER-FAIL",  false,
                "Sending to : " . ( is_array($rcpts) ? implode(', ', $rcpts) : $rcpts ) .
                " Error: " . $ret->toString());

        }
        
        return $ret;
    }
    
    function htmlbodytoCID($html)
    {
        $dom = new DOMDocument();
        // this may raise parse errors as some html may be a component..
        @$dom->loadHTML('<?xml encoding="UTF-8">' .$html);
        $imgs= $dom->getElementsByTagName('img');
        
        $urls = array();
        
        foreach ($imgs as $i=>$img) {
            $url  = $img->getAttribute('src');
            if (preg_match('#^cid:#', $url)) {
                continue;
            }
            $me = $img->getAttribute('mailembed');
            if ($me == 'no') {
                continue;
            }
            
            if(!array_key_exists($url, $urls)){
                $conv = $this->fetchImage($url);
                $urls[$url] = $conv;
                $this->images[$conv['contentid']] = $conv;
            } else {
                $conv = $urls[$url];
            }
            $img->setAttribute('origsrc', $url);
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
            
            if (!preg_match('#^(http|https)://#', $url)) {
                $file = $ff->rootDir . $url;

                if (!file_exists($file)) {
//                    echo $file;
                    $link->setAttribute('href', 'missing:' . $file);
                    continue;
                }
            } else {
               $file = $this->mapurl($url);  
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
    
    function htmlbodyInlineCss($html)
    {   
        $dom = new DOMDocument();
        
        @$dom->loadHTML('<?xml encoding="UTF-8">' .$html);
        
        $html = $dom->getElementsByTagName('html');
        $head = $dom->getElementsByTagName('head');
        $body = $dom->getElementsByTagName('body');
        
        if(!$head->length){
            $head = $dom->createElement('head');
            $html->item(0)->insertBefore($head, $body->item(0));
            $head = $dom->getElementsByTagName('head');
        }
        
        $s = $dom->createElement('style');
        $e = $dom->createTextNode($this->css_inline);
        $s->appendChild($e);
        $head->item(0)->appendChild($s);
        
        return $dom->saveHTML();
        
        /* Inline
        require_once 'HTML/CSS/InlineStyle.php';
        
        $doc = new HTML_CSS_InlineStyle($html);
        
        $doc->applyStylesheet($this->css_inline);
        
        $html = $doc->getHTML();
        
        return $html;
        */
    }
    
    function htmlbodySetClass($html, $cls)
    {
        $dom = new DOMDocument();
        
        @$dom->loadHTML('<?xml encoding="UTF-8">' .$html);
        
        $body = $dom->getElementsByTagName('body');
        if (!empty($body->length)) {
            $body->item(0)->setAttribute('class', $cls);
        } else {
            $body = $dom->createElement("body");
            $body->setAttribute('class', $cls);
            $dom->appendChild($body);
        }
        
        
        return $dom->saveHTML();
    }
    
    function fetchImage($url)
    {
        
        
        $this->log( "FETCH : $url\n");
        
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
                   'contentid' => md5($file),  // mailer makes md5 cid's' -- cid with attachment-** are done by mailer.
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
        if ($this->cache_images &&
                file_exists($cache) &&
                filemtime($cache) > strtotime('NOW - 1 WEEK')
            ) {
            $ret =  json_decode(file_get_contents($cache), true);
            $this->log("fetched from cache");
            $ret['file'] = $cache . '.data';
            return $ret;
        }
        if (!file_exists(dirname($cache))) {
            mkdir(dirname($cache),0700, true);
        }
        
        require_once 'HTTP/Request.php';
        
        $real_url = str_replace(' ', '%20', $this->mapurl($url));
        $a = new HTTP_Request($real_url);
        $a->sendRequest();
        $data = $a->getResponseBody();
        
        $this->log("got file of size " . strlen($data));
        $this->log("save contentid " . md5($url));
        
        file_put_contents($cache .'.data', $data);
        
        
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
    
    function mapurl($in)
    {
         foreach($this->urlmap as $o=>$n) {
            if (strpos($in,$o) === 0) {
                $ret =$n . substr($in,strlen($o));
                $this->log("mapURL in $in = $ret");
                return $ret;
            }
        }
        $this->log("mapurl no change - $in");
        return $in;
         
        
    }
 
    
    
    function log($val)
    {
        if (!$this->debug) {
            return;
        }
        if ($this->debug < 2) {
            echo '<PRE>' . print_r($val,true). "\n"; 
            return;
        }
        $fh = fopen('/tmp/core_mailer.log', 'a');
        fwrite($fh, date('Y-m-d H:i:s -') . json_encode($val) . "\n");
        fclose($fh);
        
        
    }
    
}
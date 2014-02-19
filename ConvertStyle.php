<?php

require_once 'Pman.php';
require_once 'HTML/CSS/InlineStyle.php';

class Pman_Core_ConvertStyle extends Pman 
{
    function getAuth()
    {
        if (HTML_FlexyFramework::get()->cli) {
            return true;
        }
        $this->authUser = $this->getAuthUser();
        if (!$this->authUser) {
            return false;
        }
        return true;
    }
    
    function relPath($base, $url)
    {   
        //var_dump(array($base,$url));        
        if (preg_match('/^(http|https|mailto):/',$url)) {
            return $url;
        }
        $ui = parse_url($base);
        // if it starts with '/'...
        // we do not handle ports...
        if (substr($url,0,2) == '//') {
            return $ui['scheme'] .':' .  $url;
        }
        
        
        
        if (substr($url,0,1) == '/') {
            return $ui['scheme'] .'://'.$ui['host']. $url;
        }
        
        if (substr($ui['path'], -1) == '/') {
           return $ui['scheme'] .'://'.$ui['host']. $ui['path'] . $url;
        }
        if (!strlen($ui['path'])) {
            return $ui['scheme'] .'://'.$ui['host']. '/' . $url;
           
        }
        /// not sure if this will work...
        return $ui['scheme'] .'://'.$ui['host']. $ui['path'] . '/../'. $url;
        
    }
    
    
    function post()
    {
        // Import from URL
        if(isset($_REQUEST['importUrl']))
        {
            $this->checkHeader($_REQUEST['importUrl']);
            $data = $this->convertStyle($_REQUEST['importUrl'], '');
            $this->jok($data);
            
        }
     
        // Import from file
        $htmlFile = DB_DataObject::factory('images');
        $htmlFile->setFrom(array(
               'onid' => 0,
               'ontable' =>'crm_mailing_list_message'
        ));
        $htmlFile->onUpload(false);
       // print_r($htmlFile);
        if($htmlFile->mimetype != 'text/html')
        {
            $this->jerr('accept html file only!');
        }
        if(!file_exists($htmlFile->getStoreName()))
        {
            $this->jerr('update failed!');
        }
        
        $data = $this->convertStyle('', $htmlFile->getStoreName());
        
        $htmlFile->delete();
        unlink($htmlFile->getStoreName()) or die('Unable to delete the file');
        
        $this->jok($data);
    }
    
    function checkHeader($url)
    {
        if(strpos($url, 'https') !== false)
        {
            $this->jerr('accept HTTP url only!');
        }
        $headers = get_headers($url, 1);
        if(strpos(is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type'], 'text/html') === false)
        {
            $this->jerr('accept html file only!');
        }
        return;
    }
    
    var $styleSheets = array();
    
    function convertStyle($url, $file)
    {
        if(!empty($url))
        {
            $host = parse_url($url);
            require_once 'System.php';
            $wget = System::which('wget');
            if (!$wget) {
                $this->jerr("no wget");
            }
            $cmd =  $wget . ' -q -O -  ' . escapeshellarg($url);
            
            //echo $cmd; exit;
            $data = `$cmd`;
            
            if (!trim(strlen($data))) {
                $this->jerr("url returned an empty string");
            }
           // $this->jerr($url);
            /*require_once 'HTTP/Request.php';
            $a = new HTTP_Request($url, array(
                    'allowRedirects' => true,
                    'maxRedirects' => 2, 
                    'userAgent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.4 (KHTML, like Gecko) Chrome/22.0.1229.94 Safari/537.4',
                    ));
            $a->sendRequest();
            // if this results in an errorr or redirect..
            // we should log that somewhere.. and display it on the feed...
            
            $data =  $a->getResponseBody();
            */
            
            //$this->jerr($data);
            
        //    $data = file_get_contents($url);
        }
        if(file_exists($file))
        {
            $data = file_get_contents($file);
        }
        
        libxml_use_internal_errors (true);
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML('<?xml encoding="UTF-8">'.$data);
        $doc->formatOutput = true;

      
        
        $xpath = new DOMXpath($doc);
        foreach ($xpath->query('//img[@src]') as $img) {
            $href = $img->getAttribute('src');
            if (!preg_match("/^http(.*)$/", $href, $matches)) {
                if(!empty($url)){
                    $img->setAttribute('src',  $this->relPath($url,  $href));
                    continue;
                }
                $this->jerr('Please use the absolutely url for image src!');
            }
        }
        
        
        foreach ($xpath->query('//a[@href]') as $a) {
            $href = $a->getAttribute('href');
            if (!preg_match("/^http|mailto|#(.*)$/", $href, $matches)) {
                if(!empty($url)){
                    $a->setAttribute('href', $this->relPath($url,  $href));
                    continue;
                }
                $this->jerr('Please use the absolutely url for a href!');
            }
        }
        
        foreach ($xpath->query('//link[@href]') as $l) {
            if($l->getAttribute('rel') == 'stylesheet'){
                $href = $l->getAttribute('href');
                
                
                if (empty($url) && !preg_match("/^http(.*)$/", $href, $matches)) {
                    // import from file , must use absolutely url
                    $this->jerr('Please use the absolutely url for link href!');
                }
                if (!empty($url)) {
                    // import from URL
                    $href = $this->relPath($url,  $href);
                }
                $this->styleSheets[$href] = $this->replaceImageUrl(file_get_contents($href),$href);
            }
        }
        $data = $doc->saveHTML();
        
        $htmldoc = new HTML_CSS_InlineStyle($data);
        if(count($this->styleSheets) > 0){
            foreach ($this->styleSheets as $styleSheet){
                $htmldoc->applyStylesheet($styleSheet);
            }
        }
        $html = $htmldoc->getHTML();
        libxml_use_internal_errors (false);
        
        if (!function_exists('tidy_repair_string')) {
            return "INSTALL TIDY ON SERVER " . $html;
        }
        
        // finally clean it up... using tidy...
       
 
        $html = tidy_repair_string(
                $html,
                array(
                  'indent' => TRUE,
                    'output-xhtml' => TRUE,
                    'wrap' => 120
                ),
                'UTF8'
        );
        
        
        return $html;
        
    }
    
    function replaceImageUrl($stylesheet,$href)
    {
        $base = explode("/", $href);
        $s = preg_split('/url\(([\'\"]?)/', $stylesheet);
        foreach($s as $k => $v){
            if($k == 0){
                continue;
            }
            array_pop($base);
            array_push($base, $v);
            $s[$k] = implode("/", $base);
        }
        
        $r = implode("url(", $s);
        
        $this->checkImportCss($r);
        
        return $r;
    }
    
    function checkImportCss($r)
    {
        if(preg_match("/@import url/", $r, $matches)){
            $importCss = explode("@import url", $r);
            foreach ($importCss as $css){
                if(preg_match("/\.css/", $css, $matches)){
                    $cssFileName = explode(".css", $css);
                    $name = preg_replace("/[\(\'\"]/", '', $cssFileName[0]);
                    $p = $name . '.css';
                    $this->styleSheets[$p] = $this->replaceImageUrl(file_get_contents($p),$p);
                }
            }
        }
        return;
    }
    
}
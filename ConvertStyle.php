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
        
        if (preg_match('/^(http|https|mailto):/',$url)) {
            return $url;
        }
        
        $ui = parse_url($base);
        
        if($ui['host'] == 'localhost'){
            return $ui['scheme'] .'://'.$ui['host']. $ui['path'] . '/'. $url;
        }
        
        if (substr($url,0,2) == '//') {
            return $ui['scheme'] .':' .  $url;
        }
        
        if (substr($url,0,1) == '/') {
            return $ui['scheme'] .'://'.$ui['host']. $url;
        }
        
        if (!empty($ui['path']) && substr($ui['path'], -1) == '/') {
           return $ui['scheme'] .'://'.$ui['host']. $ui['path'] . $url;
        }
        if (empty($ui['path'])  || !strlen($ui['path'])) {
            return $ui['scheme'] .'://'.$ui['host']. '/' . $url;
           
        }
        
        return $ui['scheme'] .'://'.$ui['host']. $ui['path'] . '/../'. $url;
        
    }
    
    function checkHeader($url)
    {
        // if(strpos($url, 'https') !== false)
        // {
        //     $this->jerr('accept HTTP url only!');
        // }
        $headers = get_headers($url, 1);
        if(strpos(is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type'], 'text/html') === false)
        {
            $this->jerr('accept html file only!');
        }
        return;
    }
    
    var $styleSheets = array();
    
    function convertStyle($url, $file, $is_url = true)
    {
        $inLineCss = true;
        
        if($is_url && !empty($url))
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
        }
        
        if(!$is_url){
            $data = file_get_contents($file);
        }
        
        if(preg_match('/^\s*<!--\s*NOT CONVERT STYLE\s*-->\s*/', $data)){
            $inLineCss = false;
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
                
                if(!preg_match("/^http(.*)$/", $href, $matches)){
                    if(empty($url)){
                        $this->jerr('Please use the absolutely url for link href!');
                    }
                    $href = $this->relPath($url,  $href);
                }
                
                $this->styleSheets[$href] = $this->replaceImageUrl(file_get_contents($href),$href);
            }
        }
        
        foreach ($xpath->query('//style') as $s){
            $this->styleSheets[] = $this->replaceImageUrl($s->nodeValue, $url);
        }
        
        $data = $doc->saveHTML();
        
        if(!$inLineCss){
            return $data;
        }
        
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
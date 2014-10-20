<?php

require_once 'Pman.php';

class Pman_Core_UpdateCurrencyRate extends Pman
{
    
    static $cli_desc = "Update Currency Exchange Rate";
    
    static $cli_opts = array();
    
    var $cli = false; 
    
    var $actionUrl = 'http://www.oanda.com/currency/historical-rates-classic';
    
    var $mapping = array(
        'CNY' => 'RMB'
    );
    
    function getAuth() 
    {
        $ff = HTML_FlexyFramework::get();
        if (!empty($ff->cli)) {
            $this->cli = true;
            return true;
        }
        
        die("NOT ALLOWED");
    }
    
    function get()
    {
        echo "obtain the currency lists... \n";
        
        $currency = array();
        
        $response = $this->curl($this->actionUrl, array(), 'GET');
        
        libxml_use_internal_errors (true);
        
        $doc = new DOMDocument();
        $doc->loadHTML($response);
        
        libxml_use_internal_errors (false);
        
        $xpath = new DOMXpath($doc);
        
        $elements = $xpath->query("//select[@name='exch']/option");
        
        foreach($elements as $el) {
            $currency[] = $el->getAttribute('value');
        }
        
        if(empty($currency)){
            die('no any currency');
        }
        
        foreach ($currency as $c){
            $params = array(
                'lang' => 'en',
                'result' => 1,
                'date1' => date('m/d/y', strtotime("-6 MONTH")),
                'date'=> date('m/d/y'),
                'date_fmt' => 'us',
                'exch' => $c,
                'expr' => 'USD',
                'margin_fixed' => 0,
                'format'=> 'HTML'
            );
        }
        
        $response = $this->curl($this->actionUrl, $params, 'POST');
        
        libxml_use_internal_errors (true);
        
        $doc = new DOMDocument();
        $doc->loadHTML($response);
        
        libxml_use_internal_errors (false);
        
        $xpath = new DOMXpath($doc);
        
        $elements = $xpath->query("//td[@id='content_section']/table/tr[last()]");
        
        print_r($elements);exit;
        
    }
    
    function curl($url, $request = array(), $method = 'GET') 
    {
         
        if(is_array($request)){
            $request = http_build_query($request);
        }
        
        $url = $url . ($method == 'GET' ? "?" . $request : '');  
        $ch = curl_init($url);
        
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array("Content-Type: application/x-www-form-urlencoded", "Content-Length: " . strlen($request)));
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        curl_close($ch);
        
        return $response;
    }
    
    /*
    lang:en
    result:1
    date1:10/14/14
    date:10/20/14
    date_fmt:us
    exch:USD
    exch2:
    expr:EUR
    expr2:
    margin_fixed:0
    format:HTML
    SUBMIT:Get Table
    */
}
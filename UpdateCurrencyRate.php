<?php

// note this no longer works -- we could try using their JSON feed?


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
    
    function get($args, $opts=array())
    {   
        
        //DB_DataObject::DebugLevel(1);
        $d = DB_DataObject::Factory('core_curr_rate');
        $d->loadRates();
        
        $hkd = $d->convert(1,'USD','HKD');
        
        echo "1 USD is ~ $hkd HKD (should be about 7.75)\n ";
        
        die("done");
        
         
        
    }
    function oldversion() 
    {
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
        
        $fromDate = date('m/d/y', strtotime("-6 MONTH"));
        $toDate = date('m/d/y');
        
        $total = count($currency);
        
        foreach ($currency as $k => $c){
            
            echo "\nProcessing Currency : $c ($k / $total) \n";
            
            $params = array(
                'lang'          => 'en',
                'result'        => 1,
                'date1'         => $fromDate,
                'date'          => $toDate,
                'date_fmt'      => 'us',
                'exch'          => $c,
                'expr'          => 'USD',
                'margin_fixed'  => 0,
                'format'        => 'HTML'
            );
            
            $response = $this->curl($this->actionUrl, $params, 'POST');
        
            libxml_use_internal_errors (true);

            $doc = new DOMDocument();
            $doc->loadHTML($response);

            libxml_use_internal_errors (false);

            $xpath = new DOMXpath($doc);

            $elements = $xpath->query("//td[@id='content_section']/table/tr[last()]/td/table/tr[1]/td[last()]");

            $rate = empty($elements->item(0)->nodeValue) ? 0 : $elements->item(0)->nodeValue * 1;

            $this->processCurrRate($c, $rate, $fromDate, $toDate);
            
            if(array_key_exists($c, $this->mapping)){
                $this->processCurrRate($this->mapping[$c], $rate, $fromDate, $toDate);
            }
        }
        
        return;
    }
    
    function processCurrRate($currency, $rate, $from, $to)
    {
        $curr = DB_DataObject::factory('core_curr_rate');
            
        $curr->curr = $currency;

        $o = false;

        if($curr->find(true)){
            $o = clone($curr);
        }

        $curr->setFrom(array(
            'rate'  => $rate,
            'from_dt'  => date('Y-m-d H:i:s', strtotime($from)),
            'to_dt'    => date('Y-m-d H:i:s', strtotime($to))
        ));

        (empty($o)) ? $curr->insert() : $curr->update($o);
        
        return;
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
    
}
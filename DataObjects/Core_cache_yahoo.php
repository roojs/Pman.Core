<?php
/**
 * Table Definition for yahoo queries cache
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_Cache_Yahoo extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_cache_yahoo';                       // table name
    public $id;
    public $query;
    public $result;
    
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    /*
    https://query1.finance.yahoo.com/v1/finance/search?
        q=hsbc&quotesCount=6&
        newsCount=5&
        quotesQueryId=tss_match_phrase_query&
        multiQuoteQueryId=multi_quote_single_token_query&
        newsQueryId=news_ss_symbols&
        enableCb=true
    */
    function checkYahoo($str, $userAgent) 
    {
        // parameter q is requried
        if(empty($str)) {
            // no request sent
            return array(
                'code' => 400,
                'response' => '{"finance":{"result":null,"error":{"code":"Bad Request","description":"Missing required query parameter=q"}}}'
            );
        }
       
        $ccy = DB_DataObject::factory('core_cache_yahoo');
        if($ccy->get('query', $str)) {
            return array(
                'code' => 200,
                'response' => $ccy->result,
            );
        }
        $request = http_build_query(array(
            'q'=>$str,
            'quotesCount'=>12,
            'newsCount'=>0,
            'quotesQueryId'=>'tss_match_phrase_query',
            'multiQuoteQueryId'=>'multi_quote_single_token_query',
            'newsQueryId'=>'news_ss_symbols'
        ));
        

        $url =     'https://query1.finance.yahoo.com/v1/finance/search?' . $request;
 
        $ch = curl_init($url);

        // $userAgent = "Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Chromium/122.0.6264.92 Safari/537.36"; // OK
        // $userAgent = "Mozilla/5.0 (X11; Arch Linux; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Brave/1.68.118 Chrome/122.0.6261.112 Safari/537.36";
        // $userAgent = "Mozilla/5.0 (X11; Fedora; Linux x86_64) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.3 Safari/605.1.15";
        // $userAgent = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.6264.111 Safari/537.36";
        // $userAgent = "Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Chromium/122.0.6264.92 Safari/537.36"; // OK
        $userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.6367.45 Safari/537.36";
        $header = array(
            // this results in 429 for some reason
            // "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36"
            "User-Agent: " . (!empty($userAgent) ? $userAgent : "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.51 Safari/537.36")
        );
        
        if(!empty($header)){
          curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
        
        $response = curl_exec($ch);
        
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        
        $header_res = substr($response, 0, $header_size);
        $body_res = substr($response, $header_size);
        
        $http_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        
        curl_close($ch);

        // cache the result on success
        if($http_code == 200) {
            $ccy = DB_DataObject::factory('core_cache_yahoo');
            $ccy->setFrom(array(
                'query' => $request,
                'result' => $body_res
            ));
            // $ccy->insert();
        }
        if ($http_code == 429) {
                // log errors...
            ini_set('display_errors', '0');
            trigger_error("URL: {$url}\n {$response}");
            ini_set('display_errors', '1');
        }
        
        return array(
            'code' => $http_code,
            'header' => $header_res,
            'response' => $body_res,
            'verbose' => $verbose
        );
    }
}
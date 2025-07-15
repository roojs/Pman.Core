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
    function checkYahoo($str) 
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
        $request = http_build_query(array(
            'q'=>$str,
            'quotesCount'=>12,
            'newsCount'=>0,
            'quotesQueryId'=>'tss_match_phrase_query',
            'multiQuoteQueryId'=>'multi_quote_single_token_query',
            'newsQueryId'=>'news_ss_symbols'
        ));

        if($ccy->get('query', $request)) {
            return array(
                'code' => 200,
                'response' => $ccy->result,
            );
        }
        

        $url =     'https://query1.finance.yahoo.com/v1/finance/search?' . $request;
 
        $ch = curl_init($url);

        $userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.51 Safari/537.36";
        if(!empty($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];

            // Chrome in Linux / Mac will fail

            // always change the os to Windows
            $userAgent = preg_replace(
                '/\(.*?\)/', 
                '(Windows NT 10.0; Win64; x64)', 
                $userAgent, 
                1
            );

            // Safari in Windows will fail

            // change the browser to Chrome if the it is Safari
            if (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
                // Replace Version/X.X and Safari/X.X.X with Chrome version
                $userAgent = preg_replace('/Version\/[\d\.]+/', 'Chrome/125.0.6422.28', $userAgent);
                $userAgent = preg_replace('/Safari\/[\d\.]+/', 'Safari/537.36', $userAgent);
            }
        }

        $header = array(
            "User-Agent: {$userAgent}"
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
            $ccy->insert();
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
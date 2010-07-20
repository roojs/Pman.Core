<?php
/**
 * 
 * Either we load this as a standard array at start??
 * eg. after login...
 * 
 * We basically load up all supported languages at the start of the application.
 * 
 * By default it returns
 * 
 * Pman.I18n.Data = {
      
      en : {
          l :  [ { code : 'en', title : 'English' }, .... ],
          c :  [ { code : 'UK', title : 'United Kingdom' }, .... ],
          m :  [ { code : 'USD', title : 'US Dollars' }, .... ],
      fr : ....
   }
 * 
 * 
 * other usage:
 * 
 * index.php/Pman/I18N/BuildDB -- buildes the database..
 * .. other formats are depreciated, but are supported by the old code....
 * 
 * 
 * Database language translation should be done using the database table.
 * So sorting can be done correctly..
 * 
 * Configuration in index.php..
 * 
 *  'Pman_Core_I18N' => array(
      'l' => array(
            'en', 'zh_CN',   'zh_HK',  'zh_TW', 'th', 'ko', 'ja', 'ms', 
            'id', // indonesian
            'tl', // tagalog
            'vi', //vietnamise
            'hi', // hindi
            'ta', // tamil
            '**', // other
        ), 
       'c' => '*', // eg. all languages..
       'm' => array( 'USD', 'HKD', 'GBP', 'CNY', 'SGD', 'JPY' )
    ), 

 * 
 */
require_once 'Pman.php';

class Pman_Core_i18N extends Pman
{
 
    
    // these are the default languages we support.
    // they will allways be overlaid with the current configuration (via getAuth)
    var $cfg = array(
        // translated versions availalable
        't' => array(
            'en', 'zh_CN',   'zh_HK', 
        ),
        // languages available
        'l' => array(
            'en', 'zh_CN',   'zh_HK',  'zh_TW', 'th', 'ko', 'ja', 'ms', 
            'id', // indonesian
            'tl', // tagalog
            'vi', //vietnamise
            'hi', // hindi
            'ta', // tamil
            '**', // other
        ),
        'c' => array(
             'AU', 'CN', 'HK', 'IN', 'ID', 'JP', 'MY', 'NZ', 'TW', 'SG', 'TH', 'KR', 'US', 'PH', 'VN','**'
        ),
        'm' => array(
            'USD', 'HKD', 'GBP', 'CNY', 'SGD', 'JPY'
        )
    );
    
    
     
    
    
    function getAuth()
    {
        parent::getAuth(); // load company!
        //return true;
        $au = $this->getAuthUser();
        //if (!$au) {
        //    $this->jerr("Not authenticated", array('authFailure' => true));
        //}
        $this->authUser = $au;
        
        $opts = PEAR::getStaticProperty('Pman_Core_I18N', 'options');
        if (empty($opts)) {
            $opts = PEAR::getStaticProperty('Pman_I18N', 'options');
        }
        $opts = empty($opts)  ?  array() : $opts;
        
        // load the cofiguration
        foreach($opts as $k=>$v) {
            
            if ($v == '*') {
                $this->cfg[$k] = $this->getDefaultCfg($k);
                continue;
            }
            $this->cfg[$k] = is_array($v) ? $v  : explode(',', $v);
        }
        
        
        
        
        return true;
    }
    // returns a list of all countries..
    function getDefaultCfg($t) {
        $ret = array();
        switch ($t) {
            case 'c':
                require_once 'I18Nv2/Country.php';
                
                $c = new I18Nv2_Country('en');
                $ret =  array_keys($c->codes);
                $ret[] = '**';
                break;
            case 'l':
                require_once 'I18Nv2/Language.php';
                $c = new I18Nv2_Language('en');
                $ret =  array_keys($c->codes);
                $ret[] = '**';
                break;
            case 'm':
                require_once 'I18Nv2/Currency.php';
                $c = new I18Nv2_Currency('en');
                $ret =  array_keys($c->codes);
                $ret[] = '**';
                break;
        }
        foreach ($ret as $k=>$v) {
            $ret[$k] = strtoupper($v);
        }
        
        
        return $ret;
    }
    
     
    function get($s ='')
    {
        
        
        switch ($s)
        {
            
            case 'BuildDB':
            // by admin only?!?
                //DB_DataObject::debugLevel(1);
                $this->buildDb('l');
                $this->buildDb('c');
                $this->buildDb('m');
                die("DONE!");
                break;
                  
            default: 
                $this->outputJavascript();
                // output javascript..
                $this->jerr("ERROR");
        }
         
        $this->jdata($ret);
        exit;
        
    }
    
    function outputJavascript()
    {
        
        require_once 'I18Nv2/Country.php';
        require_once 'I18Nv2/Language.php';
        require_once 'I18Nv2/Currency.php';
        
        $langs = $this->cfg['t'];
        var_dump($langs);exit;
        $ar = array();
        foreach($langs as $lang)
        {
            $lang = array_shift(explode('_', strtoupper($lang)));
            
            $ar[$lang] = array(
                'l' => $this->objToList(new I18Nv2_Language($lang, 'UTF-8')),
                'c' => $this->objToList(new I18Nv2_Country($lang, 'UTF-8')),
                'm' => $this->objToList(new I18Nv2_Currency($lang, 'UTF-8'))
            );
        }
        header('Content-type: text/javascript');
        echo 'Pman.I18n.Data = ' .  json_encode($ar);
        exit;
        
        
        
    }
    function objToList($obj) {
        $ret = array();
        foreach($obj->codes as $k=>$v) {
            
            $ret[] = array(
                'code'=> $k , 
                'title' => $v
            );
        }
        return $ret;
    }
    
     /**
     * translate (used by database building);
     * usage :
     * require_once 'Pman/Core/I18N.php';
     * $x = new Pman_Core_I18N();
     * $x->translate($this->authuser, 'c', 'US');
     * @param au - auth User
     * @param type = 'c' or 'l'
     * @param k - key to translate
     * 
     */
     
    function translate($au, $type, $k) 
    {
      
        static $cache;
        if (empty($k)) {
            return '??';
        }
        $lang = !$au || empty($au->lang ) ? 'en' : is_string($au) ? $au : $au->lang;
        $lbits = explode('_', strtoupper($lang));
        $lang = $lbits[0];
        
        if (!isset($cache[$lang])) {
            require_once 'I18Nv2/Country.php';
            require_once 'I18Nv2/Language.php';
            require_once 'I18Nv2/Currency.php';
            $cache[$lang] = array(
                'l' =>  new I18Nv2_Language($lang, 'UTF-8'),
                'c' => new I18Nv2_Country($lang, 'UTF-8'),
                'm' => new I18Nv2_Currency($lang, 'UTF-8')
            );
            //echo '<PRE>';print_r(array($lang, $cache[$lang]['c']));
        }
        if ($k == '**') {
            return 'Other / Unknown';
        }
    
        
        if ($type == 'l') {
            $tolang = explode('_', $k);
         
            $ret = $cache[$lang][$type]->getName($tolang[0]);
            if (count($tolang) > 1) {
                $ret.= '('.$tolang[1].')'; 
            }
            return $ret;
        }
        $ret = $cache[$lang][$type]->getName($k);
        //print_r(array($k, $ret));
        return $ret;
        
        
    }
    
    
    
    function buildDB($ltype= false, $inlang= false )
    {
        if ($ltype === false) {
            
            die("OOPS NO LTYPE");
        }
        if ($inlang == '**') {
            return; // dont bother building generic..
        }
        if ($inlang === false) {
            foreach( $this->cfg['t'] as $l) {
                $this->buildDB($ltype, $l);
            }
            return;
        }
        
        $list =  $this->getDefaultCfg($ltype);
        
        DB_DataObject::debugLevel(1);
        
        foreach($list as $lkey) {
            $x = DB_DataObject::factory('i18n');
            $x->ltype = $ltype;
            $x->lkey = $lkey;
            $x->inlang= $inlang;
            if ($x->find(true)) {
                $xx= clone($x);
                $x->lval = $this->translate($inlang, $ltype, $lkey);
                $x->update($xx);
                continue;
            }
            $x->lval = $this->translate($inlang, $ltype, $lkey);
            $x->insert();
            
        }
        
        
        
        
    }
    
}
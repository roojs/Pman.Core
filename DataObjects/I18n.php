<?php
/**
 * Table Definition for i18n
 *
 * This is heavily related to the Pman_I18n implementation..
 *
 * It should eventually replace most of that..
 * 
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_I18n extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'i18n';                            // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $ltype;                           // string(1)  not_null multiple_key
    public $lkey;                            // string(8)  not_null
    public $inlang;                          // string(8)  not_null
    public $lval;                            // string(64)  not_null

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    // we have a small default set of languages available..
    // you can modify this by making this setting in the index.php loader.
    // Pman_Core_i18n = array( 'c' => *, 'l' => '*', 'm' => '*')
    
    static $cfg = array(
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
    
    
      // the default configuration.
    
    function applyFilters($q, $au)
    {
        
        //DB_DataObject::debugLevel(1);
        if (!empty($q['query']['_with_en'])) {
            $this->selectAdd("
                i18n_translate(ltype, lkey, 'en') as lval_en
                
            ");
        }
    }
    
    
    
    
    
    
    
    
    
    
    // -------------- code to handle importing into database..
    
    
    
    
    // returns a list of all countries/languages etc.. (with '*')
    function availableCodes($t)
    {
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
    
    
    function buildDB($ltype= false, $inlang= false )
    {
        if ($ltype === false) {
            
            $this->buildDB('c');
            $this->buildDB('l');
            $this->buildDB('m');
            
            
            die("OOPS NO LTYPE");
        }
        if ($inlang == '**') {
            return; // dont bother building generic..
        }
        
        
        if ($inlang === false) {
            // do we want to add our 'configured ones..'
            foreach( $this->availableCodes('l') as $l) {
                
             // foreach( $this->cfg['t'] as $l) {
                $this->buildDB($ltype, $l);
            }
            return;
        }
        
        $list =  $this->availableCodes($ltype);
        
        //DB_DataObject::debugLevel(1);
        
        foreach($list as $lkey) {
            $x = DB_DataObject::factory('i18n');
            $x->ltype = $ltype;
            $x->lkey = $lkey;
            $x->inlang= $inlang;
            if ($x->find(true)) {
                $xx= clone($x);
                $x->lval = $this->defaultTranslate($inlang, $ltype, $lkey);
                $x->update($xx);
                continue;
            }
            $x->lval = $this->defaultTranslate($inlang, $ltype, $lkey);
            $x->insert();
            
        }
         
        
    }
    
    /**
     * default translate  - use i18n classes to provide a value.
     *
     * 
     */
     
    function defaultTranslate($lang, $type, $k) 
    {
      
        static $cache;
        
        if (empty($k)) {
            return '??';
        }

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
    
    
}

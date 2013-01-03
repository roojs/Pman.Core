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
            
            'en', 'zh_CN',   'zh_HK',  'zh_TW', //'th', 'ko', 'ja', 'ms', 
            //'id', // indonesian
           // 'tl', // tagalog
           // 'vi', //vietnamise
          //  'hi', // hindi
          //  'ta', // tamil
          //  '**', // other
        ),
        'c' => array(
             'AU', 'CN', 'HK', 'IN', 'ID', 'JP', 'MY', 'NZ', 'TW', 'SG', 'TH', 'KR', 'US', 'PH', 'VN','**'
        ),
        'm' => array(
            'USD', 'HKD', 'GBP', 'CNY', 'SGD', 'JPY'
        ),
        'add_l'=> array(), // additional languages... 
        'add_c'=> array(), // additional countries...(eg. '-R' => 'Regional' )
        'add_m'=> array(), // additional currencies...

        
    );
    /**
     * initalizie the cfg aray
     *
     */
    function cfg()
    {
        static $loaded  = false;
        if ($loaded) {
            return self::$cfg;
        }
        $loaded =true;
        $ff= HTML_FlexyFramework::get();
         
        // since our opts array changed alot..
        $opts = empty($ff->Pman_Core_I18N) ? (empty($ff->Pman_I18N) ? array() : $ff->Pman_I18N)  : $ff->Pman_Core_I18N;
        
        $i = DB_DataObject::Factory('I18n');
        // load the cofiguration
        foreach($opts as $k=>$v) {
            
            if ($v == '*') { // everything..
                self::$cfg[$k] = $i->availableCodes($k);
                continue;
            }
            self::$cfg[$k] = is_array($v) ? $v  : explode(',', $v);
        }
        return self::$cfg;
        
        
    }
    
    
      // the default configuration.
    
    function applyFilters($q, $au)
    {
        
        DB_DataObject::debugLevel(1);
        if (!empty($q['query']['_with_en'])) {
            
            $this->buildDB(); // ensure we have the full database...
            
            $this->selectAdd("
                i18n_translate(ltype, lkey, 'en') as lval_en
                
            ");
        }
        if (!empty($q['query']['name'])) {
            //DB_DAtaObject::debugLevel(1);
        
            $this->whereAdd("lval LIKE '". $this->escape($q['query']['name']). "%'");
        }
    }
    
    
    function translate($inlang,$ltype,$kval)
    {
        
        $x = DB_DataObject::factory('i18n');
        $x->ltype = $ltype;
        $x->inlang= $inlang;
        $x->lkey = $kval;
        $x->limit(1);
        $x->find(true);
        return $x->lval;
        
    }
    
    
    
    function toTransList($ltype, $inlang)
    {
        
        
        $this->ltype = $ltype;
        $this->inlang= $inlang;
        $this->selectAdd();
        $this->selectAdd('lkey as code, lval as title');
        
        $this->find();
        $ret = array();
        while ($this->fetch()) {
            $ret[] = array(
                'code'  => $this->code,
                'title' => $this->title
            );
        }
        return $ret;
    }
     
    
    
    
    // -------------- code to handle importing into database..
    
    
    
    
    // returns a list of all countries/languages etc.. (with '*')
    function availableCodes($t)
    {
        $ret = array();
        $cfg = $this->cfg();

        switch ($t) {
            case 'c':
                require_once 'I18Nv2/Country.php';
                
                $c = new I18Nv2_Country('en');
                $ret =  array_keys($c->codes);
                if (!empty($cfg['add_c'])) {
                    $ret = array_merge($ret, array_keys($cfg['add_c']));
                }
                $ret[] = '**';
                break;
            
            case 'l':
                require_once 'I18Nv2/Language.php';
                $c = new I18Nv2_Language('en');
                $ret =  array_keys($c->codes); // we need to make sure these are lowercase!!!
                 
                if (!empty($cfg['add_l'])) {
                    $ret = array_merge($ret, array_keys($cfg['add_l']));
                }

                $ret[] = '**';
                break;
            case 'm':
                require_once 'I18Nv2/Currency.php';
                $c = new I18Nv2_Currency('en');
                $ret =  array_keys($c->codes);
                if (!empty($cfg['add_m'])) {
                    $ret = array_merge($ret, array_keys($cfg['add_m']));
                }
                $ret[] = '**';
                break;
        }
        
        foreach ($ret as $k=>$v) {
            $ret[$k] = ($t=='l') ? $ret[$k] : strtoupper($v);
        }

        return $ret;
    }
    
    
    function buildDB($ltype= false, $inlang= false )
    {
        $cfg = $this->cfg();
        if ($ltype === false) {
            // trigger all builds.
            //DB_DataObject::debugLevel(1);
            $this->buildDB('c');
            $this->buildDB('l');
            $this->buildDB('m');
            return;
        }
        
        if ($inlang == '**') {
            return; // dont bother building generic..
        }
        
        
        if ($inlang === false) {
            // do we want to add our 'configured ones..'
            // We only build translatiosn for our configured ones..
            //foreach( $this->availableCodes('l') as $l) {
                
            foreach( $cfg['t'] as $l) {
                $this->buildDB($ltype, $l);
            }
            return;
        }
        
        
        //DB_DataObject::debugLevel(1);
        $x = DB_DataObject::factory('I18n');
        $x->inlang= $inlang;
        $x->ltype = $ltype;
        
        $complete = $x->fetchAll('lkey');
        
        $list =  $this->availableCodes($ltype);
        
        
        foreach($list as $lkey) {
            // skip ones we know we have done...
            if (in_array($lkey, $complete)) {
                continue;
            }
            $x = DB_DataObject::factory('I18n');
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
        $cfg = $this->cfg();
        if (empty($k)) {
            return '??';
        }
        
        //$lbits = explode('_', strtoupper($lang));
        $lbits = explode('_', $lang);
         
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
        $ret = $cache[$lang][$type]->getName($k);
        
        if ($type == 'l') {
            $tolang = explode('_', $k);
            $tolang[0] = strtolower($tolang[0]);
            print_r($tolang);
            $ret = $cache[$lang][$type]->getName($tolang[0]);
            if (count($tolang) > 1) {
                $ret.= '('.$tolang[1].')'; 
            }
        }
        // our wierd countries/langs etc..
        if (isset($cfg['add_' . $type][$k])) {
            return $cfg['add_' . $type][$k];
            
        }
        
        return $ret;
        
        
    }
    
    
}

<?php
/**
 * Table Definition for i18n
 *
 * This is heavily related to the Pman_I18n implementation..
 *
 * It should eventually replace most of that..
 * 
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

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
        'c' => '*', // array(
             //'AU', 'CN', 'HK', 'IN', 'ID', 'JP', 'MY', 'NZ', 'TW', 'SG', 'TH', 'KR', 'US', 'PH', 'VN','**'
        //),
        'm' => array(
            'USD', 'HKD', 'GBP', 'CNY', 'SGD', 'JPY'
        ),
        'p' => '*',
        'add_l'=> array(), // key -> value additional languages... 
        'add_c'=> array(), // additional countries...(eg. '-R' => 'Regional' )
        'add_m'=> array(), // additional currencies...
        'add_p'=> array(), // additional currencies...

        
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
        
        // BC compatible.. if any of these are set, then we use them as the settings..
        $opts = array();
        foreach(array('Pman_Core_I18n', 'Pman_Core_I18N', 'Pman_I18N','Pman_I18n') as $pk) {
            if (isset($ff->$pk)) {
                //var_dump($pk);
                $opts =  $ff->$pk;
                break;
            }
        }
        //echo '<PRE>';print_R($opts);//exit;
        
         
        // var_dump($opts);exit;
        
        $i = DB_DataObject::Factory('I18n');
        // load the cofiguration
        foreach($opts as $k=>$v) {
            
            if ($v == '*') { // everything..
               // self::$cfg[$k] = $i->availableCodes($k, false);
                continue;
            }
             
            self::$cfg[$k] = is_array($v) ? $v  : explode(',', $v);
        }
        // available codes recursively calls this... -- so the above has to be set first..
        foreach($opts as $k=>$v) {
            
            if ($v == '*') { // everything..
                self::$cfg[$k] = '*'; //$i->availableCodes($k, false);
                continue;
            }
           
        }
        
        
        return self::$cfg;
        
        
    }
    
    
      // the default configuration.
    
    function applyFilters($q, $au)
    {
        $this->buildDB();
        //DB_DataObject::debugLevel(1);
        if (!empty($q['query']['_with_en'])) {
            
            $this->buildDB(); // ensure we have the full database...
            
            $this->selectAdd("
                i18n_translate(ltype, lkey, 'en') as lval_en
            ");
        }
         
        if (!empty($q['!code'])) {
            $this->whereAddIn('!lkey', explode(',', $q['!code']), 'string'); 
        }
        if (!empty($q['query']['name'])) {
            //DB_DAtaObject::debugLevel(1);
            $v = strtoupper($this->escape($q['query']['name']));
            $this->whereAdd("upper(lval) LIKE '%{$v}%'");
        }
        
        if (!empty($q['_filtered']) && !empty($this->ltype)) {
            $cfg = $this->cfg();
            $filter = $cfg[$this->ltype];
            if(is_array($filter)){
                $this->whereAddIn('lkey', $filter, 'string'); 
            }
            
        }
        
        if(!empty($q['_with_geoip_count'])) {
            
            $this->selectAdd("
                (
                    SELECT
                            COUNT(geoip_division.id)
                    FROM
                            geoip_division
                    WHERE
                            geoip_division.country = i18n.lkey
                ) AS no_of_division,
                (
                    SELECT
                            COUNT(geoip_city.id)
                    FROM
                            geoip_city
                    WHERE
                            geoip_city.country = i18n.lkey
                ) AS no_of_city
            ");
        }
        
        if(!empty($q['_hide_unused'])) {
            $this->whereAdd("
                (
                    SELECT
                            COUNT(geoip_division.id)
                    FROM
                            geoip_division
                    WHERE
                            geoip_division.country = i18n.lkey
                ) > 0
            ");
        }
    }
    
    function lookupCode($inlang,$ltype,$name)
    {
        $x = DB_DataObject::factory('i18n');
        $x->ltype = $ltype;
        $x->lval = $name;
        $x->inlang= $inlang;
        
        $x->limit(1);
        if ($x->find(true) && !empty($x->lkey)) {
            return $x->lkey;
        }
        return '';
        
        
    }
    
    
    function translate($inlang,$ltype,$kval)
    {
        
        $x = DB_DataObject::factory('i18n');
        $x->ltype = $ltype;
        $x->lkey = $kval;
        $x->inlang= $inlang;
        $fallback = clone($x);
        
        $x->limit(1);
        if ($x->find(true) && !empty($x->lval)) {
            return $x->lval;
        }
        $fallback->inlang = 'en';
        if ($fallback->find(true) && !empty($fallback->lval)) {
           return $fallback->lval;
        }
        return $kval;
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
    function availableCodes($t, $filtered = true)
    {
        $ret = array();
        $cfg = $this->cfg();
        //echo '<PRE>';print_r($cfg);
        switch ($t) {
            case 'c':
                require_once 'I18Nv2/Country.php';
                
                $c = new I18Nv2_Country('en');
                $ret =  array_keys($c->codes);
                if (!empty($cfg['add_c'])) {
                    $ret = array_merge($ret, array_keys($cfg['add_c']));
                }
                
                
                 
                
                $ret[] = '**';
                //echo '<PRE>';print_R($cfg); print_r($ret); exit;
                break;
            
            case 'l':
                require_once 'I18Nv2/Language.php';
                $c = new I18Nv2_Language('en');
                $ret =  array_keys($c->codes); // we need to make sure these are lowercase!!!
                
                
                foreach ($cfg['add_l'] as $k=>$v){
                    // make sure that add_l is formated correctly.. (lower_UPPER?)
                    $tolang = explode('_', $k);
                    $tolang[0] = strtolower($tolang[0]);
                    $tolang = implode('_', $tolang);
                    
                    unset($cfg['add_l'][$k]); // if they match..unset first.. then set
                    
                    $cfg['add_l'][$tolang] = $v;
                }
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
            case 'p':
                require_once 'I18Nv2/PhonePrefix.php';
                $c = new I18Nv2_PhonePrefix('en');
                $ret =  array_keys($c->codes);
                if (!empty($cfg['add_p'])) {
                    $ret = array_merge($ret, array_keys($cfg['add_p']));
                }
                $ret[] = '**';
                break;
        }
        
        
        
        if ($filtered  && !empty($cfg[$t]) && is_array($cfg[$t])) {
            // then there is a filter. - we should include all of them, even if they are not relivatn??
            return $cfg[$t]; //array_intersect($cfg[$t], $ret);
            
        }
        
        // why upper case everyting?!?!?
        
        //foreach ($ret as $k=>$v) {
        //    $ret[$k] = ($t=='l') ? $ret[$k] : strtoupper($v);
        //}

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
            $this->buildDB('p', 'en');
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
            if (empty($lkey)) { // not sure why we get empty values here.
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
        $orig_lang = $lang;
        $lang = $lbits[0];
        
        if (!isset($cache[$lang])) {
            require_once 'I18Nv2/Country.php';
            require_once 'I18Nv2/Language.php';
            require_once 'I18Nv2/Currency.php';
            require_once 'I18Nv2/PhonePrefix.php';
            $cache[$lang] = array(
                'l' =>  new I18Nv2_Language($lang, 'UTF-8'),
                'c' => new I18Nv2_Country($lang, 'UTF-8'),
                'm' => new I18Nv2_Currency($lang, 'UTF-8'),
                'p' => new I18Nv2_PhonePrefix($lang, 'UTF-8')
            );
            //echo '<PRE>';print_r(array($lang, $cache[$lang]['c']));
        }
        
        if ($k == '**') {
            return 'Other / Unknown';
        }
        
        
        // for languages if we get zh_HK then we write out Chinese ( HK )
        
        
        if ($type == 'l' && strpos($k, '_') > -1) {
            $tolang = explode('_', $k);
            $ret = $cache[$lang][$type]->getName(strtolower($tolang[0])) .  '('.$tolang[1].')'; 
            
        } else {
            $ret = $cache[$lang][$type]->getName($k);
        }
        
        if ($orig_lang == 'zh_HK' || $orig_lang == 'zh_TW' ) {
            // then translation is by default in simplified.
            //print_r($ret);
            $ret = @iconv("UTF-8", "GB2312//IGNORE", $ret);
            //print_r($ret);
            $ret = @iconv("GB2312", "BIG5//IGNORE", $ret);
            //print_r($ret);
            
            $ret = @iconv("BIG5", "UTF-8//IGNORE", $ret);
            //print_r($ret);
         }
        
        
        
        // our wierd countries/langs etc..
        if (isset($cfg['add_' . $type][$k])) {
            return $cfg['add_' . $type][$k];
            
        }
        
        return $ret;
        
        
    }
    
    
}

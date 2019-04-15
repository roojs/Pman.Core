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

class Pman_Core_I18n extends Pman
{
 
    
     
    
     
    
    
    function getAuth()
    {
        parent::getAuth(); // load company!
        //return true;
        $au = $this->getAuthUser();
        //if (!$au) {
        //    $this->jerr("Not authenticated", array('authFailure' => true));
        //}
        $this->authUser = $au;
        
        $ff= HTML_FlexyFramework::get();
         
        
        $opts = empty($ff->Pman_Core_I18N) ?
                (empty($ff->Pman_I18N) ? array() : $ff->Pman_I18N)
                : $ff->Pman_Core_I18N;
        
         
        
        
        
        return true;
    }
     
    
    function guessUsersLanguage()
    {
        $this->sessionState(0);
        
        $lang = !$this->authUser || empty($this->authUser->lang ) ? 'en' : $this->authUser->lang;
        
        /// verify the selected language..
        $i = DB_DataObject::Factory('I18n');
        $i->ltype = 'l';                           // string(1)  not_null multiple_key
        $i->lkey = $lang;                            // string(8)  not_null
        if (!$i->count()) {    
            $i = DB_DataObject::Factory('I18n');
            $i->buildDb();
            
            $i = DB_DataObject::Factory('I18n');
            $i->ltype = 'l';                           // string(1)  not_null multiple_key
            $i->lkey = $lang;  
            if (!$i->count()) { 
                $this->jerr('invalid lang configured: ' . $lang);
            }
        }
        
        
        return explode('_', $lang);
    }
     
    function get($s ='', $opts=array())
    {
     
        $this->sessionState(0);
        $lbits = $this->guessUsersLanguage();
         
        if ($this->authUser && !empty($_REQUEST['_debug'])) {
            DB_DataObject::debugLevel(1);
        }
        
        
        
        $i = DB_DataObject::Factory('I18n');
        $i->is_active = 1;
        switch($s) {
            case 'Lang':
                 
                
                $i->ltype = 'l';
                $i->applyFilters($_REQUEST, $this->authUser, $this);
                $this->jdata($i->toTransList('l',  implode('_',$lbits)));
                break;

            case 'Country':
                $i->ltype = 'c';
                $i->applyFilters($_REQUEST, $this->authUser, $this);
                $this->jdata($i->toTransList('c',  implode('_',$lbits)));
               
                break;
                
            case 'Currency':
                $i->ltype = 'm';
                $i->applyFilters($_REQUEST, $this->authUser, $this);
                $this->jdata($i->toTransList('m',  implode('_',$lbits)));
                break;
            
            case 'Timezone':
                $ar = DateTimeZone::listAbbreviations();
                $ret = array();
                $tza = array();
                foreach($ar as $tl => $sar) {
                    foreach($sar as $tz) {
                        $tza[]  = $tz['timezone_id'];
                    
                    }
                }
                $tza= array_unique($tza);
                sort($tza);
                foreach($tza as $tz) {
                    //filtering..
                    if (empty($_REQUEST['q']) ||
                            0 === strcasecmp(
                                    substr($tz,0, strlen($_REQUEST['q'])),
                                    $_REQUEST['q'])
                    ) {
                        $ret[] = array('tz' => $tz);
                    }
                    
                }
                $this->jdata($ret);
                
                
                
             
                
        }
        if (!empty($_REQUEST['debug'])) {
            DB_DataObject::debugLevel(1);
        }
        
        $i = DB_DataObject::Factory('I18n');
        $i->buildDB();
      
       
        $i = DB_DataObject::Factory('I18n');
        $cfg = $i->cfg();
        $langs = $cfg['t'];
       // var_dump($langs);exit;
        $ar = array();
        foreach($langs as $lang)
        {
            //$rlang = array_shift(explode('_', strtoupper($lang)));
            $ll = explode('_', $lang);
            $rlang = array_shift($ll);
            
            $ar[$lang] = array();
            $i = DB_DataObject::Factory('I18n');
            $ar[$lang]['l'] = $i->toTransList('l',  $rlang);
            $i = DB_DataObject::Factory('I18n');
            $ar[$lang]['c'] =  $i->toTransList('c', $rlang);
            $i = DB_DataObject::Factory('I18n');
            $ar[$lang]['m'] = $i->toTransList('m', $rlang);
        }
        //echo '<PRE>';print_r($ar);
        header('Content-type: text/javascript');
        echo "Roo.namespace('Pman.I18n');";
        echo 'Pman.I18n.Data = ' .  json_encode($ar);
        exit;
        
        
        
    }
    
 
    
     /**
     * translate (used by database building);
     * usage :
     * require_once 'Pman/Core/I18n.php';
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
        
        // does it need caching?
        
        $i = DB_DataObject::Factory('I18n');
        return $i->translate($lang,$type,$k);
        
        
         
        
        
    }
    /**
     * translate a list of items
     * @param Pman_Core_DataObjects_Person $au Authenticated user
     * @param String                      $type  c/l/m
     * @param String                      $k     'comma' seperated list of keys to translate
     */
    
    function translateList($au, $type, $k)  
    {
        $ar = explode(',', $k);
        $ret = array();
        foreach($ar as $kk) {
            $ret[] = $this->translate($au, $type, $kk);
        }
        return implode(', ', $ret);
    }
    /**
     * DO NOT USE THIS -- see core_curr_rates dataobject.
     */
    
    
    function convertCurrency($val, $from, $to)
    {
        
        return DB_DAtaObject::Factory('core_curr_rate')->convert($val,$from,$to);
    
    }
    /**
     * DO NOT USE THIS -- see core_curr_rates dataobject.
     *
     */
    
    
    
    function loadRates()
    {   
        static $rates = array();
        
        if (!empty($rates)) {
            $this->rates = $rates;
            return;
        }
        
        $this->rates = $rates = DB_DAtaObject::Factory('core_curr_rate')->currentRates();
        
    }
    
    
     
    
}
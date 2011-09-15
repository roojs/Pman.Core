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

class Pman_Core_I18N extends Pman
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
         
        
        $opts = empty($ff->Pman_Core_I18N) ? (empty($ff->Pman_I18N) ? array() : $ff->Pman_I18N)  : $ff->Pman_Core_I18N;
        
        $i = DB_DataObject::Factory('I18n');
        $this->cfg = $i->cfg();
        
        
        
        return true;
    }
     
    
     
    function get($s ='')
    {
        
        $i = DB_DataObject::Factory('I18n');
        $i->buildDb();
        $this->outputJavascript();
    
        
        exit;
        
    }
    
    function outputJavascript()
    {
        
        $i = DB_DataObject::Factory('I18n');
        $cfg = $i->cfg();
        $langs = $cfg['t'];
       // var_dump($langs);exit;
        $ar = array();
        foreach($langs as $lang)
        {
            $rlang = array_shift(explode('_', strtoupper($lang)));
            
            $ar[$lang] = array(
                'l' => $i->toTransList('l',  $rlang),
                'c' => $i->toTransList('c', $rlang),
                'm' => $i->toTransList('m', $rlang),
            );
        }
        //echo '<PRE>';print_r($ar);
        header('Content-type: text/javascript');
        echo 'Pman.I18n.Data = ' .  json_encode($ar);
        exit;
        
        
        
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
        
        // does it need caching?
        
        $i = DB_DataObject::Factory('I18n');
        return $i->translate($lang,$type,$k);
        
        
         
        
        
    }
     
    
}
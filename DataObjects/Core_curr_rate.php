<?php
/**
 * Table Definition for core_curr_rate
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_curr_rate extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */
  
    public $__table = 'core_curr_rate';    // table name
    public $id;
    public $curr;
    public $rate;
    public $from_dt;
    public $to_dt;

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function applyFilters($q, $au, $roo)
    {
        
    }
    
    /**
     * current rate fetching
     *
     * - we used to do historical rates... (but sources keep disappearing for that..)
     *
     * this just get's the current rates from the ecb..
     * 
     * 
     */
    
    var $rates = array();
    function loadRates()
    {
        
        // how often do we need to know this..?
        
        
        if (!empty($this->rates)) {
            return true;
        }
        // load our default rates to start with..
        $dom = simplexml_load_file(dirname(__FILE__).'/eurofxref-daily.xml');
        $this->rates['EUR'] = 1.0;
        $this->rates['TWD'] = 46.7008412;
        $this->rates['VND'] = 26405.3;
       
       
        foreach($dom->Cube->Cube->Cube as $c) {
           //echo '<PRE>';print_r($c );
            $this->rates[(string)$c['currency']] = (string)$c['rate'];
        }
        $this->rates['RMB'] = $this->rates['CNY'] ;
        // now try loading from latest..
        $target = ini_get('session.save_path').'/eurofxref-daily.xml';
        
        if (!file_exists($target) || filemtime($target) < (time() - 60*60*24)) {
            // this may fail...
            $f = @file_get_contents('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
            if (!strlen($f)) {
                return;
            } 
            file_put_contents($target,$f);
        
        } 
        $dom = simplexml_load_file($target);
        $this->rates['EUR'] = 1.0;
        $this->rates['TWD'] = 46.7008412;
        $this->rates['VND'] = 26405.3;
       
        foreach($dom->Cube->Cube->Cube as $c) {
           //echo '<PRE>';print_r($c );
            $this->rates[(string)$c['currency']] = (string)$c['rate'];
        }
        $this->rates['RMB'] = $this->rates['CNY'] ;
    }
}

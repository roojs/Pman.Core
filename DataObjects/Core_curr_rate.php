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
        // let's assume we do it once a week..
        $x = DB_DataObject::Factory('core_curr_rate');
        $x->whereAdd('to_date > NOW()');
        if ($x->count()) {
            // got some data for beyond today..
            return;
        }
        
        // load our default rates to start with..
        $dom = simplexml_load_file(dirname(__FILE__).'/../eurofxref-daily.xml');
        $rates['EUR'] = 1.0;
        $rates['TWD'] = 46.7008412; // taiwan dorlar
        $rates['VND'] = 25282.24; // veitnam dong?
       
       
        foreach($dom->Cube->Cube->Cube as $c) {
           //echo '<PRE>';print_r($c );
            $rates[(string)$c['currency']] = (string)$c['rate'];
        }
        $rates['RMB'] = $rates['CNY'] ;
        
        
        
        // now try loading from latest..
       
             // this may fail...
        $f = @file_get_contents('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
        if (!strlen($f)) {
            return false;
        }
        
        $dom = simplexml_load_file($target);
        $rates['EUR'] = 1.0;
        
        foreach($dom->Cube->Cube->Cube as $c) {
           //echo '<PRE>';print_r($c );
            $rates[(string)$c['currency']] = (string)$c['rate'];
        }
        $rates['RMB'] = $rates['CNY'] ;
        
        foreach($rates as $r=>$v) {
            
            $x = DB_DataObject::Factory('core_curr_rate');
            $x->curr = $c;
            $nl = clone($x);
            $x->orderBy('to_date DESC');
            $x->limit(1);
            
            
            
            $$nl->from_dt = DB_DataObject::sqlValue("NOW()");
            if ($x->find(true)) {
                if (strtotime($x->to_date) > time()) {
                    continue;
                }
                $from_date = $x->to_date;
                
            }
            
            $x->whereAdd('to_date > NOW()');
            
            
            
        }
        
        
        
        
        
        
        
    }
}

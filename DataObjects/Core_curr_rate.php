<?php
/**
 * Table Definition for core_curr_rate
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_curr_rate extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */
  
    public $__table = 'core_curr_rate';    // table name
    public $id;
    public $curr;
    public $rate;  // always to USD...
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
    function loadRates()
    {
        
        static $checked = false;
        if ($checked ) {
            return true;
        }
        $checked  = true;
        
        // how often do we need to know this..?
        // let's assume we do it once a week..
        $x = DB_DataObject::Factory('core_curr_rate');
        $x->whereAdd('to_dt > NOW()');
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
        
        $dom = simplexml_load_string($f);
        $rates['EUR'] = 1.0;
        
        foreach($dom->Cube->Cube->Cube as $c) {
           //echo '<PRE>';print_r($c );
            $rates[(string)$c['currency']] = (string)$c['rate'];
        }
        $rates['RMB'] = $rates['CNY'] ;
        
        foreach($rates as $cur=>$in_euro) {
            

            $rate = (1.0 / $rates['USD']) * $in_euro;
             
            
            
            $ov = DB_DataObject::Factory('core_curr_rate');
            $ov->curr = $cur;
            $nl = clone($ov);
            $ov->orderBy('to_dt DESC');
            $ov->limit(1);
            
            
            $nl->from_dt = DB_DataObject::sqlValue("NOW()");
            $nl->to_dt = DB_DataObject::sqlValue("NOW() + INTERVAL 7 DAY");
            if ($ov->find(true)) {
                if (strtotime($ov->to_dt) > time()) {
                    continue;
                }
                $nl->from_dt = $ov->to_dt;
                
            
                if ($ov->rate == $rate) {
                    // modify the old one to expire
                    $oo = clone($ov);
                    $ov->to_dt = $nl->from_dt;
                    $ov->update($oo);
                    continue;
                }
            } else {
                // no previous record...
                $nl->from_dt = '1970-01-01 00:00:00';
            }
            $nl->rate = $rate;
            // create a new row.
            $nl->insert();
            
            
            
        }
        
        
    }
    function rate($cur, $when)
    {
        $when = $when === false ? date('Y-m-d H:i:s') : $when;
        $this->loadRates(); // check if we have an rates.
        
        $r = DB_DataObject::factory('core_curr_rate');
        $r->curr = $cur;
        $r->whereAdd("from_dt < '" . date('Y-m-d H:i:s', strtotime($when)) . "'");
         
       
        $r->orderBy('from_dt DESC'); // most recent
        $r->limit(1);
        if ($r->find(true)) {
            return $r->rate;
        }
        return false;
    }
    
    function convert($val, $from, $to, $when = false)
    {
        
        
        $fr = $this->rate($from, $when);
        $tr = $this->rate($to, $when);
        
        // crappy error handling..
        if ($fr === false) {
            return false;
        }
        if ($tr === false) {
            return false;
        }
        
        return ((1.0 / $fr) * $val) * $tr;
  
        
    
    }
    
    function currentRates()
    {
        $this->loadRates();
       // DB_DataObject::debugLevel(1);
        $c = DB_DAtaObject::factory('core_curr_rate');
        $c->whereAdd('from_dt < NOW() AND to_dt > NOW()');
        $c->find();
        $ret = array();
        while($c->fetch()) {
            $ret[$c->curr] = $c->rate;
        }
        return $ret;
        
        
        
    }
    
    
}

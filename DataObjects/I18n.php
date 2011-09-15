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
    
    static $cfg; // the default configuration.
    
    function applyFilters($q, $au)
    {
        
        //DB_DataObject::debugLevel(1);
        if (!empty($q['query']['_with_en'])) {
            $this->selectAdd("
                i18n_translate(ltype, lkey, 'en') as lval_en
                
            ");
        }
    }
    
    function loadDefault()
    {
        /// 
        
        
        
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

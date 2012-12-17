<?php
/**
 * Table Definition for core company
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_enum extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_enum';                       // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $etype;                           // string(32)  not_null
    public $name;                            // string(255)  not_null
    public $active;                          // int(2)  not_null
    public $seqid;                           // int(11)  not_null multiple_key
    public $seqmax;                           // int(11)  not_null multiple_key
    public $display_name;
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    function applyFilters($q, $au)
    {
        
        //DB_DataObject::debugLevel(1);
        if (!empty($q['query']['empty_etype'])) {
            $this->whereAdd("etype = ''");
        }
    }
    
    function onUpdate($old, $req)
    {
        $x = $this->factory($this->tableName());
        $x->query("SELECT core_enum_seqmax_update('". $this->escape($this->etype) ."')");
        if ($old->etype != $this->etype) {
            $x->query("SELECT core_enum_seqmax_update('". $this->escape($old->etype) ."')");
        }
        
    }
    function beforeUpdate($old, $request,$roo)
    {
        $tn = $this->tableName();
        $x = $this->factory($tn);
        if(!($old->etype == $request['etype'] && $old->name == $request['name'])){
            $x->whereAdd("etype = '{$request['etype']}' AND name = '{$request['name']}'");
            $x->find(true);
            if($x->count() > 0){
                $roo->jerr('is exsiting');
            }
        }
    }
    function beforeInsert($req, $roo)
    {
        $tn = $this->tableName();
        $x = $this->factory($tn);
        
        if(empty($req['etype'])){
            if($x->get('name', $req['name'])){
                $roo->jerr('name is exsiting');
            }
        }else{
            $x->whereAdd("etype = '{$req['etype']}' AND name = '{$req['name']}'");
            $x->find(true);
            if($x->count() > 0){
                $roo->jerr('is exsiting');
            }
        }
    }
    function onInsert($req)
    {
        $x = $this->factory($this->tableName());
        $x->query("SELECT core_enum_seqmax_update('". $this->escape($this->etype) ."')");
         
    }
    
    function lookup($etype,$name) {
        $ce = DB_DataObject::Factory('core_enum');
        $ce->etype = $etype;
        $ce->name = $name;
        if ($ce->find(true)) {
            return $ce->id;
        }
        return 0;
        
    }
    
    function lookupObject($etype,$name, $create= false)
    {
        
        static $cache = array();
        $key = "$etype:$name";
        if (isset($cache[$key]) ) {
            return $cache[$key];
        }
        $ce = DB_DataObject::Factory('core_enum');
        $ce->etype = $etype;
        $ce->name = $name;
        if ($ce->find(true)) {
            $cache[$key] = $ce;
            return $ce;
        }
        if ($create) {
            $ce->active = 1;
            $ce->insert();
            $cache[$key] = $ce;
            return $ce->id;
            
        }
        
        
        return false;
        
    }
    /**
     * The base enums that the system always needs,
     * If we need to add modular enums, then this can be extended, and the two
     * base data applied.
     *
     * This might be moved to an external file later? - eg json...
     *
     */
    function baseEnums()
    {
        //
        return array(
            array(
                'name' => 'company_type',
                'display_name' =>  'Company Types',
                'cn' => array(
                    array(
                        'name' => 'OWNER',
                        'display_name' => 'Owner',
                        'seqid' => 999, // last...
                    )
                    
                )
            )
        );  
        
        
        
    }
    
    function initEnums($data = false, $base = array())
    {
        DB_DataObject::DebugLevel(1);
        if ($data === false) {
            $this->initEnums($this->baseEnums());
            return;
        }
        foreach($data as $row) {
            $t = DB_DAtaObject::Factory('core_enum');
            $t->setFrom(array_merge($row , $base));
            
            echo '<PRE>';print_r($t);
            
            if (!$t->find(true)) {
                $t->insert();
            }
            if (!empty($row['cn'])) {
                $this->initEnums($row['cn'], array('etype', $t->name));
            }
        }
        
    }
    
    
}

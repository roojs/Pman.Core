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
        
        if(!empty($q['!name'])){
            $names = explode(',', $q['!name']);
            foreach($names as $name){
                $this->whereAdd("
                    core_enum.name NOT IN ('$name')
                ");
            }
        }
    }
    
    function postListFilter($data, $authUser, $q) {
        
        if(!empty($q['cmsTab'])){
            $ret = array();
            foreach($data as $k=>$v){
                if($v['name'] == 'element'){
                    continue;
                }
                $ary = $v;
                if($ary['name'] == 'page'){
                    $ary['display_name'] = $v['display_name'].' / Elements';
                }
                $ret[] = $ary;
            }
            $data = $ret;
        }
        
        return $data;
        
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
    
    function fetchAllByType($etype, $fetchArg1=false, $fetchArg2=false, $fetchArg3=false)
    {
        $x = DB_DataObject::factory('core_enum');
        $x->etype = $etype;
        $x->active = 1;
        return $x->fetchAll($fetchArg1, $fetchArg2, $fetchArg3);
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
                'etype' => '',
                'name' => 'COMPTYPE',
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
    
    function initEnums($data, $base = array())
    {
        
        var_Dump($data);
        $seq_id = 0;
        if (!empty($base['etype'])) {
            $seq_id = 1;
            $t = DB_DAtaObject::Factory('core_enum');
            $t->etype = $base['etype'];
            $t->selectAdD();
            $t->selectAdD('max(seqid) as seqid');
            if ($t->find(true)) {
                $seq_id = $t->seqid+1;
            }
        }
        foreach($data as $row) {
            $t = DB_DAtaObject::Factory('core_enum');
            
            $t->setFrom($row);
            $t->setFrom($base);
            unset($t->seqid); // these might have been changed
            unset($t->display_name); // these might have been changed
            
            
            if (!$t->find(true))
            {
                $t->setFrom($row);
                $t->setFrom($base);
            
                if (!empty($base['etype']) && empty($row['seqid'])) {
                    $t->seqid = $seq_id;
                    $seq_id++;
                }
                $t->insert();
            }
            if (!empty($row['cn'])) {
                $this->initEnums($row['cn'], array('etype' => $t->name));
            }
        }
        
    }
    
    
}

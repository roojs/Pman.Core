<?php
/**
 * Table Definition for core enum - it's used in pulldowns or simple option lists.
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
    
    function applyFilters($q, $au, $roo)
    {
        
//        DB_DataObject::debugLevel(1);
        if (!empty($q['query']['empty_etype'])) {
            $this->whereAdd("core_enum.etype = ''");
        }
        
        // this should be handled by roo... using '!name[0]' ....
        if(!empty($q['!name'])){
            $names = is_array($q['!name']) ? $q['!name'] : explode(',', $q['!name']);
            foreach($names as $name){
                $name  = $this->escape($name);
                $this->whereAdd("
                    core_enum.name NOT IN ('$name')
                ");
            }
        }
        if(!empty($q['search']['display_name'])) {
            $name = $this->escape($q['search']['display_name']);
            // ilike on postgres?!?
            $this->whereAdd("
                core_enum.display_name LIKE '{$name}%'
            ");
            
        }
        
        if(!empty($q['query']['search'])) {
            $name = $this->escape($q['query']['search']);
            // ilike on postgres?!?
            $this->whereAdd("
                    core_enum.name LIKE '%{$name}%'
                OR
                    core_enum.display_name LIKE '%{$name}%'
            ");
        }
        if(!empty($q['query']['search_begins'])) {
            $name = $this->escape($q['query']['search_begins']);
            // ilike on postgres?!?
            $this->whereAdd("
                    core_enum.name LIKE '{$name}%'
                OR
                    core_enum.display_name LIKE '{$name}%'
            ");
        }
        
        if (isset($q['_etypes'])) {
            $this->whereAddIn('core_enum.etype', explode(',', $q['_etypes']), 'string');
        }
        
    }
    
    function checkPerm($lvl, $au, $req=null)
    {
        if (!$au) {
            return false;
        }
        return true;
    }
    
    
    function autoJoinCmsTranslate($lang)
    {
        $l = $this->escape($lang);
        
        $this->_join .= "
            LEFT JOIN 
                cms_templatestr 
            ON
                cms_templatestr.lang = '$l'
            AND
                cms_templatestr.on_table = 'core_enum'
            AND
                cms_templatestr.on_id = core_enum.id
            AND
                cms_templatestr.on_col = 'display_name'
        ";
        
        $this->selectAdd("
            CASE WHEN 
                '$l' = 'en' THEN display_name 
            ELSE
                CASE WHEN cms_templatestr.txt IS NOT NULL AND cms_templatestr.txt != '' THEN
                    cms_templatestr.txt
                ELSE
                    display_name
                END
            END as  display_name_tr 
        ");
        
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
    
    
    function beforeUpdate($old, $request,$roo)
    {   
        if(!empty($request['_merge_id'])){
            $this->merge($request['_merge_id'], $roo);
        }
        
        $tn = $this->tableName();
        $x = $this->factory($tn);
        // check if record exists?
        if(isset($request['etype']) &&   !($old->etype == $request['etype'] && $old->name == $request['name'])){
            $x->whereAdd("etype = '{$this->escape($request['etype'])}' AND name = '{$this->escape($request['name'])}'");
            $x->whereAdd("id != ".((int) $this->id));
            $x->find(true);
            if($x->count() > 0){
                $roo->jerr('a duplicate record already exists');
            }
        }
    }
    function beforeInsert($req, $roo)
    {
        $tn = $this->tableName();
        $x = $this->factory($tn);
        
        if(empty($req['etype'])){
            if (empty($req['name'])) {
                $roo->jerr('name missing for new top level etype');
            }
            if($x->get('name', $req['name'])){
                $roo->jerr('name already exists');
            }
        } else if (!empty($req['_bulk_names'])) {
            
            
            $lines = explode("\n", $req['_bulk_names']);
            foreach($lines as $l) {
                $l = trim($l);
                if (!strlen($l)) {
                    continue;
                }
                $bits = explode(',', $l);
                $x = $this->factory($tn);
                $rr = array(
                    'etype');
                $rr['name'] = array_shift($bits);
                $rr['display_name'] = empty($bits) ? $rr['name'] : $bits[0];
                $rr[']
                
                
            }
            
            
        } else {
            $x->whereAdd("etype = '{$this->escape($req['etype'])}' AND name = '{$this->escape($req['name'])}'");
            $x->find(true);
            if($x->count() > 0){
                $roo->jerr('name already exists');
            }
        }
    }
    
    function onInsert($req, $roo)
    {
        $x = $this->factory($this->tableName());
        $x->query("SELECT core_enum_seqmax_update('". $this->escape($this->etype) ."')"); // no idea why need to do this!!??
         
    }
    
    function onUpdate($old, $req)
    {
        $x = $this->factory($this->tableName());
        $x->query("SELECT core_enum_seqmax_update('". $this->escape($this->etype) ."')"); // no idea why need to do this!!??
        if ($old->etype != $this->etype) {
            $x->query("SELECT core_enum_seqmax_update('". $this->escape($old->etype) ."')");
        }
        
        if($this->name != $old->name && !empty($old->name) && empty($old->etype) && empty($this->etype)){
            $x->query("UPDATE core_enum SET etype = '". $this->escape($this->name) 
                ."' WHERE etype = '". $this->escape($old->name)."'");
        }
    }
    
    /**
     * lookup by etype/name and return id
     */
    function lookup($etype,$name) {
        $ce = DB_DataObject::Factory('core_enum');
        $ce->etype = $etype;
        $ce->name = $name;
        if ($ce->find(true)) {
            return $ce->id;
        }
        return 0;
    }
    
    function lookupCreate($etype,$name, $display_name=false) {
        
        // check
        $ce = DB_DataObject::Factory('core_enum');
        $ce->setFrom(array(
            'etype' => '',
            'name' => $etype
        ));
        if (!$ce->find(true)) {
            $ce->display_name = $etype;
            $ce->insert();
        }
        
        $ce = DB_DataObject::Factory('core_enum');
        $ce->etype = $etype;
        $ce->name = $name;
        if ($ce->find(true)) {
            return $ce->id;
        }
        $ce->active = 1;
        $ce->display_name = $display_name === false ? $ce->name : $display_name;
        return  $ce->insert();
        
    }
    
    function lookupById($id) {
        $ce = DB_DataObject::Factory('core_enum');
        $ce->get($id);
        return $ce;
    }
    
    /**
     * 
     * 
     * 
     * @param string $etype
     * @param array $name array of name
     * @return array ID of core_enum 
     */
    
    function lookupAllByName($etype,$names) {
        $ce = DB_DataObject::Factory('core_enum');
        $ce->etype = $etype;
        $ce->whereAddIn('name', $names, 'string');
        
        if ($ce->count() > 0) {
            return $ce->fetchAll('id');
        }
        return array();
    }
    
    function fetchAllByType($etype, $fetchArg1=false, $fetchArg2=false, $fetchArg3=false)
    {
        $x = DB_DataObject::factory('core_enum');
        $x->etype = $etype;
        $x->active = 1;
        $x->orderBy('seqid ASC');
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
     // fixme - all calls should be to initDatabase, we need to remove initEnums
    function initDatabase($roo, $data)
    {
        $this->initEnums($data);
    }
    
    
    function initEnums($data, $base = array())
    {
        // base only contains etype...
        //print_r($data);
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
            
            $t->etype = isset($row['etype']) ? $row['etype'] : '';
            $t->etype = isset($base['etype']) ? $base['etype'] : $t->etype ;
            
            $t->name = isset($row['name']) ? $row['name'] : '';
            
            if (empty($t->name)) {
                print_R($data);
                die("ERROR: invalid name used for core_enum\n\n");
            }
            
            if (!$t->count()) {
                // base already gave it the etype..
                $t->setFrom($row);
                
                
                //$t->is_system_enum = 1; // this should be on the caller..
                
                if (!empty($row['seqid'])) {
                    $t->seqid = $seq_id;
                    $seq_id++;
                }
                
                $t->insert();
            }else{
                $t->find(true); // fetch it..
                if ( isset($row['is_system_enum'])) {
                     $t->is_system_enum = isset($row['is_system_enum']) ? $row['is_system_enum'] : $t->is_system_enum;
                    
                    $t->update();
                }
            }
            if (!empty($row['cn'])) {
                $this->initEnums($row['cn'], array('etype' => $t->name));
            }
        }
        
    }
    
    function merge($merge_to, $roo)
    {
        $affects  = array();
        
        $all_links = $GLOBALS['_DB_DATAOBJECT']['LINKS'][$this->_database];
        
        foreach($all_links as $tbl => $links) {
            foreach($links as $col => $totbl_col) {
                $to = explode(':', $totbl_col);
                if ($to[0] != $this->tableName()) {
                    continue;
                }
                
                $affects[$tbl .'.' . $col] = true;
            }
        }
        
        foreach($affects as $k => $true) {
            $ka = explode('.', $k);

            $chk = DB_DataObject::factory($ka[0]);
            
            if (!is_a($chk,'DB_DataObject')) {
                $roo->jerr('Unable to load referenced table, check the links config: ' .$ka[0]);
            }
            
            $chk->{$ka[1]} = $this->id;

            foreach ($chk->fetchAll() as $c){
                $cc = clone ($c);
                $c->{$ka[1]} = $merge_to;
                $c->update($cc);
            }
        }
        
        $this->delete();
        
        $roo->jok('Merged');
        
    }
    
    
}

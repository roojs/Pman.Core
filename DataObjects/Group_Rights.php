<?php
/**
 * Table Definition for Group_Rights
 */
require_once 'DB/DataObject.php';

 
class Pman_Core_DataObjects_Group_Rights extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Group_Rights';                    // table name
    public $rightname;                       // string(64)  not_null
    public $group_id;                        // int(11)  not_null
    public $AccessMask;                      // string(10)  not_null
    public $id;                              // int(11)  not_null primary_key auto_increment

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    
    var $fullRights = "ADESPIM";
    
    function listPermsFromGroupIds($grps, $isAdmin=false) {
        
        $t = clone($this);
        $t->whereAdd('group_id IN ('. implode(',', $grps).')');
        $t->find();
        $ret = array();
        while($t->fetch()) {
            if (isset($ret[$t->rightname])) {
                $ret[$t->rightname] = $this->mergeMask($ret[$t->rightname], $t->AccessMask);
                continue;
            }
            $ret[$t->rightname] = $t->AccessMask;
        }
        // blank out rights that are disabled by the system..
        $defs = $this->defaultPermData();
        //echo "<PRE>";print_r($defs);
        $r = array();
        foreach($defs as $k=>$v) {
            if (empty($v[0])) { // delete right if not there..
                $r[$k] = '';
                continue;
            }
            
            
            if (isset($ret[$k])) {
                if (empty($ret[$k]) && $isAdmin) {
                    $r[$k] = $v[0];
                    continue;
                }
                
                $r[$k] = $ret[$k];
                continue;
            }
            // not set contition...
            
            $r[$k] = $isAdmin ? $v[0] : $v[1];
            
       
        }
        
        return $r;
    }
    function mergeMask($a, $b) 
    {
        // default 
        $ret = '';
        for($i=0; $i< strlen($this->fullRights) ; $i++) {
            if ((strpos($a, $this->fullRights[$i]) > -1) ||
                (strpos($b, $this->fullRights[$i]) > -1)
            ) {
                $ret .= $this->fullRights[$i];
            }
        }
        return $ret;
        
        
    }
    
    
    function defaultPermData()
    {
        
        // we should do better caching of this... really..
        
        
        
        
        // what they mean:
        // A - add
        // D - delete
        // E - edit
        // S - list
        // P - print / export
        // I - import
        // M????
        
        
        
        static $Pman_DataObjects_Group_Right = array();
        if (!empty($Pman_DataObjects_Group_Right)) {
            return $Pman_DataObjects_Group_Right;
        }
        
        $ff = HTML_FlexyFramework::get();
        //print_R($ff);
        $enabled =  array('Core') ;
        $enabled = explode(',', $ff->enable);
        $disabled =  explode(',', $ff->disable? $ff->disable: '');
        $pman = $ff->rootDir . '/Pman/';
        $ret = array();
         //echo '<PRE>';print_r($enabled);
        foreach($enabled as $module) {
            $fn = $pman. $module.  '/'.$module. '.perms.json';
            if (!file_exists($fn)) {
                continue;
            }
            $ar = (array)json_decode(file_get_contents($fn));
            if (empty($ar)) {
                // since these are critical files.. die'ing with error is ok.
                die("invalid json file: " . $fn);
               }
           // echo '<PRE>';print_r($ar);
            foreach($ar as $k=> $perm) {
                if ($k[0] == '/') {
                    continue; // it's a comment..
                }
                if (in_array($module, $disabled) || in_array($module.'.'. $k, $disabled)) {
                    continue;
                }
                $ret[$module.'.'. $k ] = $perm;
            }
            
        }
        $Pman_DataObjects_Group_Right = $ret;
       // print_r($ret);
        return $Pman_DataObjects_Group_Right;
         
        
    }
    
    function adminRights() // get the admin rights - used when no accounts are available..
    {
        $defs = $this->defaultPermData();
        $ret = array();
        foreach($defs as $k=>$v) {
            $ret[$k] = $v[0];
        
        }
        return $ret;
        
    }
    
    function validate()
    {
        // all groups must have the minimum privaligess..
        // admin group must have all the privaliges
        $g = DB_DataObject::Factory('Groups');
        $g->get($this->group_id);
        $defs = $this->defaultPermData();
        switch($g->name) {
            case "Administrators";
                $this->AccessMask = $this->mergeMask($this->AccessMask, $defs[$this->rightname][0]);
                break;
                
            default:
                //$this->AccessMask = $this->mergeMask($this->AccessMask, $defs[$this->rightname][1]);
                break;
        
        }
        
    }
    function genDefault()
    {
        // need to create to special groups, admin & DEFAULT.
        $g = DB_DataObject::Factory('Groups');
        //$g->name = 'Default';
        //if (!$g->find(true)) {
        //    $g->insert();
        //}
        $g->id = 0;
        $this->applyDefs($g, 1);
    
        $g = DB_DataObject::Factory('Groups');
        $g->name = 'Administrators';
        $g->type = 0;
        if (!$g->find(true)) {
            $g->insert();
        }
        $this->applyDefs($g, 0);
        
        
    }
        
    function applyDefs($g, $usecol) {
        
        $defs = $this->defaultPermData();
        //echo '<PRE>';print_r($defs);
        //$usecol = 1;
        foreach($defs as $rightname => $defdata) {
            $gr = DB_DataObject::Factory('Group_Rights');
            $gr->rightname  = $rightname;
            $gr->group_id = $g->id;
            if (!$gr->find(true)) {
                $gr->AccessMask = $defdata[$usecol];
                $gr->insert();
                continue;
            }
            $oldgr = clone($gr);
            $gr->AccessMask = $gr->mergeMask($gr->AccessMask, $defdata[$usecol]);
            if ($gr->AccessMask == $oldgr->AccessMask) {
                continue;
            }
            $gr->update($oldgr);
        }
        
    }
        
    function checkPerm($lvl, $au) 
    {
        return false;
    }  
    
}

<?php
/**
 * Table Definition for Group_Rights
 *
 * // what they mean:
        // A - add
        // D - delete
        // E - edit
        // S - list
        // P - print / export
        // I - import
        // M????
 *
 * 
 */
require_once 'DB/DataObject.php';

 
class Pman_Core_DataObjects_Group_rights extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'group_rights';                    // table name
    public $rightname;                       // string(64)  not_null
    public $group_id;                        // int(11)  not_null
    public $accessmask;                      // string(10)  not_null
    public $id;                              // int(11)  not_null primary_key auto_increment

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    
    var $fullRights = "ADESPIM";
    
    
    function groupsWithRights($rightname, $right)
    {
        $t = clone($this);
        $t->rightname = $rightname;
        $t->whereAdd("accessmask like '{$this->escape($right)}'");
        $t->selectAdd();
        $t->selectAdd('distinct(group_id) as group_id');
        return $t->fetchAll('group_id');
         
    }
    
    
    function listPermsFromGroupIds($grps, $isAdmin=false, $isOwner = false) {
        
        $t = clone($this);
        $t->whereAdd('group_id IN ('. implode(',', $grps).')');
        $t->autoJoin();
        $t->find();
        
         $ret = array();
        while($t->fetch()) {
            
           
            
            
            if (isset($ret[$t->rightname])) {
                $ret[$t->rightname] = $this->mergeMask($ret[$t->rightname], $t->accessmask);
                continue;
            }
            $ret[$t->rightname] = $t->accessmask;
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
                    $r[$k] = $v[0] ; // -- it's admin they get rights... can not be disabled..
                    continue;
                }
                // in theory non-owners could sneak in rights here..??
                $r[$k] = $ret[$k];
                continue;
            }
            // not set contition...
            if (!$isOwner) {
                $r[$k] = '';
                continue;
            }
            
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
        
        
        $gid = empty($this->group_id) ? 0 : $this->group_id;
        static $Pman_DataObjects_Group_Right = array();
        
        
        if (!empty($Pman_DataObjects_Group_Right[$gid])) {
            return $Pman_DataObjects_Group_Right[$gid];
        }
        $has_admin = true; ///?? not sure..
        if ($gid) {
            $g = DB_DataObject::factory('groups');
            $g->get($this->group_id);
            $has_admin = $g->type  == 2 ? false : true;
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
            
            if (($module == 'Admin') && !$has_admin) {
                continue;
            }
            
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
        $Pman_DataObjects_Group_Right[$gid] = $ret;
        echo "<PRE>";print_r($gid);exit;
        return $Pman_DataObjects_Group_Right[$gid];
         
        
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
        $g = DB_DataObject::Factory('groups');
        $g->get($this->group_id);
        $defs = $this->defaultPermData();
        switch($g->name) {
            case "Administrators";
                $this->accessmask = $this->mergeMask($this->accessmask, $defs[$this->rightname][0]);
                break;
                
            default:
                //$this->accessmask = $this->mergeMask($this->accessmask, $defs[$this->rightname][1]);
                break;
        
        }
        
    }
    /**
     * generates the default admin group.
     * and returns it.
     */
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
        return $g;
        
    }
        
    function applyDefs($g, $usecol) {
        
        $defs = $this->defaultPermData();
        //echo '<PRE>';print_r($defs);
        //$usecol = 1;
        foreach($defs as $rightname => $defdata) {
            $gr = DB_DataObject::Factory('group_rights');
            $gr->rightname  = $rightname;
            $gr->group_id = $g->id;
            if (!$gr->find(true)) {
                $gr->accessmask = $defdata[$usecol];
                $gr->insert();
                continue;
            }
            $oldgr = clone($gr);
            $gr->accessmask = $gr->mergeMask($gr->accessmask, $defdata[$usecol]);
            if ($gr->accessmask == $oldgr->accessmask) {
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

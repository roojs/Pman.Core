<?php

// neeeds fixing!!!
/**
 * 
 * Part of core!
 * 
 */

require_once 'Pman.php';

class Pman_Core_GroupMembers extends Pman
{
    function getAuth() {
        parent::getAuth(); // load company!
        $au = $this->getAuthUser();
        if (!$au) {
            $this->jerror("LOGIN-NOAUTH", "Not authenticated", array('authFailure' => true));
        }
        if ($au->company()->comptype != 'OWNER') {
            $this->jerr("Permission Denied" );
        }
        $this->authUser = $au;
        return true;
    }
    
     
    function get($v, $opts=array())
    {
        // must recieve a group..
        if (empty($_GET['group_id']) || (int)$_GET['group_id'] < 1) {
            // return empty..
            $this->jdata(array());
            //$this->jerr("NO GROUP");
        }
         if (!$this->hasPerm('Core.Groups', 'S')) {
                $this->jerr("PERMISSION DENIED");
            }
        
        // this is a paging view...
        // does 2 queries - one for users,
        // second just flags if they are members..
        
        // Groups are only usable by owner company!!!
        
        $u = DB_DataObject::factory('core_person');
        $u->company_id = $this->company->id;
        //$this->setFilters($u,$_GET);
        $u->active = 1; // active staff only..
        
        $total = $u->count();
        // build join if req.
        
        
        // sorting..
        
        
        $sort = empty($_REQUEST['sort']) ? '' : $_REQUEST['sort'];
        $dir = (empty($_REQUEST['dir']) || $_REQUEST['dir'] == 'ASC' ? 'ASC' : 'DESC');
        $cols = $u->table();
        if (strlen($sort) && isset($cols[$sort])) {
            $sort = $u->tableName() .'.'.$sort . ' ' . $dir ;
            $u->orderBy($sort );
        } // else other formatas?
        
        
 
        $u->limit(
            empty($_REQUEST['start']) ? 0 : (int)$_REQUEST['start'],
            empty($_REQUEST['limit']) ? 25 : (int)$_REQUEST['limit']
        );
        $u->find();
        $ret = array();
        $e=-1;
        while ($u->fetch()) {
            $ret[$u->id] = array(
                'id'=> $e--, 
                'person_id' => $u->id,                 
                'name' => $u->name , 
                'isMember' => 0
            );
        }
        
        if (!$ret) {
            return $this->jdata($ret,$total);
        }
        
        
        
        $p = DB_DataObject::factory('core_group_member');
        $p->group_id = (int)$_GET['group_id'];
        $p->whereAdd('user_id IN ('. implode(',' ,array_keys($ret) ). ')');
        $p->find();
         
        
        while ($p->fetch()) {
            $ret[$p->user_id]['id'] = $p->id;
            $ret[$p->user_id]['isMember'] = 1;
        }
         
        $this->jdata(array_values($ret),$total);
        
         
    }
    
    function post($v)
    {
        if (empty($_POST['group_id']) || (int)$_POST['group_id'] < 1) {
            $this->jerr("NO GROUP");
        }
        
        if (!$this->hasPerm( 'Core.Groups','E')) { // editing groups..
            $this->jerr("PERMISSION DENIED");
        }
        
        
          // NEW DRAG DROP INTERFACE.
        if (!empty($_POST['action'])) {
            // add
            $ar = explode(',', $_POST['user_ids']);
            $ac = $_POST['action'];
            $g = DB_DataObject::factory('core_group');
            $g->get($_POST['group_id']);
            // check type????
            foreach($ar as $uid) {
                $pi = DB_DataObject::factory('core_person');
                $pi->get($uid);
                    
                $p = DB_DataObject::factory('core_group_member');
                $p->group_id = (int)$_POST['group_id'];
                $p->user_id = $uid;
                
                
                if (($pi->company()->comptype != 'OWNER') && !$g->type) {
                    $this->jerr("can not add non-owner contact to system group");
                }
                
                
                //$p->type = (int)$_POST['type'];
                $p->find(true);
                if (($ac == 'sub') && $p->id) {
                    if ($g->leader == $pi->id) {
                        continue;
                    }
                    $this->addEvent("DELETE", $p, $g->toEventString(). " Removed " . $pi->toEventString());
                    $p->delete();
                    continue;
                }
                if (($ac == 'add') && !$p->id) {
                   
                    $p->insert();
                    $this->addEvent("ADD", $p, $g->toEventString(). " Added " . $pi->toEventString());
                    continue;
                }
                
            }
            $this->jok("OK");
        }
        ///---------------- DEPERCIEATED...
        // add or update..
        if (!empty($_POST['dataDelete'])) {
           
            
            foreach($_POST['dataDelete'] as $id => $ac) {
                $m = DB_DataObject::factory('core_group_member');
                $m->get($id);
                $m->delete();
            }
        }
        
        
        if (!empty($_POST['dataAdd'])) {
             
            foreach($_POST['dataAdd'] as $id => $ac) {
                $p = DB_DataObject::factory('core_group_member');
                $p->group_id = (int)$_POST['group_id'];
                $p->user_id = $id;
                $p->insert();
            }
        }
        $this->jok("done");
        
        
        
    }
     
    
    
    
}
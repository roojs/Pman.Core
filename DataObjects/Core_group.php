<?php
/**
 * Table Definition for Groups
 *
 * group types
 *
 * 0 = permission group..
 * 1 = team
 * 2 = contact group
 *
 *
 *  NOTE - used to be called Groups ....
 *
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_group extends DB_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_group';                          // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $name;                            // string(64)  not_null
    public $type;                            // int(11)
    public $leader;                          // int(11)  not_null
    public $is_system;                       // used by timesheets?

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE


    function personTable()
    {
        $ff = HTML_FlexyFramework::get();
        return empty($ff->Pman['authTable']) ? 'core_person' : $ff->Pman['authTable'];
    }


    // group types??
    function applyFilters($q, $au, $roo)
    {

        if (!empty($q['query']['name_starts'])) {
            $v = $this->escape($q['query']['name_starts']);
            $this->whereAdd("{$this->tableName()}.name like '{$v}%'");
        }
        
        if(!empty($q['_count_member_by_name'])){
            
            $core_group = DB_DataObject::factory('core_group');
            
            if($core_group->get('name', $q['_count_member_by_name'])){
                $roo->jok($core_group->memberCount());
            }
        }
        
        $cp = DB_DataObject::Factory('core_person')->tableName();
        $cgm = DB_DataObject::Factory('core_group_member')->tableName();
        
        $this->selectAdd("
           (
            SELECT COUNT(${cgm}.user_id) 
                FROM 
                    {$cgm}
                LEFT JOIN
                    {$cp}
                ON
                    ${cp}.id = {$cgm}.user_id
                WHERE
                    ${cgm}.group_id = {$this->tableName()}.id
                AND
                    ${cp}.active = 1
                ) AS group_member_count            
        ");
        
    }
    
    function postListExtra($q, $roo)
    {
        print_R($this);exit;
    }

    function toEventString() {
        return $this->name;
    }

    function beforeInsert($q,$roo)
    {
        if (isset($q['_action'])) {
            // add // sub...
            $g = clone($this);
            if (!$g->get($q['group_id'])) {
                $roo->jerr("missing group id");

            }
             foreach(explode(',', $q['user_ids']) as $uid) {
                switch($q['_action']) {
                    case 'add':
                        $g->addMember($uid,$roo);
                        break;
                    case 'sub':
                        $g->removeMember($uid);
                        break;
                    default:
                        $roo->jerr('invalid action');
                }
            }
            $roo->jok('updated');

        }

    }


    function beforeDelete()
    {
        $x = DB_DataObject::factory('core_group_right');
        $x->query("DELETE FROM {$x->tableName()} WHERE group_id = {$this->id}");
        $x = DB_DataObject::factory('core_group_member');
        $x->query("DELETE FROM {$x->tableName()} WHERE group_id = {$this->id}");
    }
    /**
     * check who is trying to access this. false == access denied..
     */
    function checkPerm($lvl, $au)
    {
        return $au->hasPerm("Core.Groups", $lvl);
    }
    function onUpdate($old, $req, $roo)
    {
        $this->ensureLeaderMembership($roo);
    }
    function onInsert($req, $roo)
    {
        $this->ensureLeaderMembership($roo);
    }
    function ensureLeaderMembership($roo)
    {

        // groups - make sure the leader is a member...
        if (!$this->type || !$this->leader)
        {
            return true;
        }

        $pi = DB_DataObject::factory('core_person');
        $pi->get($this->leader);

        $p = DB_DataObject::factory('core_group_member');
        $p->group_id = $this->id;
        $p->user_id = $this->leader;
        //$p->type = 1; //???????
        if (!$p->count()) {

            $p->insert();
            $roo->addEvent("ADD", $p, $this->toEventString(). " Added " . $pi->toEventString());
        }

    }


    function memberCount()
    {
        $gm = DB_Dataobject::factory('core_group_member');
        $gm->group_id = $this->id;
        $gm->autoJoin();
        $gm->whereAdd('join_user_id_id.active = 1');
        //PDO_DAtaObject::DebugLevel(1); 
        return $gm->count();
    }

    function memberIds()
    {
        $gm = DB_Dataobject::factory('core_group_member');
        $gm->group_id = $this->id;
        $gm->autoJoin();
        $gm->whereAdd('join_user_id_id.active = 1');
        return $gm->fetchAll('user_id');

    }
    function isMember($person)
    {
        $gm = DB_Dataobject::factory('core_group_member');
        $gm->group_id = $this->id;
        $gm->user_id = is_object($person) ? $person->id : $person;
        return $gm->count();
    }

    function addMember($person,$roo = false)
    {
        if ($this->name == "Empty Group") {
            $roo->jerr('Cannot add the person into Empty Group');
        }
        $gm = DB_Dataobject::factory('core_group_member');
        $gm->group_id = $this->id;
        $gm->user_id = is_object($person) ? $person->id : $person;
        if (!$gm->count()) {
            $gm->insert();
        }
    }

    function removeMember($person)
    {
        $gm = DB_Dataobject::factory('core_group_member');
        $gm->group_id = $this->id;
        $gm->user_id = is_object($person) ? $person->id : $person;

        if ($gm->find(true)) {
            $gm->delete();
        }
    }

    /**
     *
     *  grab a list of members - default is the array of person objects..
     *  @param $what  = set to 'email' to get a list of email addresses.
     *
     *
     */

    function members($what = false)
    {
        $ids = $this->memberIds();
        if (!$ids) {
            return array();
        }
        //$p = DB_Dataobject::factory(empty($ff->Pman['authTable']) ? 'Person' : $ff->Pman['authTable']);
        // groups databse is hard coded to person.. so this should not be used for other tables.????
        $p = DB_Dataobject::factory( 'core_person' );

        $p->whereAdd('id IN ('. implode(',', $ids) .')');
        $p->active = 1;

        $p->orderBy('name');
        return $p->fetchAll($what);
    }




    function lookup($k,$v = false) {
        if ($v === false) {
            $v = $k;
            $k = 'id';
        }
        $this->get($k,$v);

        return $this;
    }

    function lookUpMembers($name, $what=false)
    {
        if (!$this->get('name', $name)) {
            return array();
        }
        return $this->members($what);

    }

    function lookupMembersByGroupId($id, $what=false)
    {
        if (!$this->get($id)) {
            return array();
        }

        return $this->members($what);
    }

    function postListFilter($ar, $au, $req)
    {
        if(empty($req['_add_everyone'])){
            return $ar;
        }

        $ret[] = array( 'id' => 0, 'name' => 'EVERYONE');
        $ret[] = array( 'id' => -1, 'name' => 'NOT_IN_GROUP');
        return array_merge($ret, $ar);

    }

    function initGroups()
    {
        
        $g = DB_DataObject::factory($this->tableName());
        $g->type = 0;
        $g->name = 'Administrators';
        if ($g->count()) {
            $g->find(true);;
        } else {
            $g->insert();
            $gr = DB_DataObject::factory('core_group_right');
            $gr->genDefault();
        }
        $m = $g->members();
        if (empty($m)) {
            $p = DB_DAtaObject::factory('core_person');
            $p->orderBy('id ASC');
            $p->limit(1);
            if ($p->find(true)) {
                $g->addMember($p);
            }


        }
    }

    function initDatabase($roo, $data)
    {
        $this->initGroups();

        foreach($data as $gi) {
            $g = DB_DataObject::factory($this->tableName());
            
            $o = false;
            
            if($g->get('name', $gi['name'])){
                $o = clone($g);
            }
            
            $display_name = (isset($gi['display_name'])) ? $gi['display_name'] : '';
            
            unset($gi['display_name']);
            
            $g->setFrom($gi);
            
            if(empty($o) || empty($o->display_name)){
                $g->display_name = $display_name;
            }
            
            (empty($o)) ? $g->insert() : $g->update($o);

            if(count($g->members()) || empty($gi['members'])){
                continue;
            }

            foreach ($gi['members'] as $m){
                $g->addMember($m);
            }

        }

    }

}

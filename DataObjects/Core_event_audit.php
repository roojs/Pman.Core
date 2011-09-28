<?php
/**
 * Table Definition for mtrack_change_audit
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_event_audit extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_event_audit';             // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $event_id;                       // int(11)  not_null multiple_key
    public $name;                       // string(128)  
    public $old_audit_id;                        // int(11)  blob
    public $newvalue;                           // blob(65535)  blob

    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    /**
     * get the value, 
     *
     */
    
    function value($event)
    {
        $x = DB_DataObject::factory($event->on_table);
        $ar = $x->links();
        // is the name a link..
        if (!isset($ar[$this->name])) {
            return $this->value;
        }
        if (empty($this->value) ) {
            return '';
        }
        // lr = ProjecT:id
        // get the current value of that...
        $lr = explode(':', $ar[$this->name]);
        $x = DB_DataObject::factory($lr[0]);
        if (!method_exists($x, 'toEventString')) {
            return $lr[0] .':'. $this->value;
        }
        $x->get($this->value);
        
        return $x->toEventString(); // big assumption..
        
        
        
    }
    function oldvalue($cg)
    {
        //var_dump($cg->ontable);
        $x = DB_DataObject::factory($cg->ontable);
        
        $ar = $x->links();
        if ( !isset($ar[$this->field()])) {
            return $this->oldvalue;
        }
        // lr = ProjecT:id
        if (empty($this->oldvalue)) {
            return '';
        }
        $lr = explode(':', $ar[$this->field()]);
        $x = DB_DataObject::factory($lr[0]);
        $x->get($this->oldvalue);
        return isset($x->name) ? $x->name : $this->oldvalue; 
        
    }
    
    function toAuditString($change)
    {
        $field = $this->field();
        switch($field) {
            case 'id':
            case 'created_id':
            
                return false; //??? ignore?
            case '@components':
                return false;
            //  $old = array();
            //  foreach (preg_split("/\s*,\s*/", $old_values[$field]) as $id) {
             /*   if (!strlen($id)) continue;
                $c = get_component($id);
                $old[$id] = $c->name;
              }
              $value = $T->getComponents();
              $field = 'Component';
              break;
             */
            case '@milestones':
                return false;
            //  $old = array();
            //  foreach (preg_split("/\s*,\s*/", $old_values[$field]) as $id) {
              /*  if (!strlen($id)) continue;
                $m = get_milestone($id);
                $old[$id] = $m->name;
              }
              $value = array();
              $value = $T->getMilestones();
              $field = 'Milestone';
              break;
              */
            case '@keywords':
                return false;
            
            default:
                $oldvalue = $this->oldvalue($change);
                $value = $this->value($change);
                
                $field = preg_replace('/_id$/', '', $this->field());
                $field = ucfirst(str_replace('_', ' ', $field));
                
                if ( ($oldvalue == $value)  ||
                    (!strlen($oldvalue) && !strlen($value))) {
                    return false;
                }
                $lb = strpos($oldvalue,"\n") > -1 || strpos($value,"\n") > -1 ? "\n\n" : '';
                $lbs = $lb == '' ? '' : "\n\n---\n\n";
                if (!strlen($oldvalue)) {
                    return " * Set {$field} to: {$lbs}{$value}{$lbs}";
                }
                       
                if (!strlen($value)) {
                    return  " * Removed {$lb}{$field} - was: {$lbs}{$oldvalue}";
                    
                }
                
                return " * Changed {$field} from : {$lbs}{$oldvalue} {$lbs} to {$lbs}{$value}{$lbs}";
                
        }
    }
    
    function toJSONArray($change)
    {
        $ret=  $this->toArray();
        // now add the value strings..
        
        $field = preg_replace('/_id$/', '', $this->field());
        $field = ucfirst(str_replace('_', ' ', $field));
                
        $ret['field_str'] = $field;

        $ret['oldvalue_str'] = $this->oldvalue($change);
        $ret['value_str'] = $this->value($change);
        return $ret;
        
    }
    
    
    
}
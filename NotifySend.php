<?php
require_once 'Pman.php';

/**
 * notification script runner
 *
 * This does not actualy send stuf out, it only starts the NotifySend/{id}
 * which does the actuall notifcations.
 *
 * It manages a pool of notifiers.
 * 
 * 
 */


class Pman_Core_NotifySend extends Pman
{
    
    var $table = 'core_notify';
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        if (!$ff->cli) {
            die("access denied");
        }
        //HTML_FlexyFramework::ensureSingle(__FILE__, $this);
        return true;
        
    }
    
    var $pool = array();
    
    function get($id)    
    {
        DB_DataObject::debugLevel(1);
        //date_default_timezone_set('UTC');
        // phpinfo();exit;
        
        $w = DB_DataObject::factory($this->table);
        
        if (!$w->get($id) || strtotime($w->act_when) < strtotime($w->sent)) {
            die("invalid id or time");
        }
         
        $o = $w->object();
        $p = $w->person();
        
        // let's work out the last notification sent to this user..
        $l = DB_DataObject::factory($this->table);
        $l->setFrom( array(
                'ontable' => $w->ontable,
                'onid' => $w->onid,
                'person_id' => $w->person_id,
        ));        
        $l->whereAdd('id != '. $w->id);
        $l->orderBy('sent DESC');
        $l->limit(1);
        $ar = $l->fetchAll('sent');
        $last = empty($ar) ? date('Y-m-d H:i:s', 0) : $ar[0];
        
        
        $email = $o->toEmail($p,$last);
        // should we fetch the watch that caused it.. - which should contain the method to call..
        $dom = array_pop(explode('@', $p->email));
        
        $mxs = $this->mxs($dom);
        
        
        
        
        Mail::factory('smtp', array( 
            'host'         => 'smtp.gmail.com', 
            'persist'      =>  FALSE
        )); 
        
        
        
         
        die("DONE\n");
    }
    function mxs($fqdn)
    {
        $mx_records = array();
        $mx_weight = array();
        
        if (getmxrr($fqdn, $mx_records, $mx_weight)) {
            // copy mx records and weight into array $mxs
            // ignore multiple mx's at the same weight
            for ($i = 0; $i < count($mx_records); $i++) {
                $mxs[$mx_weight[$i]] = $mx_records[$i];
            }
            // sort array mxs to get servers with highest priority
            ksort ($mxs, SORT_NUMERIC);
            reset ($mxs);
        } else {
            // No MX so use A
            $mxs[0]= $fqdn;
        }
    }
    return $mxs;
    
}
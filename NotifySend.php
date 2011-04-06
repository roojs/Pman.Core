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
        
        // find last event..
        $ev = DB_DataObject::factory('Events');
        $ev->on_id = $w->id;                           // int(11)
        $ev->od_table = $this->table;
        $ev->limit(1);
        $ev->orderBy('event_when DESC');
        $ar = $ev->fetchAll('event_when');
        $last_event = empty($ar) ? 0 : $ar[0];
        $next_try_min = 5;
        if ($last_event) {
            $next_try_min = floor((time() - strtotime($last_event)) / 60) * 2;
        }
        $next_try = $next_try_min . ' MINUTES';
        
        
        $email = $o->toEmail($p,$last);
        // should we fetch the watch that caused it.. - which should contain the method to call..
        $dom = array_pop(explode('@', $p->email));
        
        $mxs = $this->mxs($dom);
        $ww = clone($w);
        
        
        
        
        foreach($mxs as $dom) {
            
            $mailer = Mail::factory('smtp', array( 'host'         => $dom ));
            $res = $mailer->send($email['recipients'], $email['headers'], $email['body']);
            if ($res === true) {
                // success....
                
                $w->sent = date('Y-m-d H:i:s');
                $w->msgid = $email['headers']['Message-Id'];
                $w->event_id = -1; // sent ok.. - no need to record it..
                $w->update($ww);
                die("SENT");
            }
            // what type of error..
            list($code, $response) = $mailer->_smtp->getResponse();
            if ($code < 0) {
                continue; // try next mx... ??? should we wait??? - nope we did not even connect..
            }
            if ($code == 451) {
                // try again later..
                // check last event for this item..
                $this->addEvent('NOTIFY', $w, 'GREYLISTED');
                $w->act_when = date('Y-m-d H:i:s', strtotime('NOW + 5 MINUTES'));
                $w->update($ww);
                die("GREYLISTED");
            }
            // fail.. = log and give up..
            $id = $this->addEvent('NOTIFY', $w, 'FAILED - '. $res->toString());
            $w->sent = date('Y-m-d H:i:s');
            $w->msgid = '';
            $w->event_id = $id;
            $w->update($ww);
            die("DONE");
        }
        
        $this->addEvent('NOTIFY', $w, 'NO HOST CAN BE CONTACTED');
        $w->act_when = date('Y-m-d H:i:s', strtotime('NOW + 5 MINUTES'));
        $w->update($ww);
        die("NO HOST AVAILABLE");

        
    }
    function mxs($fqdn)
    {
        $mx_records = array();
        $mx_weight = array();
        $mxs = array();
        if (!getmxrr($fqdn, $mx_records, $mx_weight)) {
            return araray($fqdn);
        }
        
        asort($mx_weight,SORT_NUMERIC);
        
        forach($mx_weight as $k => $weight) {
            $mxs[] = $mx_records[$k];
        }
        return $mxs;
    }
    
    
}
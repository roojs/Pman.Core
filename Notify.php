<?php
require_once 'MTrackWeb.php';

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

class Pman_Core_Notify extends Pman
{
    
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        if (!$ff->cli) {
            die("access denied");
        }
        HTML_FlexyFramework::ensureSingle(__FILE__, $this);
        return true;
        
    }
    
    var $pool = array();
    
    function get()    
    {
        //DB_DataObject::debugLevel(1);
        date_default_timezone_set('UTC');
        
        
        $w = DB_DataObject::factory('core_notify');
         
        
         
        /* For each watcher, compute the changes.
         * Group changes by ticket, sending one email per ticket.
         * Group tickets into batch updates if the only fields that changed are
         * bulk update style (milestone, assignment etc.)
         *
         * For the wiki repo, group by file so that serial edits within the batch
         * period show up as a single email.
         */
         
    }
}
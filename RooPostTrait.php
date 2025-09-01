<?php

trait Pman_Core_RooPostTrait {
    
    var $old;
    
    /**
     * POST method   Roo/TABLENAME  
     * -- creates, updates, or deletes data.
     *
     * INSERT
     *    if the primary key is empty, this happens
     *    will automatically set these to current date and authUser->id
     *        created, created_by, created_dt
     *        updated, update_by, updated_dt
     *        modified, modified_by, modified_dt
     *        
     *   will return a GET request SINGLE SELECT (and accepts same)
     *    
     * DELETE
     *    _delete=1,2,3     delete a set of data.
     * UPDATE
     *    if the primary key value is set, then update occurs.
     *    will automatically set these to current date and authUser->id
     *        updated, update_by, updated_dt
     *        modified, modified_by, modified_dt
     *        
     *
     * Params:
     *   _delete=1,2,3   causes a delete to occur.
     *   _ids=1,2,3,4    causes update to occur on all primary ids.
     *  
     *  RETURNS
     *     = same as single SELECT GET request..
     *
     *
     *
     * DEBUGGING
     *   _debug=1    forces debug
     *   _get=1 - causes a get request to occur when doing a POST..
     *
     *
     * CALLS
     *   these methods on dataobjects if they exist
     * 
     *   checkPerm('E' / 'D' , $authuser)
     *                      - can we list the stuff
     *                      - return false to disallow...
   
     *   toRooSingleArray($authUser, $request) : array
     *                       - called on single fetch only, add or maniuplate returned array data.
     *   toRooArray($request) : array
     *                      - Called if toSingleArray does not exist.
     *                      - if you need to return different data than toArray..
     *
     *   toEventString()
     *                  (for logging - this is generically prefixed to all database operations.)
     *
     *  
     *   onUpload($roo)
     *                  called when $_FILES is not empty
     *
     *                  
     *   setFromRoo($ar, $roo)
     *                      - alternative to setFrom() which is called if this method does not exist
     *                      - values from post (deal with dates etc.) - return true|error string.
     *                      - call $roo->jerr() on failure...
     *
     * CALLS BEFORE change occurs:
     *  
     *      beforeDelete($dependants_array, $roo, $request)
     *                      Argument is an array of un-find/fetched dependant items.
     *                      - jerr() will stop insert.. (Prefered)
     *                      - return false for fail and set DO->err;
     *                      
     *      beforeUpdate($old, $request,$roo)
     *                      - after update - jerr() will stop insert..
     *      beforeInsert($request,$roo)
     *                      - before insert - jerr() will stop insert..
     *
     *
     * CALLS AFTER change occured
     * 
     *      onUpdate($old, $request,$roo)
     *               - after update // return value ignored
     *
     *      onInsert($request,$roo)
     *                  - after insert
     * 
     *      onDelete($request, $roo) - after delete
     * 
     */                     
     
    function post($tab) // update / insert (?? delete??)
    {   
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'onPearError'));
    
        $this->checkDebug();
        
        if (!empty($_REQUEST['_get'])) {
            return $this->get($tab);
        }
        
        $this->init();
         
        $x = $this->dataObject($tab);
        
        $this->transObj = clone($x);
        
        $this->transObj->query('BEGIN');
        // find the key and use that to get the thing..
        $keys = $x->keys();
        if (empty($keys) ) {
            $this->jerr('no key');
        }
        
        $this->key = $keys[0];
        
          // delete should be here...
        if (isset($_REQUEST['_delete'])) {
            // do we really delete stuff!?!?!?
            return $this->delete($x,$_REQUEST);
        } 
        
        $old = false;
        
        // not sure if this is a good idea here...
        
        if (!empty($_REQUEST['_ids'])) {
            $ids = explode(',',$_REQUEST['_ids']);
            $x->whereAddIn($this->key, $ids, 'int');
            $ar = $x->fetchAll();
            
            foreach($ar as $x) {
                $this->update($x, $_REQUEST);
                
            }
            // all done..
            $this->jok("UPDATED");
            
            
        }
         
        if (!empty($_REQUEST[$this->key])) {
            // it's a create..
            if (!$x->get($this->key, $_REQUEST[$this->key]))  {
                $this->jerr("Invalid request");
            }
            $this->jok($this->update($x, $_REQUEST));
        } else {
            
            if (empty($_POST)) {
                $this->jerr("No data recieved for inserting");
            }
            
            $this->jok($this->insert($x, $_REQUEST));
            
        }
        
        
        
    }
    
    function delete($x, $req)
    {
        // do we really delete stuff!?!?!?
        if (empty($req['_delete'])) {
            $this->jerr("Delete Requested with no value");
        }
        // build a list of tables to queriy for dependant data..
        $map = $x->links();
        
        $affects  = array();
        
        $all_links = $x->databaseLinks();
        
        foreach($all_links as $tbl => $links) {
            foreach($links as $col => $totbl_col) {
                $to = explode(':', $totbl_col);
                if ($to[0] != $x->tableName()) {
                    continue;
                }
                
                $affects[$tbl .'.' . $col] = true;
            }
        }
        // collect tables

       // echo '<PRE>';print_r($affects);exit;
       // DB_Dataobject::debugLevel(1);
       
        if (function_exists('create_function')) {
            $clean = create_function('$v', 'return (int)$v;');
        } else {
            $clean = function($v) {
                return (int)$v;
            };
        }
        
        $bits = array_map($clean, explode(',', $req['_delete']));
        
       // print_r($bits);exit;
         
        // let's assume it has a key!!!
        
        
        $x->whereAdd($this->key .'  IN ('. implode(',', $bits) .')');
        if (!$x->find()) {
            $this->jerr("Nothing found to delete");
        }
        $errs = array();
        while ($x->fetch()) {
            $xx = clone($x);
            
           
            // perms first.
            
            if (!$this->checkPerm($x,'D') )  {
                $this->jerr("PERMISSION DENIED (d)");
            }
            
            $match_ar = array();
            foreach($affects as $k=> $true) {
                $ka = explode('.', $k);
                
                $chk = DB_DataObject::factory($ka[0]);
                if (!is_a($chk,'DB_DataObject')) {
                    $this->jerr('Unable to load referenced table, check the links config: ' .$ka[0]);
                }
               // print_r(array($chk->tablename() , $ka[1] ,  $xx->tablename() , $this->key ));
                $chk->{$ka[1]} =  $xx->{$this->key};
                
                if (count($chk->keys())) {
                    $matches = $chk->count();
                } else {
                    //DB_DataObject::DebugLevel(1);
                    $matches = $chk->count($ka[1]);
                }
                
                if ($matches) {
                    $chk->_match_key = $ka[1];
                    $match_ar[] = clone($chk);
                    continue;
                }          
            }
            
            $has_beforeDelete = method_exists($xx, 'beforeDelete');
            // before delte = allows us to trash dependancies if needed..
            $match_total = 0;
            
            if ( $has_beforeDelete ) {
                if ($xx->beforeDelete($match_ar, $this, $req) === false) {
                    $errs[] = "Delete failed ({$xx->id})\n".
                        (isset($xx->err) ? $xx->err : '');
                    continue;
                }
                // refetch affects..
                
                $match_ar = array();
                foreach($affects as $k=> $true) {
                    $ka = explode('.', $k);
                    $chk = DB_DataObject::factory($ka[0]);
                    if (!is_a($chk,'DB_DataObject')) {
                        $this->jerr('Unable to load referenced table, check the links config: ' .$ka[0]);
                    }
                    $chk->{$ka[1]} =  $xx->{$this->key};
                    $matches = $chk->count();
                    $match_total += $matches;
                    if ($matches) {
                        $chk->_match_key = $ka[1];
                        $match_ar[] = clone($chk);
                        continue;
                    }          
                }
                
            }
            
            if (!empty($match_ar)) {
                $chk = $match_ar[0];
                $chk->limit(1);
                $o = $chk->fetchAll();
                $key = isset($chk->_match_key) ?$chk->_match_key  : '?unknown column?';
                $desc =  $chk->tableName(). '.' . $key .'='.$xx->{$this->key} ;
                if (method_exists($chk, 'toEventString')) {
                    $desc .=  ' : ' . $o[0]->toEventString();
                }
                    
                $this->jerr("Delete Dependant records ($match_total  found),  " .
                             "first is ( $desc )");
          
            }
            
            $this->logDeleteEvent($x);
            
            $xx->delete();
            
            if (method_exists($xx,'onDelete')) {
                $xx->onDelete($req, $this);
            }
            
            
        }
        if ($errs) {
            $this->jerr(implode("\n<BR>", $errs));
        }
        $this->jok("Deleted");
        
    }
    
    function logDeleteEvent($object)
    {
        
        DB_DataObject::Factory('Events')->logDeletedRecord($object);
        
        $this->addEvent("DELETE", $object);
          
        
    }
    
    
    function update($x, $req,  $with_perm_check = true)
    {
        if ( $with_perm_check && !$this->checkPerm($x,'E', $req) )  {
            $this->jerr("PERMISSION DENIED - No Edit permissions on this element");
        }
       
        // check any locks..
        // only done if we recieve a lock_id.
        // we are very trusing here.. that someone has not messed around with locks..
        // the object might want to check in their checkPerm - if locking is essential..
        $lock = $this->updateLock($x,$req);
        
        $old = clone($x);
        $this->old = $x;
        // this lot is generic.. needs moving 
        if (method_exists($x, 'setFromRoo')) {
            $res = $x->setFromRoo($req, $this);
            if (is_string($res)) {
                $this->jerr($res);
            }
        } else {
            $x->setFrom($req);
        }
      
        
        
        //echo '<PRE>';print_r($old);print_r($x);exit;
        //print_r($old);
        
        $cols = $x->table();
        //print_r($cols);
        if (isset($cols['modified'])) {
            $x->modified = date('Y-m-d H:i:s');
        }
        if (isset($cols['modified_dt'])) {
            $x->modified_dt = date('Y-m-d H:i:s');
        }
        if (isset($cols['modified_by']) && $this->authUser) {
            $x->modified_by = $this->authUser->id;
        }
        
        if (isset($cols['updated'])) {
            $x->updated = date('Y-m-d H:i:s');
        }
        if (isset($cols['updated_dt'])) {
            $x->updated_dt = date('Y-m-d H:i:s');
        }
        if (isset($cols['updated_by']) && $this->authUser) {
            $x->updated_by = $this->authUser->id;
        }
        
        if (method_exists($x, 'beforeUpdate')) {
            $x->beforeUpdate($old, $req, $this);
        }
        
        if ($with_perm_check && !empty($_FILES) && method_exists($x, 'onUpload')) {
            $x->onUpload($this, $_REQUEST);
        }
        
        //DB_DataObject::DebugLevel(1);
        $res = $x->update($old);
        if ($res === false) {
            $this->jerr($x->_lastError->toString());
        }
        
        if (method_exists($x, 'onUpdate')) {
            $x->onUpdate($old, $req, $this);
        }
        $ev = $this->addEvent("EDIT", $x);
        if ($ev) { 
            $ev->audit($x, $old);
        }
        
        
        return $this->selectSingle(
            DB_DataObject::factory($x->tableName()),
            $x->{$this->key}
        );
        
    }
    
    function updateLock($x, $req )
    {
        $this->permitError = true; // allow it to fail without dieing
        
        $lock = DB_DataObjecT::factory('core_locking');
        $this->permitError = false; 
        if (is_a($lock,'DB_DataObject') && $this->authUser)  {
                 
            $lock->on_id = $x->{$this->key};
            $lock->on_table= strtolower($x->tableName());
            if (!empty($_REQUEST['_lock_id'])) {
                $lock->whereAdd('id != ' . ((int)$_REQUEST['_lock_id']));
            } else {
                $lock->whereAdd('person_id !=' . $this->authUser->id);
            }
            
            $llc = clone($lock);
            $exp = date('Y-m-d', strtotime('NOW - 1 WEEK'));
            $llc->whereAdd("created < '$exp'");
            if ($llc->count()) {
                $llc->find();
                while($llc->fetch()) {
                    $llcd = clone($llc);
                    $llcd->delete();
                }
            }
            
            $lock->limit(1);
            if ($lock->find(true)) {
                // it's locked by someone else..
               $p = $lock->person();
               
               
               $this->jnotice("LOCKED", "Record was locked by " . $p->name . " at " .$lock->created.
                           " - Please confirm you wish to save" 
                           , array('needs_confirm' => true)); 
          
              
            }
            // check the users lock.. - no point.. ??? - if there are no other locks and it's not the users, then they can 
            // edit it anyways...
            
            // can we find the user's lock.
            $lock = DB_DataObjecT::factory('core_locking');
            $lock->on_id = $x->{$this->key};
            $lock->on_table= strtolower($x->tableName());
            $lock->person_id = $this->authUser->id;
            $lock->orderBy('created DESC');
            $lock->limit(1);
            
            if (
                    $lock->find(true) &&
                    isset($x->modified_dt) &&
                    strtotime($x->modified_dt) > strtotime($lock->created) &&
                    empty($req['_submit_confirmed']) &&
	            $x->modified_by != $this->authUser->id 	
                )
            {
                $p = DB_DataObject::factory('core_person');
                $p->get($x->modified_by);
                $this->jerr($p->name . " saved the record since you started editing,\nDo you really want to update it?", array('needs_confirm' => true)); 
                
            }
        }
        
        return $lock;
        
    }
    
    function insert($x, $req, $with_perm_check = true)
    {   
        if (method_exists($x, 'setFromRoo')) {
            $res = $x->setFromRoo($req, $this);
            if (is_string($res)) {
                $this->jerr($res);
            }
        } else {
            $x->setFrom($req);
        }
        
        if ( $with_perm_check &&  !$this->checkPerm($x,'A', $req))  {
            $this->jerr("PERMISSION DENIED (i)");
        }
        $cols = $x->table();
     
        if (isset($cols['created'])) {
            $x->created = date('Y-m-d H:i:s');
        }
        if (isset($cols['created_dt'])) {
            $x->created_dt = date('Y-m-d H:i:s');
        }
        if (isset($cols['created_by'])) {
            $x->created_by = $this->authUser->id;
        }
        
     
        if (isset($cols['modified'])) {
            $x->modified = date('Y-m-d H:i:s');
        }
        if (isset($cols['modified_dt'])) {
            $x->modified_dt = date('Y-m-d H:i:s');
        }
        if (isset($cols['modified_by'])) {
            $x->modified_by = $this->authUser->id;
        }
        
        if (isset($cols['updated'])) {
            $x->updated = date('Y-m-d H:i:s');
        }
        if (isset($cols['updated_dt'])) {
            $x->updated_dt = date('Y-m-d H:i:s');
        }
        if (isset($cols['updated_by'])) {
            $x->updated_by = $this->authUser->id;
        }
        
        if (method_exists($x, 'beforeInsert')) {
            $x->beforeInsert($_REQUEST, $this);
        }
        
        $res = $x->insert();
        if ($res === false) {
            $this->jerr($x->_lastError->toString());
        }
        if (method_exists($x, 'onInsert')) {
            $x->onInsert($_REQUEST, $this);
        }
        $ev = $this->addEvent("ADD", $x);
        if ($ev) { 
            $ev->audit($x);
        }
        
        // note setFrom might handle this before hand...!??!
        if (!empty($_FILES) && method_exists($x, 'onUpload')) {
            $x->onUpload($this, $_REQUEST);
        }
        
        return $this->selectSingle(
            DB_DataObject::factory($x->tableName()),
            $x->pid()
        );
        
    }
}
<?php

trait Pman_Core_RooPostTrait {
    
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
     *      beforeDelete($dependants_array, $roo)
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
}
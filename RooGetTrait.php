<?php

trait Pman_Core_RooGetTrait {
    /**
     * GET method   Roo/TABLENAME.php
     *
     * Generally for SELECT or Single SELECT
     *
     * Single SELECT:
     *    _id=value          single fetch based on primary id.
     *                       can be '0' if you want to fetch a set of defaults
     *                       Use in conjuntion with toRooSingleArray()
     *                      
     *    lookup[key]=value  single fetch based on a single key value lookup.
     *                       multiple key/value can be used. eg. ontable+onid..
     *    _columns           what to return.
     *
     *    
     * JOINS:
     *  - all tables are always autojoined.
     * 
     * Search SELECT
     *    COLUMNS to fetch
     *      _columns=a,b,c,d     comma seperated list of columns.
     *      _columns_exclude=a,b,c,d   comma seperated list of columns.
     *      _distinct=name        a distinct column lookup. you also have to use _columns with this.
     *
     *    WHERE (searches)
     *       colname = ...              => colname = ....
     *       !colname=....                 => colname != ....
     *       !colname[0]=... !colname[1]=... => colname NOT IN (.....) ** only supports main table at present..
     *       colname[0]=... colname[1]=... => colname IN (.....) ** only supports main table at present..
     *
     *    ORDER BY
     *       sort=name          what to sort.
     *       sort=a,b,d         can support multiple columns
     *       dir=ASC            what direction
     *       _multisort ={...}  JSON encoded { sort : { row : direction }, order : [ row, row, row ] }
     *
     *    LIMIT
     *      start=0         limit start
     *      limit=25        limit number 
     * 
     * 
     *    Simple CSV support
     *      csvCols[0] csvCols[1]....    = .... column titles for CSV output
     *      csvTitles[0], csvTitles[1] ....  = columns to use for CSV output
     *
     *  Depricated  
     *      _toggleActive !:!:!:! - this hsould not really be here..
     *      query[add_blank] - add a line in with an empty option...  - not really needed???
     *      _delete    = delete a list of ids element. (depricated.. this will be removed...)
     * 
     * DEBUGGING
     *  _post   =1    = simulate a post with debuggin on.
     *  _debug_post << This is prefered, as _post may overlap with accouting posts..
     *  
     *  _debug     = turn on DB_dataobject deubbing, must be admin at present..
     *
     *
     * CALLS methods on dataobjects if they exist
     *
     * 
     *   checkPerm('S' , $authuser)
     *                      - can we list the stuff
     *                      - return false to disallow...
     *   applySort($au, $sortcol, $direction, $array_of_columns, $multisort)
     *                     -- does not support multisort at present..
     *   applyFilters($_REQUEST, $authUser, $roo)
     *                     -- apply any query filters on data. and hide stuff not to be seen.
     *                     -- can exit by calling $roo->jerr()
     *   postListExtra($_REQUEST) : array(extra_name => data)
     *                     - add extra column data on the results (like new messages etc.)
     *   postListFilter($data, $authUser, $request) return $data
     *                      - add extra data to an object
     * 
     *   
     *   toRooSingleArray($authUser, $request) : array
     *                       - called on single fetch only, add or maniuplate returned array data.
     *                       - is also called when _id=0 is used (for fetching a default set.)
     *   toRooArray($request) : array
     *                      - called if singleArray is unavailable on single fetch.
     *                      - always tried for mutiple results.
     *   toArray()          - the default method if none of the others are found. 
     *   
     *   autoJoin($request) 
     *                      - standard DataObject feature - causes all results to show all
     *                        referenced data.
     *
     * PROPERTIES:
     *    _extra_cols  -- if set, then filtering by column etc. will use them.
     *
     
     */
    function get($tab)
    {
        $this->init();
        
        HTML_FlexyFramework::get()->generateDataobjectsCache($this->isDev);
        
        if ( $this->checkDebugPost()) {
            $_POST  = $_GET;
            return $this->post($tab);
        }
        
        $this->checkDebug();
        
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'onPearError'));
   
        $tab = array_shift(explode('/', $tab));
        
        $x = $this->dataObject($tab);
        
        $_columns = !empty($_REQUEST['_columns']) ? explode(',', $_REQUEST['_columns']) : false;
        
        if (isset( $_REQUEST['lookup'] ) && is_array($_REQUEST['lookup'] )) { // single fetch based on key/value pairs
             $this->selectSingle($x, $_REQUEST['lookup'],$_REQUEST);
             // actually exits.
        }
        
        
        // single fetch (use '0' to fetch an empty object..)
        if (isset($_REQUEST['_id']) && is_numeric($_REQUEST['_id'])) {
             
             $this->selectSingle($x, $_REQUEST['_id'],$_REQUEST);
             // actually exits.
        }
        
        // Depricated...

       
        if (isset($_REQUEST['_delete'])) {
            $this->jerr("DELETE by GET has been removed - update the code to use POST");
            /*
            
            $keys = $x->keys();
            if (empty($keys) ) {
                $this->jerr('no key');
            }
            
            $this->key = $keys[0];
            
            
            // do we really delete stuff!?!?!?
            return $this->delete($x,$_REQUEST);
            */
        } 
        
        
        // Depricated...
        
        if (isset($_REQUEST['_toggleActive'])) {
            // do we really delete stuff!?!?!?
            if (!$this->hasPerm("Core.Staff", 'E'))  {
                $this->jerr("PERMISSION DENIED (ta)");
            }
            $clean = create_function('$v', 'return (int)$v;');
            $bits = array_map($clean, explode(',', $_REQUEST['_toggleActive']));
            if (in_array($this->authUser->id, $bits) && $this->authUser->active) {
                $this->jerr("you can not disable yourself");
            }
            $x->query('UPDATE Person SET active = !active WHERE id IN (' .implode(',', $bits).')');
            $this->addEvent("USERTOGGLE", false, implode(',', $bits));
            $this->jok("Updated");
            
        }
       //DB_DataObject::debugLevel(1);
       
        
        // sets map and countWhat
        $this->loadMap($x, array(
                    'columns' => $_columns,
                    'distinct' => empty($_REQUEST['_distinct']) ? false:  $_REQUEST['_distinct'],
                    'exclude' => empty($_REQUEST['_exclude_columns']) ? false:  explode(',', $_REQUEST['_exclude_columns'])
            ));
        
        
        $this->setFilters($x,$_REQUEST);
        
        if (!$this->checkPerm($x,'S', $_REQUEST))  {
            $this->jerr("PERMISSION DENIED (g)");
        }
        
         //print_r($x);
        // build join if req.
          //DB_DataObject::debugLevel(1);
       //   var_dump($this->countWhat);
        $total = $x->count($this->countWhat);
        // sorting..
      //   
        //var_dump($total);exit;
        $this->applySort($x);
        
        $fake_limit = false;
        
        if (!empty($_REQUEST['_distinct']) && $total < 400) {
            $fake_limit  = true;
        }
        
        if (!$fake_limit) {
 
            $x->limit(
                empty($_REQUEST['start']) ? 0 : (int)$_REQUEST['start'],
                min(empty($_REQUEST['limit']) ? 25 : (int)$_REQUEST['limit'], 10000)
            );
        } 
        $queryObj = clone($x);
        //DB_DataObject::debuglevel(1);
        
        $this->sessionState(0);
        $res = $x->find();
        $this->sessionState(1);
        
        if (false === $res) {
            $this->jerr($x->_lastError->toString());
            
        }
        
        
        
        $ret = array();
        
        // ---------------- THESE ARE DEPRICATED.. they should be moved to the model...
        
        
        if (!empty($_REQUEST['query']['add_blank'])) {
            $ret[] = array( 'id' => 0, 'name' => '----');
            $total+=1;
        }
         
        $rooar = method_exists($x, 'toRooArray');
        $_columnsf = $_columns  ? array_flip($_columns) : false;
        while ($x->fetch()) {
            //print_R($x);exit;
            $add = $rooar  ? $x->toRooArray($_REQUEST) : $x->toArray();
            if ($add === false) {
                continue;
            }
            $ret[] =  !$_columns ? $add : array_intersect_key($add, $_columnsf);
        }
        
        if ($fake_limit) {
            $ret = array_slice($ret,
                   empty($_REQUEST['start']) ? 0 : (int)$_REQUEST['start'],
                    min(empty($_REQUEST['limit']) ? 25 : (int)$_REQUEST['limit'], 10000)
            );
            
        }
        
        
        $extra = false;
        if (method_exists($queryObj ,'postListExtra')) {
            $extra = $queryObj->postListExtra($_REQUEST, $this);
        }
        
        
        // filter results, and add any data that is needed...
        if (method_exists($x,'postListFilter')) {
            $ret = $x->postListFilter($ret, $this->authUser, $_REQUEST);
        }
        
        
        
        if (!empty($_REQUEST['csvCols']) && !empty($_REQUEST['csvTitles']) ) {
            
            
            $this->toCsv($ret, $_REQUEST['csvCols'], $_REQUEST['csvTitles'],
                        empty($_REQUEST['csvFilename']) ? '' : $_REQUEST['csvFilename']
                         );
            
            
        
        }
        //die("DONE?");
      
        //if ($x->tableName() == 'Documents_Tracking') {
        //    $ret = $this->replaceSubject(&$ret, 'doc_id_subject');
       // }
        
        
        
        if (!empty($_REQUEST['_requestMeta']) &&  count($ret)) {
            $meta = $this->meta($x, $ret);
            if ($meta) {
                $extra['metaData'] = $meta;
            }
        }
        // this make take some time...
        $this->sessionState(0);
       // echo "<PRE>"; print_r($ret);
        $this->jdata($ret, max(count($ret), $total), $extra );

    
    }
    
    function checkDebugPost()
    {
        return (!empty($_GET['_post']) || !empty($_GET['_debug_post'])) && 
                    $this->authUser && 
                    method_exists($this->authUser,'groups') &&
                    in_array('Administrators', $this->authUser->groups('name')); 
        
    }
    
    function selectSingle($x, $id, $req=false)
    {
        $_columns = !empty($req['_columns']) ? explode(',', $req['_columns']) : false;

        if (!is_array($id) && empty($id)) {
            
            if (method_exists($x, 'toRooSingleArray')) {
                $this->jok($x->toRooSingleArray($this->getAuthUser(), $req));
            }
            
            if (method_exists($x, 'toRooArray')) {
                $this->jok($x->toRooArray($req));
            }
            
            $this->jok($x->toArray());
        }
       
        
        $this->loadMap($x, array(
            'columns' => $_columns,
        ));
        
        if ($req !== false) { 
            $this->setFilters($x, $req);
        }
        
        if (is_array($id)) {
            // lookup...
            $x->setFrom($req['lookup'] );
            $x->limit(1);
            if (!$x->find(true)) {
                if (!empty($id['_id'])) {
                    // standardize this?
                    $this->jok($x->toArray());
                }
                $this->jok(false);
            }
            
        } else if (!$x->get($id)) {
            $this->jerr("selectSingle: no such record ($id)");
        }
        
        // ignore perms if comming from update/insert - as it's already done...
        if ($req !== false && !$this->checkPerm($x,'S'))  {
            $this->jerr("PERMISSION DENIED - si");
        }
        // different symantics on all these calls??
        if (method_exists($x, 'toRooSingleArray')) {
            $this->jok($x->toRooSingleArray($this->authUser, $req));
        }
        if (method_exists($x, 'toRooArray')) {
            $this->jok($x->toRooArray($req));
        }
        
        $this->jok($x->toArray());
        
        
    }
    
    function setFilters($x, $q)
    {
        if (method_exists($x, 'applyFilters')) {
           // DB_DataObject::debugLevel(1);
            if (false === $x->applyFilters($q, $this->getAuthUser(), $this)) {
                return; 
            } 
        }
        $q_filtered = array();
        
        $keys = $x->keys();
        // var_dump($keys);exit;
        foreach($q as $key=>$val) {
            
            if (in_array($key,$keys) && !is_array($val)) {
               
                $x->$key  = $val;
            }
            
             // handles name[]=fred&name[]=brian => name in ('fred', 'brian').
            // value is an array..
            if (is_array($val) ) {
                
                $pref = '';
                
                if ($key[0] == '!') {
                    $pref = '!';
                    $key = substr($key,1);
                }
                
                if (!in_array( $key,  array_keys($this->cols))) {
                    continue;
                }
                
                // support a[0] a[1] ..... => whereAddIn(
                $ar = array();
                $quote = false;
                foreach($val as $k=>$v) {
                    if (!is_numeric($k)) {
                        $ar = array();
                        break;
                    }
                    // FIXME: note this is not typesafe for anything other than mysql..
                    
                    if (!is_numeric($v) || !is_long($v)) {
                        $quote = true;
                    }
                    $ar[] = $v;
                    
                }
                if (count($ar)) {
                    
                    
                    $x->whereAddIn($pref . (
                        isset($this->colsJname[$key]) ? 
                            $this->colsJname[$key] :
                            ($x->tableName(). '.'.$key)),
                        $ar, $quote ? 'string' : 'int');
                }
                
                continue;
            }
            
            
            // handles !name=fred => name not equal fred.
            if ($key[0] == '!' && in_array(substr($key, 1), array_keys($this->cols))) {
                
                $key  = substr($key, 1) ;
                
                $x->whereAdd(   (
                        isset($this->colsJname[$key]) ? 
                            $this->colsJname[$key] :
                            $x->tableName(). '.'.$key ) . ' != ' .
                    (is_numeric($val) ? $val : "'".  $x->escape($val) . "'")
                );
                continue;
                
            }

            switch($key) {
                    
                // Events and remarks -- fixme - move to events/remarsk...
                case 'on_id':  // where TF is this used...
                    if (!empty($q['query']['original'])) {
                      //  DB_DataObject::debugLevel(1);
                        $o = (int) $q['query']['original'];
                        $oid = (int) $val;
                        $x->whereAdd("(on_id = $oid  OR 
                                on_id IN ( SELECT distinct(id) FROM Documents WHERE original = $o ) 
                            )");
                        continue;
                                
                    }
                    $x->on_id = $val;
                
                
                default:
                    if (strlen($val) && $key[0] != '_') {
                        $q_filtered[$key] = $val;
                    }
                    
                    // subjoined columns = check the values.
                    // note this is not typesafe for anything other than mysql..
                    
                    if (isset($this->colsJname[$key])) {
                        $quote = false;
                        if (!is_numeric($val) || !is_long($val)) {
                            $quote = true;
                        }
                        $x->whereAdd( "{$this->colsJname[$key]} = " . ($quote ? "'". $x->escape($val) ."'" : $val));
                        
                    }
                    
                    
                    continue;
            }
        }
        if (!empty($q_filtered)) {
            $x->setFrom($q_filtered);
        }
        
        if (!empty($q['query']['name'])) {
            if (in_array( 'name',  array_keys($x->table()))) {
                $x->whereAdd($x->tableName().".name LIKE '". $x->escape($q['query']['name']) . "%'");
            }
        }
        
    }
}

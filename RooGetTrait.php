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
   
        $explode_tab = explode('/', $tab);
        $tab = array_shift($explode_tab);
        
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
        
        if (!empty($_REQUEST['_requestMeta']) &&  count($ret)) {
            $meta = $this->meta($x, $ret);
            if ($meta) {
                $extra = $extra ? $extra: array();
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
    
    function applySort($x, $sort = '', $dir ='')
    {
        $sort = empty($_REQUEST['sort']) ? $sort : $_REQUEST['sort'];
        $dir = empty($_REQUEST['dir']) ? $dir : $_REQUEST['dir'];
        $dir = $dir == 'ASC' ? 'ASC' : 'DESC';
         
        $ms = empty($_REQUEST['_multisort']) ? false : $_REQUEST['_multisort'];
        //var_Dump($ms);exit;
        $sorted = false;
        if (method_exists($x, 'applySort')) {
            $sorted = $x->applySort(
                    $this->authUser,
                    $sort,
                    $dir,
                    array_keys($this->cols),
                    $ms ? json_decode($ms) : false
            );
        }
        if ($ms !== false) {
            return $this->multiSort($x);
        }
        
        if ($sorted === false) {
            
            $cols = $x->table();
            $excols = array_keys($this->cols);
            //print_R($excols);
            
            if (isset($x->_extra_cols)) {
                $excols = array_merge($excols, $x->_extra_cols);
            }
            $sort_ar = explode(',', $sort);
            $sort_str = array();
          
            foreach($sort_ar as $sort) {
                
                if (strlen($sort) && isset($cols[$sort]) ) {
                    $sort_str[] =  $x->tableName() .'.'.$sort . ' ' . $dir ;
                    
                } else if (in_array($sort, $excols)) {
                    $sort_str[] = $sort . ' ' . $dir ;
                }
            }
             
            if ($sort_str) {
                $x->orderBy(implode(', ', $sort_str ));
            }
        }
    }
    
    function toCsv($data, $cols, $titles, $filename, $addDate = true)
    {
        $this->sessionState(0); // turn off sessions  - no locking..

        require_once 'Pman/Core/SimpleExcel.php';
        
        $fn = (empty($filename) ? 'list-export-' : urlencode($filename)) . (($addDate) ? date('Y-m-d') : '') ;
        
        
        $se_config=  array(
            'workbook' => substr($fn, 0, 31),
            'cols' => array(),
            'leave_open' => true
        );
        
        
        $se = false;
        if (is_object($data)) {
            $rooar = method_exists($data, 'toRooArray');
            while($data->fetch()) {
                $x = $rooar  ? $data->toRooArray($q) : $data->toArray();
                
                
                if ($cols == '*') {  /// did we get cols sent to us?
                    $cols = array_keys($x);
                }
                if ($titles== '*') {
                    $titles= array_keys($x);
                }
                if ($titles !== false) {
                    
                    foreach($cols as $i=>$col) {
                        $se_config['cols'][] = array(
                            'header'=> isset($titles[$i]) ? $titles[$i] : $col,
                            'dataIndex'=> $col,
                            'width'=>  100
                        );
                         $se = new Pman_Core_SimpleExcel(array(), $se_config);
       
                        
                    }
                    
                    
                    //fputcsv($fh, $titles);
                    $titles = false;
                }
                

                $se->addLine($se_config['workbook'], $x);
                    
                
            }
            if(!$se){
                
                $this->jerr('no data found', false, 'text/plain');
            }
            $se->send($fn .'.xls');
            exit;
            
        } 
        
        
        foreach($data as $x) {
            //echo "<PRE>"; print_r(array($_REQUEST['csvCols'], $x->toArray())); exit;
            $line = array();
            if ($titles== '*') {
                $titles= array_keys($x);
            }
            if ($cols== '*') {
                $cols= array_keys($x);
            }
            if ($titles !== false) {
                foreach($cols as $i=>$col) {
                    $se_config['cols'][] = array(
                        'header'=> isset($titles[$i]) ? $titles[$i] : $col,
                        'dataIndex'=> $col,
                        'width'=>  100,
                       //     'renderer' => array($this, 'getThumb'),
                         //   'color' => 'yellow', // set color for the cell which is a header element
                          // 'fillBlank' => 'gray', // set 
                    );
                    $se = new Pman_Core_SimpleExcel(array(),$se_config);
   
                    
                }
                
                
                //fputcsv($fh, $titles);
                $titles = false;
            }
            
            
            
            $se->addLine($se_config['workbook'], $x);
        }
        if(!$se){
            $this->jerr('no data found');
        }
        $se->send($fn .'.xls');
        exit;
        
    }
    
    function meta($x, $data)
    {
        $lost = 0;
        $cols  = array_keys($data[0]);
     
        $options = &PEAR::getStaticProperty('DB_DataObject','options');
        $reader = $options["ini_{$x->databaseNickname()}"] .'.reader';
        if (!file_exists( $reader )) {
            return;
        }
        
        $rdata = unserialize(file_get_contents($reader));
        
        $keys = $x->keys();
        $key = empty($keys) ? 'id' : $keys[0];
        
        
        $meta = array();
        foreach($cols as $c ) {
            if (!isset($this->cols[$c]) || !isset($rdata[$this->cols[$c]]) || !is_array($rdata[$this->cols[$c]])) {
                $meta[] = $c;
                continue;    
            }
            $add = $rdata[$this->cols[$c]];
            $add['name'] = $c;
            $meta[] = $add;
        }
        return array(
            'totalProperty' =>  'total',
            'successProperty' => 'success',
            'root' => 'data',
            'id' => $key, // was 'id'...
            'fields' => $meta
        );
         
        
    }
    
    function multiSort($x)
    {
        $ms = json_decode($_REQUEST['_multisort']);
        if (!isset($ms->order) || !is_array($ms->order)) {
            return;
        }
        $sort_str = array();
        
        $cols = $x->table();
        
        foreach($ms->order  as $col) {
            if (!isset($ms->sort->{$col})) {
                continue; // no direction..
            }
            $ms->sort->{$col} = $ms->sort->{$col}  == 'ASC' ? 'ASC' : 'DESC';
            
            if (strlen($col) && isset($cols[$col]) ) {
                $sort_str[] =  $x->tableName() .'.'.$col . ' ' .  $ms->sort->{$col};
                continue;
            }
            
            if (in_array($col, array_keys($this->cols))) {
                $sort_str[] = $col. ' ' . $ms->sort->{$col};
                continue;
            }
            if (isset($x->_extra_cols) && in_array($col, $x->_extra_cols)) {
                $sort_str[] = $col. ' ' . $ms->sort->{$col};
            }
        }
         
        if ($sort_str) {
            $x->orderBy(implode(', ', $sort_str ));
        }
    }
}

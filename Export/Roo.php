<?php

require_once 'Pman/Roo.php'
;
class Pman_Core_Export_Roo extends Pman_Roo {
    
     static $cli_desc = "Export a roo query to a files  use like url Core/Export/Roo/Core_enum  (CSV)"; 
    
    static $cli_opts = array(
        'file' => array(
            'desc' => 'File to export to. (absolute path)',
            'short' => 'f',
            'min' => 1,
            'max' => 1,
            
        ),
        'query' => array(
            'desc' => 'argument.. eg. --query sort=id&direction=ASC&parent_id=1  ',
            'short' => 'q',
            'default' => '',
            'min' => 0,
            'max' => 1,
            
        ),
        'user' => array(
            'desc' => 'user to log in as..',
            'short' => 'u',
            'default' => '',
            'min' => 1,
            'max' => 1,
            
        ),
        
    );
    
    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();
        
        if (!$ff->cli) {
            die("cli only");
        }
       
        
    } 
    
    
    function post($v)
    {
        die("should not happen!");
    }
    
   
    function get($tab, $opts = Array())
    {
  
        if ($opts['file'][0] != '/') {
            $this->jerr("file must be an absolute path ");
        }
        
        $args = array();
        if (!empty($opts['query'])) {
            parse_str($opts['query'], $args);
        }
        
        $this->authUser = DB_DAtaObject::Factory('Person');
        if (!$this->authUser->get('email', $opts['user'])) {
            $this->jerr("user count not be found: " . $opts['user']);
            
        }
        
         
        //$this->init(); // from pman.
        //DB_DataObject::debuglevel(1);
        //HTML_FlexyFramework::get()->generateDataobjectsCache($this->isDev);
        
   
        
        // debugging...
        
        if (!empty($args['_debug'])) {
            DB_DAtaObject::debuglevel((int)$args['_debug']);
        }
        
         
        
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'onPearError'));
   
        $tab = array_shift(explode('/', $tab));
        $x = $this->dataObject($tab);
        
        $_columns = !empty($args['_columns']) ? explode(',', $args['_columns']) : false;
        
        
        
        // sets map and countWhat
        $this->loadMap($x, array(
                    'columns' => $_columns,
                    'distinct' => empty($args['_distinct']) ? false:  $args['_distinct'],
                    'exclude' => empty($args['_exclude_columns']) ? false:  explode(',', $args['_exclude_columns'])
            ));
        
        
        $this->setFilters($x,$args);
        
         
        $total = $x->count($this->countWhat);
        // sorting..
      //   
        //var_dump($total);exit;
        $this->applySort($x);
        
        
 
        $x->limit(
            empty($args['start']) ? 0 : (int)$args['start'],
            min(empty($args['limit']) ? 25 : (int)$args['limit'], 10000000) // we can handle alot at the command line...
        );
        
        $queryObj = clone($x);
        //DB_DataObject::debuglevel(1);
        
        
        $res = $x->find();
       
        if (false === $res) {
            $this->jerr($x->_lastError->toString());
            
        }
        
        
        
        $ret = array();
       
        $rooar = method_exists($x, 'toRooArray');
        $_columnsf = $_columns  ? array_flip($_columns) : false;
        while ($x->fetch()) {
            //print_R($x);exit;
            $add = $rooar  ? $x->toRooArray($args) : $x->toArray();
            if ($add === false) {
                continue;
            }
            $ret[] =  !$_columns ? $add : array_intersect_key($add, $_columnsf);
        }
        
        
        
        $extra = false;
        if (method_exists($queryObj ,'postListExtra')) {
            $extra = $queryObj->postListExtra($args, $this);
        }
        
        
        // filter results, and add any data that is needed...
        if (method_exists($x,'postListFilter')) {
            $ret = $x->postListFilter($ret, $this->authUser, $args);
        }
        
        $args['csvCols'] = isset($args['csvCols']) ? $args['csvCols'] : '*';
        $args['csvTitles'] = isset($args['csvTitles']) ? $args['csvTitles'] : '*';
            
        
        $this->toCsv($ret, $args['csvCols'], $args['csvTitles'],  $opts['file']  );
        $this->jerr("we should not get here...");
        
        
        
        
    }
    function checkDebug()
    {
        
    
        
    }
    // min requirement is just to list csvCols ...
    
    function toCsv($data, $cols, $titles, $filename, $addDate = true)
    {
          
        $this->sessionState(0); // turn off sessions  - no locking..

        $fh = fopen($filename,'w');
                    
        
        foreach($data as $x) {
            //echo "<PRE>"; print_r(array($_REQUEST['csvCols'], $x->toArray())); exit;
            $line = array();
            if ($titles== '*') {
                $titles = $cols== '*' ? array_keys($x) : $cols;
            }
            if ($cols== '*') {
                $cols= array_keys($x);
            }
            if ($titles !== false) {
                fputcsv($fh,$titles);
                
                
                //fputcsv($fh, $titles);
                $titles = false;
            }
            $ar = array();
            foreach($cols as $c) {
                $ar[] = $x[$c];
            }
            fputcsv($fh,$ar);
            
            
        }
        
        fclose($fh);
        $this->jok("Wrote file : " . $filename);
        exit;
    
        
        
    }
    
    
}

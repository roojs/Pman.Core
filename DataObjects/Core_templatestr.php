<?php
/**
 * Table Definition for core_templatestr
 *
 *
 * The idea here is that it contains all the strings in the templates with a language '' (empty)
 * , it then generates a matching set of strings
 * 
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_templatestr extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_templatestr';         // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $template_id;                           // string(64)  not_null
    public $txt;                    // datetime(19)  multiple_key binary
    public $updated;                        // blob(65535)  blob
    public $src_id;                          // int(11)  not_null
    public $lang;    // text  NOT NULL;
    public $mdsum;    // text  NOT NULL;
    public $active;
    public $on_table;
    public $on_id;
    public $on_col;
 
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function beforeInsert($q,$roo)
    {
        if(!empty($q['_rescan'])){
            $this->syncLang($q['_rescan']);
            $roo->jok('OK');
        }
    }
    
    
    function applyFilters($q, $au, $roo)
    {
        if (!empty($q['_tree'])) {
            $this->applyFiltersTree($q,$roo);
        }
        
        if(!empty($q['on_table']) && (empty($q['template_id']) || !is_numeric($q['template_id']))){
            $this->template_id = 0;
             $this->whereAdd("  join_src_id_id.txt != ''");
        }
        if (!empty($q['_search_txt'])) {
            $str = $this->escape($q['_search_txt']);
            $this->whereAdd("core_templatestr.txt like '%{$str}%' OR join_src_id_id.txt like '%{$str}%'");
            
        }
    }
    function translateTableCol($obj, $col, $lang)
    {
        $cts = DB_DataObject::factory('core_templatestr');
        $cts->lang = $lang;
        $cts->on_table = $obj->tableName();
        $cts->on_id = $obj->pid();
        $cc = clone($cts);
        if(!$cts->find(true)){
            return $obj->$col;
        }
        
        
        if(empty($cts->txt)){
            return $obj->$col;
        }
            
        return $cts->txt;
    }
    /**
     *
     * insert the origanal table text
     * 
     * @param type $roo
     * @param type $obj
     * @param type $chg
     * @return type 
     */
    function onTableChange($roo, $obj, $chg)
    {
        
        $ff = HTML_FlexyFramework::get()->Pman_Core;
            
        if(empty($ff['DataObjects_Core_templatestr']['tables'])){
            return;
        }
        $tn = $obj->tableName();
        if(empty($ff['DataObjects_Core_templatestr']['tables'][$tn])){
            return;
        }
        $cols = $ff['DataObjects_Core_templatestr']['tables'][$tn];
        
        
        foreach($cols as $c) {
            $x = $this->factory($this->tableName());
            if(strpos($c, ',') !== false) {
                $arr = explode(',', $c);
                $c = $arr[0];
                $cond = $arr[1];

                $ar = explode('=', $cond);
                $key = $ar[0];
                $x->$key = $ar[1];
                var_dump($x);
                die('b');
            }
            $x->on_id = $obj->pid();
            $x->on_table = $tn;
            $x->on_col = $c;
            $x->lang = ''; /// eg. base language..
            $up = $x->find(true);
            if ($up && $x->txt == $obj->$c) {
                continue; // update an no change..
            }
            $x->active = 1;
            $x->src_id = 0;
            $x->txt = $obj->$c;
            $x->mdsum = md5($obj->$c);
            $x->template_id = 0;
            $x->updated = date('Y-m-d H:i:s', strtotime("NOW"));
            $up ? $x->update() : $x->insert();
        }
        
        
    }
    
    
    function applyFiltersTree($q,$roo)
    {
        if (empty($q['node'])) {
            $roo->jerr("invalid node");
        }
        switch(true) {
            
            case ($q['node'] == 'transtree'):
            case ($q['node'] == 'langlist'):
               // DB_DataObject::debugLevel(1);
                $x = DB_Dataobject::Factory($this->tableName());
                $x->selectAdd();
                $x->selectAdd('distinct(lang) as lang');
                
                $x->selectAdd("i18n_translate('l', lang, 'en') as lang_name");
                $x->whereAdd("lang != ''");
                $ret= array();
                foreach( $x->fetchAll() as $l) {
                    $ret[] = array(
                        'text'=>$l->lang_name,
                        'id' => $q['node'] == 'langlist' ? $l->lang : 'lang:'.$l->lang ,
                        'language' => true
                    );
                }
                if (empty($ret)) {
                    $ret[] = array(
                        'text'=>'English',
                        'id' => 'lang:en',
                        'language' => true
                    );
                }
                $roo->jdata($ret);
            
            case  preg_match('/^lang:/', $q['node']):
                
                $lang = preg_replace('/^lang:/', '', $q['node']);
                $ret= array();  
                $x = DB_DataObject::factory('core_templatestr');
                $x->autoJoin();
                $x->selectAdd();
                $x->selectAdd('distinct(view_name) as view_name');
                $x->lang = $lang;
                $x->whereAdd('join_template_id_id.is_deleted = 0');
                $x->orderBy('view_name DESC');
                $x->find();
                while($x->fetch()) {
                    $ret[] = array( 
                        'text'=> $x->view_name,
                        'id' => 'view:'. $lang .':'.  $x->view_name,
                        'leaf' => false
                    );
                }

                $ff = HTML_FlexyFramework::get()->Pman_Core;

                if(!empty($ff['DataObjects_Core_templatestr']['tables'])){
                    foreach($ff['DataObjects_Core_templatestr']['tables'] as $table=>$v){
                        $ret[] = array(
                            'text'=> $table,
                            'on_table' => $table,
                            'id' => 'table:'. $lang .':'. $table,
                            'leaf' => true
                        );
                    }
                }
                
                 
                $roo->jdata($ret);
                
                break;
                
                
            case  preg_match('/^view:/', $q['node']):
                
               
                $bits= explode(":",preg_replace('/^view:/', '', $q['node']));
                
                $x = DB_DataObject::factory($this->tableName());
                $x->autoJoin();
                $x->selectAdd();
                $x->selectAdd('distinct(core_templatestr.template_id) as template_id');
                $x->whereAdd("join_template_id_id.view_name = '{$x->escape($bits[1])}'");
                $x->whereAdd('join_template_id_id.is_deleted = 0');
                $x->lang = $bits[0];
                $ids = $x->fetchAll('template_id');
                
                $ret= array();
                //add the table type lists
                /*
                $ff = HTML_FlexyFramework::get()->Pman_Core;
                
                
//                $x->orderBy('template ASC');
//                $x->whereAdd("lang != ''");
                */
                //below are old code
                $xx = DB_Dataobject::Factory('core_template');
                $xx->whereAddIn('id', $ids, 'int');
                $xx->selectAdd();
                $xx->selectAdd("
                               
                    id, concat(  template) as template_name
                ");
                $xx->orderBy('template_name ASC');
                
                foreach( $xx->fetchAll('id', 'template_name') as $l =>$n) {
                    $ret[] = array(
                        'text'=> $n,
                        'id' => $l,
                        'leaf' => true
                    );
                }
                
                $roo->jdata($ret);
                 break;
        }
                
        
        
    }
    
    
    /**
     *
     * 
     * @param object $tmpl core_template data object
     * @param array $words array of words 
     */ 
    function syncTemplateWords($tmpl, $keyvalue = false)
    {
        
        $words = $tmpl->words;
        // mapping for template : 
        //tablename => $n (templatename) 
        //tableid => $k (key value)
        //colname => $n (templatename) 
        // mdsum => md5(sum)
        //
        //print_r($words);exit;
        // grab original
        $tt = DB_DataObject::factory($this->tableName());
 
        $t = clone($tt);
        $t->template_id = $tmpl->id;
        $t->whereAdd("lang = ''");
        
        
        // we have a situation where old md sums where created..
        
        
        
        $cur = $t->fetchAll('mdsum', 'id'); 
        
        
        
        
        
        
        
        // now loop through current..
        $cwords = array();// not in used??
        $active = array();
//        echo "sync Template Words... \n";
//        print_r($words);
        
        foreach($words as $k=>$v) {
            
            
            $v = trim($v);
            
            $md = $keyvalue ? $k : md5($v);
            
            // check to see if there are more that one versions of that md5
            
            
            if (!isset($cur[$md])) {
                // create a record for it..
                $t = DB_DataObject::factory($this->tableName());
                $t->setFrom(array(
                    'txt' => $v,
                    'lang' => '',// by default should a english 
                    'updated' => date('Y-m-d H:i:s', strtotime("YESTERDAY")),
                    'template_id'=>$tmpl->id,
                    'mdsum' => $md,
                    'src_id' => 0,
                    'active' => 1,
                ));
                $active[] =  $t->insert();
                continue;
            }  
            $cur[$md] = $this->checkDupes($tmpl->id, '',  $cur[$md] , $md);
            
            $active[] = $cur[$md];
            
            // we have it already? - 
            $tt->query("UPDATE {$this->tableName()}
                        SET active= 1
                        WHERE
                        id = ".$cur[$md]);
            unset($cur[$md]);
            
        }
        // delete unused.
        
        
        

        $deactive = array();
        if (count(array_values($cur))) {// de-active unused

            $t = DB_DataObject::factory($this->tableName());
//            echo "de-active current?? \n";
//            print_r($cur);
//            echo "\n";
            $deactive = array_values($cur);
            $t->query("UPDATE core_templatestr
                      SET active = 0 WHERE id in (" . implode(',' ,$deactive) . ")
                     ");
        }
        
        // delete all the items that are not relivant.
        // clear orphaned chidren - it just blanks out the src id, so they can be used as suggestions..?
        // this does not help - as it just puts random strings in there.. - with no reference to the original text..
        $t = DB_DataObject::factory($this->tableName());
    
        // this will active the child data
        if (empty($active)) {// set the active array to empty
            $active = array(-1);
        }
        $t->query("UPDATE  core_templatestr 
                SET active = 1
                  WHERE
                     src_id IN (". implode(',' ,$active) . ")
                    AND
                    template_id = {$tmpl->id}
        ");
        //deactive the child data
        if (empty($deactive)) {
            $deactive = array(-1);
        }
        $t->query("UPDATE  core_templatestr 
                SET active = 0
                  WHERE
                    src_id IN (". implode(',' ,$deactive) . ")
                    AND
                    template_id = {$tmpl->id}
                    AND
                    lang != ''
        ");
    }
    
    function checkDupes($tid, $lang, $id, $mdsum) {
        
        $t = DB_DataObject::factory($this->tableName());
        $t->template_id = $tid;
        $t->mdsum = $mdsum;
        $t->whereAdd("lang = '{$lang}'");
        if ($t->count() == 1) {
            return $id; // only got one ... no issues..
        }
        
        //echo "GOT DUPES : $id, $lang, $id , $mdsum\n";
        
        //DB_DataObject::debugLevel(1);
        // find out if any of them have got translations.
        $ids = $t->fetchAll('id');
        
        
        $t = DB_DataObject::factory($this->tableName());
        $t->whereAddIn('src_id', $ids, 'int');
        $t->whereAdd("txt != ''");
        $t->orderBy('updated DESC');
        if ($t->count()) {
            $t->limit(1);
            // do any translations exist?
            $t->find(true);
            $id = $t->src_id;
        }
        
        
        // delete all the others...
        $t = DB_DataObject::factory($this->tableName());
        $t->whereAddIn('src_id', $ids, 'int');
        $t->whereAdd("src_id != $id");
        $t->find();
        while($t->fetch()) {
            $tt = clone($t);
            $tt->mdsum = $t->mdsum . '-bad-'. $t->id;
            $tt->update($t);
        }
        $t = DB_DataObject::factory($this->tableName());
        $t->whereAddIn('id', $ids, 'int');
        $t->whereAdd("id != $id");
        $t->find();
        while($t->fetch()) {
            $tt = clone($t);
            $tt->mdsum = $t->mdsum . '-bad-'. $t->id;
            $tt->update($t);
        }
        // this is done by calling code 
        //$t = DB_DataObject::factory($this->tableName());
        //$t->query("update core_templatestr set active= 1 where src_id = $id");
        
        
        
       //exit;
        return $id;
    
               
         
    }
    
    function syncLang($lang)
    {
        // bugs with our old code...
//        die('in?');
        $tn = $this->tableName();
        $t = DB_DataObject::factory($tn);
        $t->query("DELETE FROM {$tn} WHERE lang !='' AND src_id = 0 AND on_table  = ''");
        
        // find all the id's from lang that have not been generated..
        
        
        static $id_tmp = false;
        
        if ($id_tmp == false) {
            //find the origanal 
            $t = DB_DataObject::factory($tn);
            $t->whereAdd("lang = ''");
            $t->active = 1;
            
            //old code, this did not support the on_table
    //        $id_tmp = $t->fetchAll('id','template_id');
    //        $ids = array_keys($id_tmp);
            $id_tmp = array();
            //new code for support the sync tables 
            foreach($t->fetchAll() as $ori){
                $id_tmp[$ori->id] = $ori;
            }
        }
        $ids = array_keys($id_tmp);
        
        // matching by language:
        $t = DB_DataObject::factory($tn);
        $t->whereAddIn('src_id', $ids , 'int');
        $t->lang = $lang;
        //$t->active = 1;
        $got = $t->fetchAll('src_id');
        $missing = array_diff($ids, $got);
        
        if (empty($missing)) {
            return;
        }
        $t = DB_DataObject::factory($tn);
        $q = "CREATE TEMPORARY TABLE core_templatestr_insert SELECT
            id as src_id,
            '' as txt,
            '$lang' as lang,
            NOW() as updated,
            template_id,
            on_table,
            on_id,
            on_col,
            1 as active
            FROM core_templatestr
            WHERE
            id IN (". implode(',', $missing) . ")
        ";
        //echo $q; exit;
        DB_DataObject::factory($tn)->query($q);
        $q = "INSERT INTO $tn (src_id, txt, lang, updated, template_id, on_table,on_id, on_col, active) SELECT * FROM core_templatestr_insert";
        DB_DataObject::factory($tn)->query($q);
        $q = "DROP TEMPORARY TABLE core_templatestr_insert";
        DB_DataObject::factory($tn)->query($q);
            
      
        
    }
    
    // called from flexy to translate a string.
    
    
    function translateFlexyString($flexy, $string)
    {
         $debug = false;;
        //if (!empty($_REQUEST['_debug'])) { $debug= true; }
        
        // using $flexy->currentTemplate -> find the template we are looking at..
        // then find the string for $flexy->options['locale']
        //DB_DataObject::debugLevel(1);
        if ($debug) { var_dump($string); }
        
        static $cache = array(); // cache of templates..
        
        $ff = HTML_FlexyFramework::get();
        $view_name = isset($ff->Pman_Core['view_name']) ? $ff->Pman_Core['view_name'] : false;
        if (empty($view_name)) {
            $pg = HTML_FlexyFramework::get()->page;
            if (isset($pg->templateViewName)) {
                $view_name = $pg->templateViewName;
            }
            
        }
        
        
        
        if ($debug) { var_dump(array('view_name'=> $view_name)); }
        
        $tempdir = '';
        foreach($flexy->options['templateDir'] as $td) {
            if (substr($flexy->currentTemplate, 0, strlen($td)) == $td) {
                $tempdir = $td;
                break;
            }
        }
        
        
        $tmpname = substr($flexy->currentTemplate, strlen($td) +1);
        
         
        if (isset($cache[$tmpname]) && $cache[$tmpname] === false) {
            if ($debug) { echo "from cache no match - $string\n"; }
            return $string;
        }
        
        if (!isset($cache[$tmpname])) {
                
            
            
            $tmpl = DB_DataObject::factory('core_template');
            if ($view_name !== false) {
                $tmpl->view_name = $view_name;
            }
            if(!$tmpl->get('template', $tmpname)){
                // strip of site prefix if set...
                 
                $tmpl = DB_DataObject::factory('core_template');
                if(!$tmpl->get('template', $tmpname)){
                    //var_dump("no template? {$tmpname} or {$relpath}" );
                    $cache[$tmpname] = false;
                    if ($debug) { echo "no template found - no match - $string\n"; }
                    return $string;
                }
            }
            $cache[$tmpname] = $tmpl;
        } else {
            $tmpl = $cache[$tmpname] ;
        }
        
        
    
        //get original template id
        /*
        $orig = DB_DataObject::factory($this->tableName());
        $orig->lang = '';
        $orig->template_id = $tmpl->id;
        $orig->active = 1;
        
        $cache[$tmpname]->words = 
        
        if(!$orig->get( 'mdsum' , md5(trim($string)))){
             //var_dump('no text? '. $string);
            if ($debug) { echo "no original string found tplid: {$tmpl->id}\n"; }
            return false;
        }
        */
        
        if (empty($cache[$tmpname]->translations)) {
        
            //find out the text by language
            //DB_DataObject::DebugLevel(1);
            $x = DB_DataObject::factory($this->tableName());
            $x->lang = $flexy->options['locale'];
            $x->template_id = $tmpl->id;
            $x->autoJoin();
            $cache[$tmpname]->translations = $x->fetchAll('src_id_mdsum', 'txt');
            if (empty($cache[$tmpname]->translations)) {
                $cache[$tmpname]->translations = true;
            }
            //var_Dump($cache[$tmpname]->translations);
        }
        
        
        if ($cache[$tmpname]->translations === true) {
            return $string;
        }
        //var_dump("Checking: " . md5(trim($string)));
        
        if (!empty($cache[$tmpname]->translations [md5(trim($string))])){
           // var_dump("RETURNING: ". $cache[$tmpname]->translations [md5(trim($string))]);
            return $cache[$tmpname]->translations [md5(trim($string))];
        }
        return $string;
        
    }
    
    
    function translateChanged($flexy)
    {
        
        $date = $this->lastUpdated($flexy);
        if ($date === false) {
            return false;
        }
        $utime = file_exists($flexy->compiledTemplate) ?  filemtime( $flexy->compiledTemplate) : 0;
        return strtotime($date) >  $utime;
    }
    
    // determine if a complied template need recompling
    
    function lastUpdated($flexy)
    {
        //return true;
        // var_dump('check changed?');
        //DB_DataObject::debugLevel(1);
        //var_Dump(array($flexy->options['templateDir'][0], $flexy->currentTemplate));
        
        //var_dump($flexy->compiledTemplate);
        $utime = file_exists($flexy->compiledTemplate) ?  filemtime( $flexy->compiledTemplate) : 0;
        
       
        static $cache = array(); // cache of templates..
        
        $ff = HTML_FlexyFramework::get();
        $view_name = isset($ff->Pman_Core['view_name']) ? $ff->Pman_Core['view_name'] : false;
        
        // find which of the template directories was actually used for the template.
        
        $tempdir = '';
        foreach($flexy->options['templateDir'] as $td) {
            if (substr($flexy->currentTemplate, 0, strlen($td)) == $td) {
                $tempdir = $td;
                break;
            }
        }
        
        
        $tmpname = substr($flexy->currentTemplate, strlen($td) +1);
        
        // we do not have any record of this template..
        if (isset($cache[$tmpname]) && $cache[$tmpname] === false) {
            return false;
        }
        
        if (!isset($cache[$tmpname])) {
                
            
            
            $tmpl = DB_DataObject::factory('core_template');
            if ($view_name !== false) {
                $tmpl->view_name = $view_name;
            }
            if(!$tmpl->get('template', $tmpname)){
                // strip of site prefix if set...
                 
                $tmpl = DB_DataObject::factory('core_template');
                if(!$tmpl->get('template', $tmpname)){
                    //var_dump("no template? {$tmpname} or {$relpath}" );
                    $cache[$tmpname] = false;
                    return false;
                }
            }
            $cache[$tmpname] = $tmpl;
        } else {
            $tmpl = $cache[$tmpname] ;
        }
          
         
        
        $x = DB_DataObject::factory($this->tableName());
        $x->lang = $flexy->options['locale'];
        $x->active = 1;
        $x->template_id = $tmpl->id;
        //$x->whereAdd("updated > '". date('Y-m-d H:i:s', $utime)."'");
        if ($x->count() < 1) {
            return false; // we don't have any record of it.
        }
        $x->selectAdd();
        $x->selectAdd('max(updated) as max_updated');
        $x->find(true);
        return $x->max_updated;
        
        
    }
    
}

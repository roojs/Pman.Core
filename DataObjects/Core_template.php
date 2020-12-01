<?php
/**
 * Table Definition for cms_template 
 *
 *
 * The idea here is that it contains all the strings in the templates with a language '' (empty)
 * , it then generates a matching set of strings
 * 
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Cms_DataObjects_Cms_template  extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'cms_template';         // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $template;                           // string(64)  not_null

    public $updated;                        // blob(65535)  blob
    public $lang;    // text  NOT NULL;
    public $view_name; // eg mobile or desktop
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    
    function applyFilters($q, $au, $roo)
    {
        //DB_DataObject::debugLEvel(1);
//        $x = DB_Dataobject::Factory($this->tableName());
        
        
        // template scanning and syncing should be done by the UpdateDatabase Code.
        //if (!$x->count() || !empty($q['_rescan'])) {
            //DB_DataObject::debugLEvel(1);
            //$tp = DB_DataObject::factory('cms_template');
            //$opts = HTML_FlexyFramework::get()->Pman_Cms;
            //$tp->syncTemplateDir(false, '', !empty($q['_rescan']));
            //if (isset($q['lang'])) {
            //    $this->syncLang($q['lang']);
            //}
        //} 
//        $this->whereAdd("
//                join_
//            ");
        
    }
        
    function toRooArray($req)
    {
        $ret = $this->toArray();
        if (!empty($req['_clean_name']) ) {
            $ret['template_clean'] = preg_replace('#\.html$#i', '', $this->template);
            
        }
        return $ret;
        
    }
    
    /*
     * @param base (should be full path to template directory)
     * @param subdir = empty for top or subpath.
     */
    function syncTemplateDir($base = false,  $subdir = '', $force = false)
    {
        echo "syncTemplateDir: $base , $subdir, $force \n";
        //print_r(func_get_args());
        if (!$base) {
            $ff = HTML_FlexyFramework::get();
            if (!isset($ff->Pman_Cms)) {
                echo "[ERROR] Not scanning template directory - no set in Pman_Cms[templateDir]\n";
                return;
            }
            $opts = $ff->Pman_Cms;
            if (is_array($opts['templateDir'])) {
                foreach($opts['templateDir'] as $type=>$dir) {
                    $this->syncTemplateDir($dir, '', $force);
                }
                return;
            }
            
            $base = $opts['templateDir'];
            
            
        }
        if($force){
            $tmpls = DB_DataObject::factory('cms_template');
            $this->tmpls = $tmpls->fetchAll('template','id'); // dupes??
        }
        
        $tmp_dir = $base . (empty($subdir) ? '' : '/') . $subdir;
        
        if(!is_dir($tmp_dir)){
            return;
        }
        
        $dh = opendir($tmp_dir);
        if(!$dh){
            return; // something went wrong!?
        }
        
        while (($fn = readdir($dh)) !== false) {
            // do we care that it will try and parse the template directory??? - not really..
            // as we are only looking for php files..
            if(empty($fn) || $fn[0] == '.'){
                continue;
            }

            $fullpath = $tmp_dir."/".$fn;
//            echo "filename: ".$fullpath." \n";    
             
            $relpath = $subdir . (empty($subdir) ? '' : '/') . $fn;
            
            if(is_dir($fullpath)){
                // then recursively call self...
//                var_Dump($base);
//                var_Dump($subdir . (empty($subdir) ? '' : '/') . $fn);
                $this->syncTemplateDir($base, $subdir . (empty($subdir) ? '' : '/') . $fn );
                
                continue;
            }
            if (!preg_match('/(\.html|\.txt)$/', $fn)) {
                continue;
            }
            
             
//            var_dump($tmp);
//            var_dump($tmp_path);
//            $fn = basename($fn);
            if (isset($this->tmpls[$relpath])) {
                unset($this->tmpls[$relpath]);
            }
            
            
            
               
            $template = $this->syncTemplate($relpath, true, false);
//            var_dump($template);
            if (is_a($template, 'PEAR_Error')) {
                continue;
            }
        }
        closedir($dh);
//        
 
        if($force){
            foreach($this->tmpls as $id) {
                $x = DB_DataObject::factory($this->tableName());
                if ($x->get($id)) {
                    $x->delete();
                }
            }
        }
    }
    
    
       /**
      *
      * sync a single template
      * This should only be called by the UpdateDatabase code.
      * @param string $n      name of template
      * @param boolean $force  force parsing.
      * @param string $link ??? related to CMS page???
        
      * @return boolean|PEAR_Error errors if compile fails..
      *
      */
     /*
    function syncTemplate($n, $force = false, $link = '')
    {
        // read the template and extract the translatable strings.
        ini_set('memory_limit', '512M');
        
        //var_dump($n);
        $n= ltrim($n,'/');  // remove trailing slash..
        
        $fopts = HTML_FlexyFramework::get()->HTML_Template_Flexy;
        $opts = HTML_FlexyFramework::get()->Pman_Cms;
        //print_R($opts);
        //$dir = $opts['templateDir'] . '/' . $node;
        
        $oo['templateDir'] = $opts['templateDir'] ;
        $oo['compileDir'] = $fopts['compileDir'] . '_translation_files';
        $oo['forceCompile'] = true; //?? only for force above???
         
        $prefix = explode('/', $link);
        
        $n_split  = explode('/', $n);
        // this is project specific and should not be put in here..
        // we need a better way to handle it...
        // if first part of template dire matches an option in our settings...
        $pf = false;
        if (is_array($opts['templateDir']) &&  strlen($link) && isset($opts['templateDir'][$prefix[0]] )) {
            //echo "renaming templatedir/file (2)\n";
            $pf = $prefix[0];
            $oo['templateDir'] = $opts['templateDir'][$pf];
            
            
        }
        
        if (is_array($opts['templateDir']) &&  count($n_split) > 1 && isset($opts['templateDir'][$n_split[0]] )) {
            //echo "renaming templatedir/file (2)\n";
            $pf = $n_split[0];
            $oo['templateDir'] = $opts['templateDir'][$pf];
            $n = substr($n,  (strlen($pf)+1));
            //var_dump($n);
        }
        // non-html templates - treat as such..
        if (!preg_match('/\.html$/i', $n)) {
            $oo['nonHTML'] = true;
        }
        ///print_r(array($oo, $n));
        
        
        $flexy = new HTML_Template_Flexy( $oo );
          
        $r = $flexy->compile($n);
       
//        print_r( $flexy->compiler);
      
        //printf(" %0.3fs : $fname<BR>", $time);
        if (is_a($r,'PEAR_Error')) {
            return $r;
        }
        //print_R(number_format(memory_get_usage(),0, '.', ','))  ;
        $words = file_exists($flexy->getTextStringsFile) ?
                unserialize(file_get_contents($flexy->getTextStringsFile)) :
                array();
        
        $contentStrings =  $flexy->compiler->contentStrings;
        
        print_R($contentStrings);
        $ori_n = $n;
        if($pf){// support prefix
            
            $n = $pf.'/'.$n;
        }
        
        //check the template is static page or not 
        $link_split = explode('/', $link);
        $is_static = ($link_split[0] == 'Static') ? 1 : 0;
        
        $tmpl = DB_DataObject::Factory($this->tableName());
        if (!$tmpl->get('template', $n)) {
            $nn = $n;
            if($is_static)
            {
                $nn = 'static/'.$n;
            }
            $tmpl->template = $nn;
            $tmpl->lang = 'en'; /// ??? hard coded??
            $tmpl->updated = date('Y-m-d H:i:s', filemtime($flexy->currentTemplate));
            $tmpl->insert();
        } else {
            $xx =clone($tmpl);
            // has it been cahnged...
            if (!$force && filemtime($flexy->currentTemplate) == strtotime($tmpl->updated)) {
                // nothing changed..
                return $tmpl;
            }
            if (empty($tmpl->lang))  {
                //echo "FIX LANG?";exit;
                $tmpl->lang = 'en'; /// ??? hard coded??
            }
            
            $tmpl->updated = date('Y-m-d H:i:s', filemtime($flexy->currentTemplate));
            $tmpl->update($xx);
        }
        $cmsPage = false;
        
        
        
        if(!empty($link) && empty($is_static)){
            
            $page_id = DB_DataObject::factory('core_enum')->lookup('cms_page_type', 'page');
            echo "$link \n";
            $cmsPage = DB_DataObject::factory('cms_page');
            $cmsPage->setFrom(array(
                'page_link'  => $link,
                'page_type_id'  => $page_id,
                'translation_of_id' => 0,
                'is_element' => 0,
            ));
            $parent = 0;
            if(!$cmsPage->find(true)){
                $cmsPage = DB_DataObject::factory('cms_page');
            } else {
                // existing parent..
                $parent = $cmsPage->parent_id;
            }
            
            if (!$parent && strpos($link,'/') !== false) {
                $par = explode('/', $link);
                array_pop($par);
                $pname = implode('/', $par);
                $cmsPageP = DB_DataObject::factory('cms_page');
                if ($cmsPageP->get('page_link',$pname)) {
                    $parent = $cmsPageP->id;
                }
            }
            
            //if not php was found then the page should be a static
            $php = explode('/', $link);
            array_shift($php);
            
            
            $file = explode('/', $oo['templateDir']);
            array_pop($file);
            $file = implode('/', $file).'/'.implode('/', $php).'.php';
            
            $is_static = 1;
        
            // not sure how this is supposed to work..
            // if a tempalte exists then static = 0 ??
        
            if(preg_match('/\.html$/', $ori_n) && file_exists($file)){
                $is_static = 0;
            }

//            echo "$ori_n <<<< $is_static \n";
            $run_opts  = HTML_FlexyFramework::get()->page->opts;
            
            $cmsPage->setFrom(array(
                'parent_id' => $parent,
                'page_link'  => $link,
                'title' => basename($link),
                'page_type_id'  => $page_id,
                'template_id' => $tmpl->id,
                'language' => 'en',
                'translation_of_id' => 0,
                'is_system_page' => 1,
                'is_element' => 0,
                'is_static' => $is_static,
            ));
            //print_r($contentStrings);
            
            if (!empty($run_opts['force-content-update']) || !$cmsPage->id) { // only do this for the initila load
                foreach( array('title', 'body', 'extended') as $prop) {
                    if (isset($contentStrings['{page.'. $prop.'}'])) {
                        $cmsPage->{$prop} = $contentStrings['{page.'. $prop.'}'];
                    }
                    if (isset($contentStrings['{page.'. $prop.':h}'])) {
                        $cmsPage->{$prop} = $contentStrings['{page.'. $prop.':h}'];
                    }
                    if (isset($contentStrings['{page.'. $prop.'ToDisplayHtml():h}'])) {
                        $cmsPage->{$prop} = $contentStrings['{page.'. $prop.'ToDisplayHtml():h}'];
                    }
                    echo "cmrpage->{$prop} = ". $cmsPage->{$prop} ."\n";
                }
            }   
            
             
            
            if(!$cmsPage->id){
                $cmsPage->insert();
            } else {
                $cmsPage->update();
            }
        }
        
        $x = DB_DataObject::Factory('cms_templatestr');
        $x->syncTemplateWords($tmpl, $words);
        
        // if file_exists ( template/path/name.php << eg. a matching view..)
        // then create a system page for this. 
        
        
        
        $x = DB_DataObject::Factory('cms_template_element');
        $x->syncTemplateElement($tmpl, file_get_contents($flexy->currentTemplate), $flexy->compiler->contentStrings, $cmsPage);
        
        return $tmpl;
    
    }
    */
    function syncTemplatePage($pgdata)
    {
        $force = true;
        echo "compiling:"; print_r($pgdata);
        // read the template and extract the translatable strings.
        ini_set('memory_limit', '512M');
        
        //var_dump($n);
        $n= $pgdata['template'];  // remove trailing slash..
        
        $fopts = HTML_FlexyFramework::get()->HTML_Template_Flexy;
        $opts = HTML_FlexyFramework::get()->Pman_Cms;
        //print_R($opts);
        //$dir = $opts['templateDir'] . '/' . $node;
        $oo = array(
            'disableTranslate' => false,
            'templateDir' => $pgdata['template_dir'],
            'compileDir' => $fopts['compileDir'] . '_translation_files',
            'forceCompile' => true, //?? only for force above???
        );
         
        // non-html templates - treat as such..
        if (!preg_match('/\.html$/i', $pgdata['template'])) {
            $oo['nonHTML'] = true;
        }
        
        //print_r(array($oo, $n));
        
        
        $flexy = new HTML_Template_Flexy( $oo );
          
        if (!$flexy->resolvePath ($pgdata['template'])) {
            
            echo "SKIP - could not resolve path?\n";
            print_r($oo);
            return false;
        }
          
          
        $r = $flexy->compile($pgdata['template']);
       
        //print_r( $flexy);
      
        //printf(" %0.3fs : $fname<BR>", $time);
        if (is_a($r,'PEAR_Error')) {
            
            echo $r->toString(). "\n";
            return $r;
        }
        //print_R(number_format(memory_get_usage(),0, '.', ','))  ;
         
        $tmpl = DB_DataObject::Factory($this->tableName());
        $tmpl->words = file_exists($flexy->getTextStringsFile) ?
                unserialize(file_get_contents($flexy->getTextStringsFile)) :
                array();
        
        $tmpl->contentStrings   = $flexy->compiler->contentStrings;
        //var_dump(file_exists($flexy->getTextStringsFile));
        print_r($tmpl->words);
        $tmpl->currentTemplate  = $flexy->currentTemplate;
        
        $tmpl->view_name = $pgdata['base'];
        
        
        if (!$tmpl->get('template',  $pgdata['template'])) {
            
            $tmpl->template = $pgdata['template'];
            $tmpl->lang = 'en'; /// ??? hard coded??
            $tmpl->updated = date('Y-m-d H:i:s', filemtime($flexy->currentTemplate));
            $tmpl->insert();
        } else {
            $xx =clone($tmpl);
            // has it been cahnged...
            //if (!$force && filemtime($flexy->currentTemplate) == strtotime($tmpl->updated)) {
                // nothing changed..
            //    return $tmpl;
            //}
            if (empty($tmpl->lang))  {
                //echo "FIX LANG?";exit;
                $tmpl->lang = 'en'; /// ??? hard coded??
            }
            
            $tmpl->updated = date('Y-m-d H:i:s', filemtime($flexy->currentTemplate));
            $tmpl->update($xx);
        }
      
        
        $x = DB_DataObject::Factory('cms_templatestr');
        $x->syncTemplateWords($tmpl);
        
        // if file_exists ( template/path/name.php << eg. a matching view..)
        // then create a system page for this. 
        
        
        
        $x = DB_DataObject::Factory('cms_template_element');
        $tmpl->elements = $x->syncTemplateElement($tmpl,
                file_get_contents($flexy->currentTemplate),
                $flexy->compiler->contentStrings,
                false);
        
        
        
        return clone($tmpl);
    
    }
}
<?php
/**
 * Table Definition for core_template 
 *
 *
 * The idea here is that it contains all the strings in the templates with a language '' (empty)
 * , it then generates a matching set of strings
 * 
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_template  extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'core_template';         // table name
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
            //$tp = DB_DataObject::factory('core_template');
            //$opts = HTML_FlexyFramework::get()->Pman_Core;
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
            if (!isset($ff->Pman_Core)) {
                echo "[ERROR] Not scanning template directory - no set in Pman_Core[templateDir]\n";
                return;
            }
            $opts = $ff->Pman_Core;
            if (is_array($opts['templateDir'])) {
                foreach($opts['templateDir'] as $type=>$dir) {
                    $this->syncTemplateDir($dir, '', $force);
                }
                return;
            }
            
            $base = $opts['templateDir'];
            
            
        }
        if($force){
            $tmpls = DB_DataObject::factory('core_template');
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
            if (!preg_match('/(\.html|\.txt|\.abw)$/', $fn)) {
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
    
    /* compile a html template
     *  
     *  @param template_dir  << the path to the template dir ... Pman/XXX/template ...
     *  @param template   << name of template used by name field)
     *  @param base  << view name (module ? + templates?)
     *  
     *  
     */
    function syncTemplatePage($pgdata)
    {
        print_r($pgdata);
        
        $force = true;
         //echo "compiling:"; print_r($pgdata);
        // read the template and extract the translatable strings.
        ini_set('memory_limit', '512M');
        
        //var_dump($n);
        $n= $pgdata['template'];  // remove trailing slash..
        
        $fopts = HTML_FlexyFramework::get()->HTML_Template_Flexy;
        $opts = HTML_FlexyFramework::get()->Pman_Core;
        //print_R($opts);
        //$dir = $opts['templateDir'] . '/' . $node;
        $oo = array(
            'fatalError'    => PEAR_ERROR_EXCEPTION,  
            'disableTranslate' => false,
            'templateDir' => $pgdata['template_dir'],
            'compileDir' => $fopts['compileDir'] . '_translation_files',
            'forceCompile' => true, //?? only for force above???
        );
         
        // non-html templates - treat as such..
        // abiword - treat as html?
        if (!preg_match('/\.(html|abw)$/i', $pgdata['template'])) {
            $oo['nonHTML'] = true;
        }
        
        //print_r(array($oo, $n));
        
        
        $flexy = new HTML_Template_Flexy( $oo );
          
        if (!$flexy->resolvePath ($pgdata['template'])) {
            //echo "SKIP - could not resolve path?\n"; print_r($oo);
            return false;
        }
        
        // attempt to find the template... record.
        $tmpl = DB_DataObject::Factory($this->tableName());
       
        $tmpl->view_name = $pgdata['base'];
        if ($tmpl->get('template',  $pgdata['template'])) {
            if (strttotime($tmpl->updated) >= filemtime($flexy->resolvePath ($pgdata['template']))) {
                return $tmpl;
            }
        }
        
        
        
        try {
            $r = $flexy->compile($pgdata['template']);
        } catch(Exception $e) {
            $old = clone($tmpl);
            $tmpl->updated   = date('Y-m-d H:i:s',filemtime($flexy->resolvePath ($pgdata['template'])));
            if ($tmpl->id) {
                $tmpl->update($tmpl);
            } else {
                $tmpl->lang = 'en';
                $tmpl->insert();
            }
            
            
            return false;
        }
       
      
        if (is_a($r,'PEAR_Error')) {
            
           // echo $r->toString(). "\n";
            return $r;
        }
          
        $tmpl = DB_DataObject::Factory($this->tableName());
        $tmpl->words = file_exists($flexy->getTextStringsFile) ?
                unserialize(file_get_contents($flexy->getTextStringsFile)) :
                array();
        
        $tmpl->contentStrings   = $flexy->compiler->contentStrings;
        //var_dump(file_exists($flexy->getTextStringsFile));
        //print_r($tmpl->words);
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
      
        
        $x = DB_DataObject::Factory('core_templatestr');
        $x->syncTemplateWords($tmpl);
        
        // if file_exists ( template/path/name.php << eg. a matching view..)
        // then create a system page for this. 
        
        
        
        $x = DB_DataObject::Factory('core_template_element');
        $tmpl->elements = $x->syncTemplateElement($tmpl,
                file_get_contents($flexy->currentTemplate),
                $flexy->compiler->contentStrings,
                false);
        
        
        
        return clone($tmpl);
    
    }
    function syncPhpGetText($pgdata)
    {
        $tmpl = DB_DataObject::Factory($this->tableName());
        $tmpl->view_name = $pgdata['base'];
        if ($tmpl->get('template',  $pgdata['template'])) {
            if (strttotime($tmpl->updated) >= filemtime( $pgdata['template_dir'] . '/'. $pgdata['template']  )) {
                return $tmpl;
            }
        }
        
        $ar = token_get_all(file_get_contents($pgdata['template_dir'] . '/'. $pgdata['template']  ));
        print_R($ar);exit;
        exit;
        
        
        
        
    }
}
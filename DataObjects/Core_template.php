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
    
    function beforeUpdate($old, $q, $roo)
    {
        if (!empty($q['_rescan'])){
            if ($this->filetype != 'html') {
                $roo->jerr("can not update a php source file currently - TDOD");
            }
            $pg = HTML_FlexyFramework::get()->page;
            
            $this->syncTemplatePage(array(
                'template_dir' => $pg->rootDir . '/'. str_replace('.', '/', $this->view_name). '/templates',
                'template' => $this->template,
                'base' => $this->view_name,
                'force' => true
            ));
            // update the different langage versions of this page.
            $x = DB_Dataobject::Factory('core_templatestr');
            $x->selectAdd();
            $x->selectAdd('distinct(lang) as lang');
            $x->whereAdd("lang != ''");
            $langs  = $x->fetchAll('lang');
            foreach($langs as $l) {
                $x = DB_Dataobject::Factory('core_templatestr');
                $x->syncLang($l, $this->id);
            }
           
            
            $roo->jok("updated -" .  $this->template);
        }
    }
   
    
    
    
    /*
     * USED? - this is in updateBJSTemplate ?
     * @param base (should be full path to template directory)
     * @param subdir = empty for top or subpath.
     */
    /*
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
    */
    /* compile a html template  - called by UpdateBjsTemplates - scan Pman Templates
     *  
     *  @param template_dir  << the path to the template dir ... Pman/XXX/template ...
     *  @param template   << name of template used by name field)
     *  @param base  << view name (module ? + templates?)
     *  @param force << optional - forces even if database is newer.
     *  
     *  
     */
    
    function syncTemplatePage($pgdata)
    {
        //print_r($pgdata);
        
        $force = true;
         //echo "compiling:"; print_r($pgdata);
        // read the template and extract the translatable strings.
        ini_set('memory_limit', '512M');
        
        //var_dump($n);
        $n = $pgdata['template'];  // remove trailing slash..
        
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
            clearstatcache();
            if (strtotime($tmpl->updated) >= filemtime($flexy->resolvePath ($pgdata['template']) . '/'. $pgdata['template'])) {
                if ($tmpl->is_deleted != 0 ||  $tmpl->filetype != 'html') {
                    $oo = clone($tmpl);
                    $tmpl->is_deleted = 0;
                    $tmpl->filetype = 'html';
                    $tmpl->update($oo);
                }
                if (empty($pgdata['force'])) {
                  //  echo "SKIP NO UPDATE: " . $pgdata['template'] ."\n";
                   // echo $flexy->resolvePath ($pgdata['template']).  ':'. $tmpl->updated  . ">=" .  date('Y-m-d H:i:s',filemtime($flexy->resolvePath ($pgdata['template']))) . "\n";
                    return $tmpl;
                }
            }
        }
        
        //die("got here");
        
        try {
            $r = $flexy->compile($pgdata['template']);
           
            
        } catch(Exception $e) {
            $old = clone($tmpl);
            $tmpl->updated   = date('Y-m-d H:i:s',filemtime($flexy->resolvePath ($pgdata['template']) . '/'. $pgdata['template']));
            if ($tmpl->id) {
                $tmpl->is_deleted = 0;
                $tmpl->filetype = 'html';
                $tmpl->update($tmpl);
            } else {
                $tmpl->is_deleted = 0;
                $tmpl->filetype = 'html';
                $tmpl->lang = 'en';
                $tmpl->insert();
            }
            //echo "SKIP: " . $pgdata['template'] ."\n";
           //   echo "SKIP - exception\n"; print_r($e);
            return false;
        }
       
      
        if (is_a($r,'PEAR_Error')) {
            //echo "SKIP: " . $pgdata['template'] ."\n";
            //echo $r->toString(). "\n";
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
        
        //echo $pgdata['template'] ."\n";
        if (!$tmpl->get('template',  $pgdata['template'])) {
            $tmpl->is_deleted = 0;
            $tmpl->filetype = 'html';
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
            $tmpl->filetype = 'html';
            $tmpl->is_deleted = 0;
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
    
    // allow reuse in cms templatstr
    function factoryStr()
    {
        return DB_DataObject::factory('core_templatestr');
    }

    function syncFileWord($pgdata, $filetype)
    {
        $tmpl = DB_DataObject::Factory($this->tableName());
        $tmpl->view_name = $pgdata['base'];
        $tmpl->currentTemplate = $pgdata['template_dir'] . '/'. $pgdata['template'];
        
        if ($tmpl->get('template',  $pgdata['template'])) {
            if (strtotime($tmpl->updated) >= filemtime( $tmpl->currentTemplate )) {
                if ($tmpl->is_deleted != 0 ||  $tmpl->filetype != $filetype) {
                    $oo = clone($tmpl);
                    $tmpl->is_deleted = 0;
                    $tmpl->filetype = $filetype;
                    $tmpl->update($oo);
                }
                return $tmpl;
            }
        }

        $words = array();

        switch($filetype) {
            case "php":
                $ar = token_get_all(file_get_contents( $tmpl->currentTemplate  ));
                foreach( $ar as $i=> $tok) {
                    if (!is_array($tok) || $tok[0] != T_CONSTANT_ENCAPSED_STRING) {
                        continue;
                    }
                    if ($i < 2) {
                        continue;
                    }
                    if (is_array($ar[$i-1]) || $ar[$i-1] != '(') {
                        continue;
                    }
                    if (!is_array($ar[$i-2]) || $ar[$i-2][1] != '_') {
                        continue;
                    }
                    $ct = $tok[1][0];
                    $words[] =  str_replace('\\'. $ct, $ct, trim($tok[1] , $ct));
                    
                }
                break;
            case "js":
                $fc = file_get_contents( $tmpl->currentTemplate );
        
                preg_match_all('/\._\("([^"]+)"\)/', $fc, $outd);
                $words = $outd[1];
                 
                preg_match_all('/\._\(\'([^\']+)\'\)/', $fc, $outs);
                
                // ?? seriously adding two arrays?
                $words =  array_diff(array_merge($words, $outs[1]), array_intersect($words, $outs[1]));
                break;
            case "xml":
                $words = $pgdata['words'];
                break;
        }

        $words = array_unique($words);

        if(empty($words)) {
            return;
        }

        if ($tmpl->id) {
            $oo = clone($tmpl);
            $tmpl->is_deleted = 0;
            $tmpl->filetype = $filetype;
            $tmpl->updated = date('Y-m-d H:i:s', filemtime($tmpl->currentTemplate));
            $tmpl->update($oo);
        } else {
            $tmpl->is_deleted = 0;
            $tmpl->filetype = $filetype;
            $tmpl->lang = 'en';
            $tmpl->updated = date('Y-m-d H:i:s', filemtime($tmpl->currentTemplate));
            $tmpl->insert();
        }

        $tmpl->words = $words;

        $this->factoryStr()->syncTemplateWords($tmpl);

        return $tmpl;
    }

    function syncPhpGetText($pgdata)
    {
        return $this->syncFileWord($pgdata, 'php'); 
    }

    /**
     * plain JS files use ._(....) to flag 
     * it does not support quoted strings or anything really
     * very simple strings only
     */ 
    
    function syncJsWords($pgdata)
    {
        return $this->syncFileWord($pgdata, 'js');   
    }

    function syncPowerpointXMLText($pgdata) 
    {
        return $this->syncFileWord($pgdata, 'xml');
    }
    
    /*
    SELECT LOWER(
CONCAT(
REPLACE(view_name, '.','_'),
'_',
REPLACE(template,'/','_')
)
)
FROM core_template

WHERE (
 = 'release_pressrelease_distributionreportnew_journalistdistribution.php'
)
*/

    
    function genGetText($clsname, $lang=false)
    {
        static $done = array();
        $clsname = strtolower($clsname);

        textdomain($clsname);
     

        $ff = HTML_FlexyFramework::get();
        $lang = $lang ? $lang : (isset($ff->locale) ? $ff->locale : 'en');
        

        if (!empty($done[$clsname.':'.$lang])) {
            return true; // already sent headers and everything.
        }
        
        putenv("LANGUAGE=$lang");
        if ($lang != 'en') {
            if (!setlocale(LC_ALL, $lang.'.UTF-8')) {
                $ff->page->jerr("Language is not available {$lang}");
            }
        }
        
        
        $d = DB_DataObject::factory($this->tableName());
        $d->whereAdd("
            LOWER(
                CONCAT(
                      REPLACE(view_name, '.','_'),
                    '_',
                    REPLACE(template,'/','_')
                )
            ) = '{$clsname}.php'
       ");
        $d->filetype = 'php';
        if (! $d->find(true) ){
            $done[$clsname.':'.$lang] = true;
            return false;
        }
        $user = 'www-data'; // ?? do we need other ones
        $compileDir = ini_get('session.save_path') .'/' . 
            $user . '_gettext_' . $ff->project;
        
        if ($ff->appNameShort) {
            $compileDir .= '_' . $ff->appNameShort;
        }
        if ($ff->version) {
            $compileDir .= '.' . $ff->version;
        }
        $lang = $lang ? $lang : $ff->locale;
        $fdir = "{$compileDir}/{$lang}/LC_MESSAGES";
        $fname = "{$fdir}/{$clsname}.mo";
        
         
        //exit;
        bindtextdomain($clsname, $compileDir) ;
        bind_textdomain_codeset($clsname, 'UTF-8');

        textdomain($clsname);

        
        //textdomain($clsname);
        
        $done[$clsname.':'.$lang] = 1;
        
        // do we need to compile the file..
        $ts = $this->factoryStr();
        $ts->selectAdd('COALESCE(MAX(updated), "1000-01-01") as updated');
        $ts->lang = $lang;
        $ts->template_id = $d->id;
        if (!$ts->find(true)) {
            // then in theory there are no translations
            return false;
        }
        if (file_exists($fname) && strtotime($ts->updated) < filemtime($fname)) {
            return $fname; // file exists and is newer than our updated line.
        }
        //DB_DataObject::debugLevel(1);

        $ts = $this->factoryStr();
        $ts->autoJoin();
        $ts->selectAdd("join_src_id_id.txt as src_id_txt, {$ts->tableName()}.txt as txt");
        $ts->lang = $lang;
        $ts->template_id = $d->id;
        $ts->whereAdd("LENGTH(join_src_id_id.txt) > 0 AND LENGTH({$ts->tableName()}.txt) > 0");
        $words = $ts->fetchAll('src_id_txt', 'txt' );
               
        if (!file_exists($fdir)) {
            // var_dump($fdir);
            mkdir($fdir, 0700, true);
        }
        
        require_once 'File/Gettext.php';
        $gt = File_Gettext::factory('PO', preg_replace('/\.mo$/', '.po', $fname));
        $gt->fromArray(
            
            array(
                'meta' => array(
                    "Language" =>  $lang,
                    'Content-Type'      => 'text/plain; charset=UTF-8',
                    'Content-Transfer-Encoding'      => ' 8bit',
                     'PO-Revision-Date'  => date('Y-m-d H:iO'),
                 ),
                'strings' => $words
            )
            
        );
        $gt->save();
        
        // mo DOESNT WORK!!
        require_once 'System.php';
        $poname = preg_replace('/\.mo$/', '.po', $fname);
        $msgfmt = System::which('msgfmt');
        $cmd = "{$msgfmt} {$poname}  -o {$fname}";
        //echo $cmd;
        
        `$cmd`;
        
        
         
        
        return $fname;
        
        require_once 'File/Gettext.php';
        $gt = File_Gettext::factory('MO', $fname);
        $gt->fromArray(
            
            array(
                'meta' => array(
                     "Language" =>  $lang,
                    'Content-Type'      => 'text/plain; charset=UTF-8',
                    'Content-Transfer-Encoding'      => ' 8bit',
                     'PO-Revision-Date'  => date('Y-m-d H:iO'),
                ),
                'strings' => $words
            )
            
        );
        $gt->save(); 
        
    }
}
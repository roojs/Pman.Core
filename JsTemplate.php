<?php
/***
 *
 * usage:
 *
 * just need to add this to HTML
 *
 * <script src="{baseURL}/Core/JsTemplate.js">
 *
 * 
 *
 * 
 *
 * first part should return a list of files to include.
 * $x = new Pman_Core_JsTemplate($cfg)
 *
 * $x->to
 *
 * second part should compile and deliver.
 *
 * 
 *
 * // should return {baseurl}/Pman/JsTemplate/mod/file
 *
 * 
 *
 *
 */
require_once 'Pman.php';

class Pman_Core_JsTemplate extends Pman {
    
    
    var $modDir = false;
    
    function getAuth()
    {
        parent::getAuth();
        return true;
         
    }
     
    function get()
    {
        // get the modules.
        header('Content-type: text/javascript');
        
        $ff = HTML_FlexyFramework::get();
        
        $pr = $ff->project;
        $ar = explode(PATH_SEPARATOR, $ff->HTML_Template_Flexy['templateDir']);
        
        $prefix = $pr == 'Pman' ? 'Pman.' : '';
        
        foreach($ar as $mod) {
            $dir =   dirname($mod) . '/jtemplates';
            if (!file_exists($dir)) {
                echo '// missing directory '. htmlspecialchars($dir) ."\n";
                continue;
            }
            // got a directory..
            $mn = basename(dirname($mod));
            $ar = glob("$dir/*.html") ;
            if (empty($ar)) {
                echo '// no template is directory '. htmlspecialchars($dir) ."\n";
                continue;
            }
            
            echo "{$prefix}{$mn} = {$prefix}{$mn} || {};\n";
            echo "{$prefix}{$mn}.template = {$prefix}{$mn}.template   || {};\n\n";
            
            foreach(glob("$dir/*.html") as $fn) {
                $name = "{$prefix}{$mn}.template." . preg_replace('/\.html$/i', '', basename($fn));
                echo $this->compile($fn, $name) . "\n";
                

            }
  //              testing..
//new HTML_FlexyFramework_JsTemplate('/home/alan/gitlive/web.mtrack/MTrackWeb/jtemplates/TimelineTicket.html', 'Pman.template.TimelineTicket');
            
            
            
        }
        exit;
        
        
        
    }
    
    
    function compile($fn, $name)
    {
        // cached? - check file see if we have cached contents.
        
        
        $contents = file_get_contents($fn);
        $ar = preg_split('/(\{[^\}]+})/', $contents, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        
        
        
        //echo '<PRE>' . htmlspecialchars(print_r($ar,true));
        
        $out= array();
        
        $head = "$name = function(t)\n{\n    var ret = [];\n\n";
        
        $funcs = array();
        // do not allow nested functions..?
        $fstart = -1;
        $indent = 1;
        $inscript = false;
        $ret = &$out;
        foreach($ar as $item) {
            $in = str_repeat("    ", $indent);
            
            //var_Dump(substr($item,-3,2));
            switch(true) {
                case (!strlen($item)):
                    continue;
                
                case ($inscript && ($item != '{end:}')):
                    $ret[] = $item;
                    continue;
                
                case ($inscript && ($item == '{end:}')):
                    $inscript = false;
                    continue;
                
                case ($item[0] != '{'):
                    if (!strlen(trim($item))) {
                        continue;
                    }
                    $ret[] = $in . "ret += ". json_encode($item) . ";";
                    continue;
                
                
                case ($item == '{script:}'): 
                    $inscript = true;
                    continue;
                
                
                
                case (substr($item,1,3) == 'if('):
                    $ret[] = $in . substr($item,1,-1) . ' {';
                    $indent++;
                    continue;
                
                case (substr($item,1,4) == 'end:'):
                    $indent--;
                    $in = str_repeat("    ", $indent);
                    $ret[] = $in . "}";
                    if ($fstart == $indent) {
                        $fstart = -1;
                        $ret = &$out;
                    }
                    continue;
                
                case (substr($item,1,7) == 'return:'):
                    $ret[] = $in . "return;";
                    continue;
                
                case (substr($item,1,9) == 'function:'):
                    $fstart = $indent;
                    $indent++;
                    $ret = &$funcs;
                    $def  = substr($item,10,-1) ;
                    list($name,$body) = explode('(', $def, 2);
                    
                    
                    $ret[] = $in . "var $name = function (" .  $body  . '{';
                    continue;
                
                default:
                    if (substr($item,-3,2) == ':h') {
                        $ret[] = $in . "ret += ".  substr($item,1,-3) . ';';
                        continue;
                    }
                    $ret[] = $in . "ret += Roo.util.Format.htmlEncode(".  substr($item,1,-1).');';
                    continue;
                
            }
            
            
        }
        $in = str_repeat("    ", $indent);
        $ret[] = $in .  "return ret.join('');\n}\n";
        return $head . implode("\n",$funcs) . "\n" .implode("\n",$out) ;
        //echo '<PRE>' . htmlspecialchars(implode("\n",$ret));
        
        
        
    }
    
    
    
}

// 


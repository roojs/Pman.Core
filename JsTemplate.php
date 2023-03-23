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
     
    function get($v, $opts=array())
    {
        
        $this->sessionState(0);
        // get the modules.
        header('Content-type: text/javascript');
        
        $ff = HTML_FlexyFramework::get();
        
        $pr = $ff->project;
        
        $mods = $this->modulesList();
        //print_r($mods);
        
        //$ar = explode(PATH_SEPARATOR, $ff->HTML_Template_Flexy['templateDir']);
        array_push($mods, $pr);
        
        foreach($mods as $mod )
        {
            $prefix = $mod == $pr  ?  "" : "{$pr}.";
            //var_dump($prefix);
            $pdir = $mod == $pr  ? '' : ($pr .'/') ;
         
            $dir =  $this->rootDir .'/'.$pdir .  $mod . '/jtemplates';
            if (!file_exists($dir)) {
                //echo '// missing directory '. htmlspecialchars($dir) ."\n";
                continue;
            }
            // got a directory..
             
            $ar = glob("$dir/*.html") ;
            if (empty($ar)) {
                echo '// no template is directory '. htmlspecialchars($dir) ."\n";
                continue;
            }
            
            echo "{$prefix}{$mod} = {$prefix}{$mod} || {};\n";
            echo "{$prefix}{$mod}.template = {$prefix}{$mod}.template   || {};\n\n";
            
            foreach(glob("$dir/*.html") as $fn) {
                $name = "{$prefix}{$mod}.template." . preg_replace('/\.html$/i', '', basename($fn));
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
        $ar = preg_split('/(\{[^\\n}]+})/', $contents, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        
        
        
        //echo '<PRE>' . htmlspecialchars(print_r($ar,true));
        
        $out= array();
        
        $head = "$name = function(t)\n{\n    var ret = '';\n\n";
        
        $funcs = array();
        // do not allow nested functions..?
        $fstart = -1;
        $indent = 2;
        $inscript = false;
        $ret = &$out;
        foreach($ar as $item) {
            $in = str_repeat("    ", $indent);
            $indent  = max($indent , 1);
            //var_Dump(substr($item,-3,2));
            switch(true) {
                case (!strlen($item)):
                    continue 2;
                
                case ($inscript && ($item != '{end:}')):
                    $ret[count($ret)-1] .= $item;
                    continue 2;
                
                case ($inscript && ($item == '{end:}')):
                    $inscript = false;
                    continue 2;
                 
             
                case ($item[0] != '{'):
                    if (!strlen(trim($item))) {
                        continue 2;
                    }
                    $ret[] = $in . "ret += ". json_encode($item) . ";";
                    continue 2;
                
                
                case ($item == '{script:}'): 
                    $inscript = true;
                     $ret[] = '';
                    continue 2;
                
                case ($item[1] == '!'):
                    $ret[] = $in . substr($item,2,-1) .';';
                    continue 2;
                
                
                case (substr($item,1,3) == 'if('):
                    $ret[] = $in . substr($item,1,-1) . ' {';
                    $indent++;
                    continue 2;
                
                case (substr($item,1,5) == 'else:'):
                    $indent--;
                    $in = str_repeat("    ", $indent);
                    $ret[] = $in . "} else { ";
                    $indent++;
                    continue 2;
                 
                case (substr($item,1,4) == 'end:'):
                    $indent--;
                    $in = str_repeat("    ", $indent);
                    $ret[] = $in . "}";
                    if ($fstart == $indent) {
                        $fstart = -1;
                        $ret = &$out;
                    }
                    continue 2;
                
                case (substr($item,1,7) == 'return:'):
                    $ret[] = $in . "return;";
                    continue 2;
                
                case (substr($item,1,9) == 'function:'):
                    $fstart = $indent;
                    $indent++;
                    $ret = &$funcs;
                    $def  = substr($item,10,-1) ;
                    list($name,$body) = explode('(', $def, 2);
                    
                    
                    $ret[] = $in . "var $name = function (" .  $body  . '{';
                    continue 2;
                
                default:
                    if (substr($item,-3,2) == ':h') {
                        $ret[] = $in . "ret += ".  substr($item,1,-3) . ';';
                        continue 2;
                    }
                    if (substr($item,-3,2) == ':b') {
                        $ret[] = $in . "ret += Roo.util.Format.htmlEncode(".  substr($item,1,-3).').split("\n").join("<br/>\n");';
                        continue 2;
                    }
                    $ret[] = $in . "ret += Roo.util.Format.htmlEncode(".  substr($item,1,-1).');';
                    continue 2;
                
            }
            
            
        }
        $in = str_repeat("    ", $indent);
        $ret[] = $in .  "return ret;\n}\n";
        return $head . implode("\n",$funcs) . "\n\n" .implode("\n",$out) ;
        //echo '<PRE>' . htmlspecialchars(implode("\n",$ret));
        
        
        
    }
    
    
    
}

// 


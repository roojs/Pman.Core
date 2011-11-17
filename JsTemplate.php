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
        
        $ff = HTML_FlexyFramework::get()->HTML_Template_Flexy;
        
        $ar = explode(PATH_SEPARATOR, $ff['templateDir']);
        
        
        
        
        foreach($ar as $mod) {
            $dir =   dirname($mod) . '/jtemplates';
            if (!file_exists($dir)) {
                echo '// missing directory '. htmlspecialchars($dir) ."\n";
            }
            // got a directory..
            $mn = basename(dirname($mod));

            
            foreach(glob("$dir/*.html") as $fn) {
                $name = 'Pman.' . $mn .'.' . preg_replace('/\.html$/i', '', basename($fn));
                echo $this->compile($fn, $name);
                

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
        
        $ret = array();
        
        $ret[] = "var $name = function(t) {\n    var ret=[];\n";
        $indent = 1;
        foreach($ar as $item) {
            $in = str_repeat("    ", $indent);
            
            //var_Dump(substr($item,-3,2));
            switch(true) {
                case (!strlen($item)):
                    continue;
                
                case ($item[0] != '{'):
                    if (!strlen(trim($item))) {
                        continue;
                    }
                    $ret[] = $in . "ret+= ". json_encode($item) . ";";
                    continue;
                
                case (substr($item,1,3) == 'if('):
                    $ret[] = $in . substr($item,1,-1) . ' {';
                    $indent++;
                    continue;
                
                case (substr($item,1,4) == 'end:'):
                    $indent--;
                    $in = str_repeat("    ", $indent);
                    $ret[] = $in . "}";
                    continue;
                
                case (substr($item,1,7) == 'return:'):
                    $ret[] = $in . "return;";
                    continue;
                
                case (substr($item,1,9) == 'function:'):
                    $indent++;
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
        return implode("\n",$ret);
        echo '<PRE>' . htmlspecialchars(implode("\n",$ret));
        
        
        
    }
    
    
    
}

// 


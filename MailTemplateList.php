<?php

require_once 'ConvertStyle.php';

class Pman_Core_MailTemplateList extends Pman_Core_ConvertStyle
{
    
    function get()
    {
        $fopts = HTML_FlexyFramework::get()->HTML_Template_Flexy;
        
        $templateDir = explode(PATH_SEPARATOR, $fopts['templateDir']);
        
        $base = 'http://' . $fopts['host'] . $this->rootURL;
        
        $ret = array();
        
        foreach ($templateDir as $dir){
            
            if(!file_exists($dir . '/mail')){
                continue;
            }
              
            if ($handle = opendir($dir . '/mail')) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry == "." || $entry == ".." || !preg_match('/\.html$/', $entry)) {
                        continue;
                    }
                    
                    $path = "$dir/mail/$entry";
                    
                    $ret[] = array(
                        'file' => $entry,
                        'content' => $this->convertStyle($base, $path, false)
                    );
                }
                
                closedir($handle);
            }
            
        }

        $this->jok($ret);
        
    }
     
}

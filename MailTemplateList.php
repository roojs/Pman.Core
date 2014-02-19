<?php

require_once 'ConvertStyle.php';

class Pman_Core_MailTemplateList extends Pman_Core_ConvertStyle
{
    
    function get()
    {
        print_r($this);exit;
        $fopts = HTML_FlexyFramework::get()->HTML_Template_Flexy;
        
        $templateDir = explode(PATH_SEPARATOR, $fopts['templateDir']);
        
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
                    
                    $ret[] = array(
                        'file' => $entry,
                        'content' => $this->convertStyle("$dir/mail/$entry")
                    );
                }
                
                closedir($handle);
            }
            
        }
        
        $this->jok($ret);
        
    }
     
}

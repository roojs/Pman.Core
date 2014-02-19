<?php

require_once 'Pman.php';

class Pman_Core_MailTemplateList extends Pman
{
    function getAuth()
    {
        $au = $this->getAuthUser();
        if (!$au) {
            die("NOT authenticated");
        }
        $this->authUser = $au;
        return true;
    }

    function get()
    {
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
                        'file' => $entry
                        'content' => file_get_contents("$dir/mail/$entry")
                    );
                }
                
                closedir($handle);
            }
            
        }
        print_r($ret);exit;
        exit;
        $this->jdata(array(array('name' => 'aa', 'body'=> 'test')));
        
        print_r(array_unique($fopts->templateDir));
        exit;
    }
     
}

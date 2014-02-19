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
        
        foreach ($templateDir as $dir){
            echo "$dir \n";
            if(!file_exists($dir . '/mail')){
                continue;
            }
            
            if ($handle = opendir($dir . '/')) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry == "." || $entry == "..") {
                        continue;
                    }
                    
                    echo "$entry\n";
                }
                
                closedir($handle);
            }
            
        }
        exit;
        $this->jdata(array(array('name' => 'aa', 'body'=> 'test')));
        
        print_r(array_unique($fopts->templateDir));
        exit;
    }
     
}

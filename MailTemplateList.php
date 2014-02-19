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
        $this->jdata(array(array('name' => 'aa', 'body'=> 'test')));
        $fopts = HTML_FlexyFramework::get()->HTML_Template_Flexy;
        print_r(array_unique($fopts->templateDir));
        exit;
    }
     
}

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
        $fopts = HTML_FlexyFramework::get()->page;
        print_r(array_unique($fopts->templateDir));
        exit;
    }
     
}

<?php

require_once 'Pman.php';

class Pman_Core_MessagePreview extends Pman
{
    var $masterTemplate = 'mail/MessagePreview.html';
    
    function getAuth()
    {
        if (HTML_FlexyFramework::get()->cli) {
            return true;
        }
        $this->authUser = $this->getAuthUser();
        if (!$this->authUser) {
            return false;
        }
        return true;
    }
    
    function get()
    {
        if(empty($_REQUEST['_id'])){
            $this->jerr('Missing Options');
        }
        
        $mlq = DB_DataObject::factory('core_mailing_list_message');
        
        $mlq->get($_REQUEST['_id']);
        
        $this->msg = $mlq;

        $this->showHtml = isset($_REQUEST['_as_html']) ? true : false;
        
    }
}

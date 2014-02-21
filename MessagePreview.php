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
        if(empty($_REQUEST['_id']) || empty($_REQUEST['_table'])){
            $this->jerr('Missing Options');
        }
        
        $mlq = DB_DataObject::factory($_REQUEST['_table']);
        
        $mlq->get($_REQUEST['_id']);
        
        $this->msg = $mlq;

        $this->showHtml = isset($_REQUEST['_as_html']) ? true : false;
        
    }
    
    function post()
    {
        if(empty($_REQUEST['_id']) || empty($_REQUEST['_table'])){
            $this->jerr('Missing Options');
        }
        
        $mid = $_REQUEST['_id'];
        
        $mlq = DB_DataObject::factory($_REQUEST['_table']);
        
        $mlq->get($_REQUEST['_id']);
        
        $content = array(
            'template' => $mlq->name,
            'person' => $this->authUser
        );
        
        $sent = DB_DataObject::factory($_REQUEST['_table'])->send($content);
        
        if(!is_object($sent)){
            $this->jok('SUCCESS');
        }
        $this->jerr('error!!:' . $sent->toString());
        
    }
}

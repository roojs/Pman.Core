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
        
        if(!empty($_REQUEST['_send_test']) && $_REQUEST['core_email'])){
            $this->sendTest();
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
    
    function sendTest()
    {
        $table = DB_DataObject::factory($_REQUEST['_table']);
        
        if($table->get($_REQUEST['_id'])){
            $this->jerr('Invalid Message ID');
        }
        
        if(empty($table->test_class)){
            
        }
    }
}

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
        
        if($_REQUEST['_table'] == 'core_email'){
            $this->coreEmailSendTest();
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
    
    function coreEmailSendTest()
    {
        $core_email = DB_DataObject::factory('core_email');
        
        if(!$core_email->get($_REQUEST['_id'])){
            $this->jerr('Invalid Message ID');
        }
        
        if(empty($core_email->test_class)){
            $this->jerr("[{$core_email->name}] does not has test class");
        }
        
        require_once "{$core_email->test_class}.php";
        
        $cls = str_replace('/', '_', $core_email->test_class);
        
        $x = new $cls;
        
        if(!method_exists($x, "test_{$core_email->name}")){
            $this->jerr("Function test_{$core_email->name} does not exists");
        }
        
    }
}

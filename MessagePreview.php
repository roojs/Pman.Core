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
            $this->jerr('id got error');
        }
        
        $mlq = DB_DataObject::factory('core_mailing_list_message');
        
        $mlq->get($_REQUEST['_id']);
        
        $this->msg = $mlq;

        $this->showHtml = isset($_REQUEST['_as_html']) ? true : false;
        
    }
    
    function post()
    {
        if(empty($_REQUEST['_id'])){
            $this->jerr('id got error');
        }
        if(empty($_REQUEST['_action'])){
            $this->jerr('preview type not set');
        }
        $mid = $_REQUEST['_id'];
        
        $cfg = HTML_FlexyFramework::get()->Pman_Crm;
        
       
       
        $q = DB_DataObject::factory('crm_mailing_list_queue');
        $q->id = 'test-message-'. $mid;
        $q->message_id = $mid;
        $q->message_id_subject = $q->message()->subject;
        $q->message_id_from_email = $cfg['from_email'];
        $q->message_id_from_name = "Email Test"; 
        
        $q->cachedMailWithOutImages(true);
        $r = $q->getMailerObject($this->authUser, false, false, true);
        //print_r($r->toData());
        $ret = $r->toData();
        $images = file_get_contents(session_save_path() . '/email-cache-' . getenv('APACHE_RUN_USER') . '/mail/' . $q->id . '-images.txt');
       // var_dump($images);exit;
        
        $ret['body'] = str_replace('%Images%', $images, $ret['body']);
        
        $sent = $r->send($ret);
        if(!is_object($sent)){
            $this->jok('SUCCESS');
        }
        $this->jerr('error!!:' . $sent->toString());
        
    }
    
}

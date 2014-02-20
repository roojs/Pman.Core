<?php

require_once 'Pman.php';

class Pman_Crm_MessagePreview extends Pman
{
    var $masterTemplate = 'mail/rich_sample.html';
    
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
        $mlq = DB_DataObject::factory('crm_mailing_list_message');
        
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
        /*
        $ret = $r->toData();
        
        //print_r($ret);exit;
        $images = file_get_contents(session_save_path() . '/email-cache-' . get_current_user() . '/mail/' . $this->id . '-images.txt');
       // var_dump($images);exit;
        
        $ret['body'] = str_replace('%Images%', $images, $ret['body']);
        return $ret;
        
       
       
       
       
        $message = DB_DataObject::factory('crm_mailing_list_message');
        if(!$message->get($mid)){
            $this->jerr("Error occour on getting message!");
        }
        $mm = clone($message);
        $message->processRelacements($this->authUser);

        $ids = $mm->attachmentIds();
        //$this->jerr(print_r($ids,true));
        
        
        
        
        
        
        
        
        
        
         require_once 'Pman/Core/Mailer.php';
        $i = DB_DataObject::factory('Images');
        $i->onid = $mid;
        $i->ontable = 'crm_mailing_list_message';
        $i->whereAddIn('id', $ids, 'int');
        $i->find();
        $attachment = array();
        while ($i->fetch()){
            $ii = clone($i);
            require_once 'File/MimeType.php';
            $y = new File_MimeType();
            $ii->ext = $y->toExt(trim((string) $ii->mimetype ));
            $ii->file = chunk_split(base64_encode(file_get_contents($ii->getStoreName())));
            $attachment[] = $ii;
        }
        
        $cfg = HTML_FlexyFramework::get()->Pman_Crm;
        
        
        $template = ($_REQUEST['_action'] == 'html') ? 'mixedMail' : 'plainMail';
        
        $random_hash = md5(date('r', time()));
        $r = new Pman_Core_Mailer(array(
            'template'=> $template,
            'page' => $this,
            'contents' => array(
                'random_hash' => $random_hash,
                'person' => $this->authUser,
                'data' => $message,
                'attach' => $attachment,
                'from' => '<'.$cfg['from_email'].'>'
            )
        ));
        //print_R($r->toData());
        $sent = $r->send();
        if(!is_object($sent)){
            $this->jok('SUCCESS');
        }
        $this->jerr('error!!:' . $sent->toString());
        */
        
    }
    
}

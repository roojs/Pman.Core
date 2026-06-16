<?php

require_once 'Pman.php';

class Pman_Core_MessagePreview extends Pman
{
    var $masterTemplate = 'mail/MessagePreview.html';
    
    
    var $showHtml;
    var $msg;
    
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
    
    function get($v, $opts=array())
    {
 
        if((empty($_REQUEST['_id']) && empty($_REQUEST['template_name']) )|| empty($_REQUEST['_table'])){
            $this->jerr('Missing Options');
        }
        
        $mlq = DB_DataObject::factory($_REQUEST['_table']);
        if (!empty($_REQUEST['template_name'])) {
            $res = $mlq->get('name', $_REQUEST['template_name']);
        } else {
            $res = $mlq->get($_REQUEST['_id']);
        }
        if (!$res) {
            $this->jerr("invalid id/name");
        }
        
        $this->showHtml = isset($_REQUEST['_as_html']) ? true : false;
        
        
        if (isset($_REQUEST['ontable']) && !empty($_REQUEST['onid']) && !empty($_REQUEST['evtype'])) {
            $tn = preg_replace('/[^a-z_]+/i', '', $_REQUEST['ontable']);
            
            $t = DB_DataObject::factory($tn);
            if (!is_a($t, 'DB_DataObject') && !is_a($t, 'PDO_DataObject')) {
                $this->jerr("invalid URL");
            }
            if (!$t->get($_REQUEST['onid'])) {
                $this->jerr("invalid id");
            }
            if (!method_exists($t,'notify'.$_REQUEST['evtype'])) {
                $this->jerr("invalid evtype");
            }
              
            $m = 'notify'.$_REQUEST['evtype'];
            $this->msg = (object)$t->$m('test@test.com', false, false, false);
           // print_R($this->msg->mailer );
            $this->msg->subject = $this->msg->headers['Subject'];
            $this->msg->from_email = $mlq->from_email;
            $this->msg->from_name = $mlq->from_name;
            $this->msg->plaintext  = $this->msg->mailer->textbody ;
            $this->msg->bodytext = $this->msg->mailer->htmlbody;
            $this->msg->rcpts = $this->msg->mailer->rcpts;
            // htmlbody 
            //$this->plaintext = 
            //$data->subject = $data['Subject;
             
            
             

            return;
        }
        if (!empty($_REQUEST['data'])) {
            $data = json_decode($_REQUEST['data']);
            // echo '<PRE>';print_R($data);
            $md = $mlq->toMailerData($data);
           // echo '<PRE>';  print_r($md['mailer']);exit;
            $this->msg = $mlq;
            $this->msg->mailer = (object)$md['mailer'];
           // echo '<PRE>';  print_r($this->msg);exit;
             $this->msg->plaintext  = $this->msg->mailer->textbody ;
            $this->msg->bodytext = $this->msg->mailer->htmlbody;
            $this->msg->subject = $mlq->subject;
            $this->msg->rcpts = "send to <these@people>";
            $this->msg->from_email = $mlq->from_email;
            $this->msg->from_name = $mlq->from_name;
         /*
             
            //$this->msg->mailer = $mlq->toMailerData(json_decode($_REQUEST['data']));
            
            $this->msg->subject = $mlq->subject;
            $this->msg->from_email = $mlq->from_email;
            $this->msg->from_name = $mlq->from_name;
            $this->msg->plaintext  = $this->msg->mailer->textbody ;
            $this->msg->bodytext = $this->msg->mailer->htmlbody;
            $this->msg->rcpts = empty($this->msg->mailer->rcpts) ? "test@test.com" :
                    $this->msg->mailer->rcpts;
            */
           // echo '<PRE>'; print_R($this->msg);
            return;
            
        }
        $this->msg = $mlq;
        $this->msg->rcpts = "send to <these@people>";
      
        
    }
    
    function post($v)
    {
        if(empty($_REQUEST['_id']) || empty($_REQUEST['_table'])){
            $this->jerr('Missing Options');
        }
        
        if($_REQUEST['_table'] == 'core_email'){
            $this->coreEmailSendTest();
        }
        
        $cn = DB_DataObject::factory('core_notify');
        $cn->setFrom(array(
            'evtype'        => "{$_REQUEST['_table']}::SendPreviewEmail",
            'onid'          => $_REQUEST['_id'],
            'ontable'       => $_REQUEST['_table'],
            'person_id'     => $this->authUser->id,
            'person_table'  => 'Person',
            'act_when'      => $cn->sqlValue("NOW() + INTERVAL 10 MINUTE"),
            'act_start'     => $cn->sqlValue("NOW() + INTERVAL 10 MINUTE")
        ));
        
        $cn->insert();
        
        $sent = $cn->sendManual();
        
        if(get_class($sent) != 'Pman_Core_NotifySend_Exception_Success'){
            $this->jerr($sent->getMessage());
        }
        
        $this->jok("SUCCESS");
        
        /*
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
        */
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
        
        $method = "test_{$core_email->name}";
        
        if(!method_exists($x, $method)){
            $this->jerr("{$method} does not exists in {$cls}");
        }
        /*
        $content = $x->{$method}($this, $this->authUser);
        
        $content['bcc'] = array();
        */
        
        
        $cn = DB_DataObject::factory('core_notify');
        $cn->setFrom(array(
            'evtype'        => 'Core_email::testData',
            'onid'          => $_REQUEST['_id'],
            'ontable'       => $_REQUEST['_table'],
            'person_id'     => $this->authUser->id,
            'person_table'  => 'Person',
            'act_when'      => $cn->sqlValue("NOW()"),
            'act_start'     => $cn->sqlValue("NOW()"),
            'email_id'      => $core_email->id
        ));
        
        $cn->insert();
        
        $sent = $cn->sendManual();
        
        if(get_class($sent) != 'Pman_Core_NotifySend_Exception_Success'){
            $this->jerr($sent->getMessage());
        }
        
        $this->jok("SUCCESS");
        
        
        
        
        
        $sent = $core_email->send($content);
        
        if(is_object($sent)){
            $this->jerr("Error sending email - " . $sent->toString());
        }
        
        $this->jok('SUCCESS');
    }
}

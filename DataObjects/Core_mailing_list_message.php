<?php
/**
 * Table Definition for core_mailinglist_message
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_mailing_list_message extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */
  
    public $__table = 'core_mailing_list_message';    // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $name;                            // 
    public $subject;                         // blob(65535)  blob
    public $bodytext;                        // blob(65535)  blob
    public $plaintext;
    public $updated_dt;                      //datetime not_null
    public $from_email;
    public $from_name;
    public $owner_id;
    public $is_system;

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function applyFilters($q, $au, $roo)
    {
        if(!empty($q['search']['nameortitle'])){
            $this->whereAdd("
                name LIKE '%{$this->escape($q['search']['nameortitle'])}%'
                OR
                subject LIKE '%{$this->escape($q['search']['nameortitle'])}%'
            ");
        }
    }
    
    function beforeDelete($dependants_array, $roo)
    {   
        $i = DB_DataObject::factory('Images');
        $i->onid = $this->id;
        $i->ontable = $this->tableName();
        $i->find();
        while ($i->fetch()){
            $i->beforeDelete();
            $i->delete();
        }
        
        $mlq = DB_DataObject::factory('crm_mailing_list_queue');
        $mlq->message_id = $this->id;
        $mlq->find();
        while ($mlq->fetch()){
            $mlq->beforeDelete();
            $mlq->delete();
        }
        
        
    }
    function beforeUpdate($old, $request,$roo)
    {   
        if (!empty($request['_make_copy'])) {
            $this->makeCopy($roo);
            
        }
         
       
    }
    
    function makeCopy($roo)
    {
        $c = DB_DataObject::Factory($this->tableName());
        $c->setFrom($this);
        $c->name = "COPY of " . $this->name;
        $c->updated_dt = $this->sqlValue('NOW()');
        
        $id = $c->insert();
        $c = DB_DataObject::Factory($this->tableName());
        $c->get($id);
        
        
        // copy images.
        
        $i = DB_DataObject::factory('Images');
        $i->onid = $this->id;
        $i->ontable = $this->tableName();
        $i->find();
        while ($i->fetch()){
            
            $new_image = DB_DataObject::factory('Images');
            $new_image->onid = $c->id;
            $new_image->ontable = $this->tableName();
            $new_image->createFrom($i->getStoreName(), $i->filename);
            
            $map[$i->id] = $new_image->id;
        }
        
        
        
        libxml_use_internal_errors (true);
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML('<?xml encoding="UTF-8"><HTML><BODY>'.$this->bodytext.'</BODY></HTML>');
        $doc->formatOutput = true;

       //echo '<PRE>'; print_R($doc);
        
        
        $xpath = new DOMXpath($doc);
        foreach ($xpath->query('//img[@src]') as $img) {
            $href = $img->getAttribute('src');
            //var_dump($href);
            $matches = array();
            if (preg_match("/Images\/([0-9]+)\/([^#]+)\#attachment\-([0-9]+)$/", $href, $matches)) {
                 
                $oid = $matches[1];
                
                if (!isset($map[$oid])) {
                    //echo "skip no new id for $oid";
                    continue;
                }
                $nid = $map[$oid];
                $nstr = "/Images/$nid/{$matches[2]}/#attachment-{$nid}";
                $img->setAttribute('src',  str_replace($href, $matches[0], $nstr ));
                    
                 
            }
        }
        $cc = clone($c);
        $c->bodytext = $doc->saveHTML();
        $c->update($cc);
        libxml_use_internal_errors (false);
        
        
        $roo->jok("duplicated");
        
        
        
        
    }
    
    
    function onInsert($request,$roo)
    {   
        $i = DB_DataObject::factory('Images');
        $i->whereAdd('onid = 0');
        $i->find();
        while ($i->fetch()){
            $i->onid = $this->id;
            $i->update();
        }
       
    }
    
    
    function attachmentIds()
    {
        
         $roo = HTML_FlexyFramework::get()->page;
        
        libxml_use_internal_errors (true);
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML('<?xml encoding="UTF-8">'.$this->bodytext);
        
        $xpath = new DOMXpath($doc);
        $ret = array();
        
        foreach ($xpath->query('//img[@src]') as $img) { // process images!
            $href = $img->getAttribute('src');
            $cid = explode('#', $href);
            if(!isset($cid[1])){
                continue;
            }
            $cid = explode('-', $cid[1]);
            if (!isset($cid[1])||!is_numeric($cid[1])) {
                continue;
            }
            $ret[] = $cid[1];
        }
       // print_r($ret);
        return $ret;
    }
    /**
     * process replacements is run to generate a template - not the final content..
     *
     */
    
    function processRelacements($replace_links = true)
    {
        $roo = HTML_FlexyFramework::get()->page;
        
        $cfg = HTML_FlexyFramework::get()->Pman_Crm;
        
        libxml_use_internal_errors (true);
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML('<?xml encoding="UTF-8">'.$this->bodytext);
        
        $xpath = new DOMXpath($doc);
        
        foreach ($xpath->query('//img[@src]') as $img) { // process images!
            $href = $img->getAttribute('src');
            $cid = explode('#', $href);
            if(isset($cid[1])){
                $img->setAttribute('src', 'cid:' . $cid[1]);
            }
        }
        $unsubscribe = $cfg ['server_baseurl'] . '/Crm/Unsubscribe/' . $this->id . '/{person.id}';
        
       
        foreach ($xpath->query('//a[@href]') as $a) { 
            
            $href = $a->getAttribute('href');
            
            if(preg_match('/#unsubscribe/', $href)){
                $a->setAttribute('href', $unsubscribe);
                continue;
            }
            
            if(!preg_match('/^http(.*)/', $href)){
                continue;
            }
            if (!$replace_links) {
                continue;
            }
            $link = DB_DataObject::factory('crm_mailing_list_link');
            $link->setFrom(array(
                'url' => $href
            ));
            
            if(!$link->find(true)){
                $link->insert();
            }
            
            if(!$link->id){
                continue;
            }
            
            $l = $cfg ['server_baseurl'] . '/Crm/Link/' .$this->id . '/' . $link->id . '/{person.id}.html';
            
            $a->setAttribute('href', $l);
            
        }
        
        $element = $doc->createElement('img');
        
        $element->setAttribute('src', $cfg ['server_baseurl']  . '/Crm/Open/' . $this->id . '/{person.id}.html');
        $element->setAttribute('width', '1');
        $element->setAttribute('height', '1');
        
        $html = $doc->getElementsByTagName('html');
        $html->item(0)->appendChild($element);
        
        $this->bodytext = $doc->saveHTML();
        
        libxml_use_internal_errors (false);
        
        /*
        $this->bodytext = str_replace("{person.firstname}", htmlspecialchars($person->firstname), $this->bodytext);
        $this->bodytext = str_replace("{person.lastname}", htmlspecialchars($person->lastname), $this->bodytext);
        $this->bodytext = str_replace("{person.name}", htmlspecialchars($person->name), $this->bodytext);
         
        
        $this->plaintext = str_replace("{person.firstname}", $person->firstname, $this->plaintext);
        $this->plaintext = str_replace("{person.lastname}", $person->lastname, $this->plaintext);
        $this->plaintext = str_replace("{person.name}", $person->name, $this->plaintext);
        */
        $this->plaintext = str_replace("{unsubscribe_link}", $unsubscribe, $this->plaintext);
        $this->bodytext = str_replace('%7B', '{', $this->bodytext ); // kludge as template is not interpretated as html.
        $this->bodytext = str_replace('%7D', '}', $this->bodytext ); // kludge as template is not interpretated as html.
         
        
        
        return;
    }
    function send($obj)
    {
        
         
        $contents = (array)$obj;
        
        $q = DB_DataObject::factory('crm_mailing_list_queue');
        $q->id = 'test-message-'. $this->id;
        $q->message_id = $this->id;
        $q->message_id_subject = $this->subject;
        $q->message_id_from_email = $this->from_email;
        $q->message_id_from_name = $this->from_name;
        
        $q->cachedMailWithOutImages(true, false);
        
        $contents['subject'] = $this->subject;
        
        require_once 'Pman/Core/Mailer.php';
        
        $templateDir = session_save_path() . '/email-cache-' . get_current_user() ;
        $r = new Pman_Core_Mailer(array(
            'template'=> $q->id,
            'templateDir' => $templateDir,
            'page' => $q,
            'contents' => $contents
            //array(
            //    'person' => $person,
            //    'subject' => $this->message_id_subject,
           // )
        ));
        
        
         
        ///print_r($r->toData());
        $ret = $r->toData();
        $images = file_get_contents(session_save_path() . '/email-cache-' . get_current_user() . '/mail/' . $q->id . '-images.txt');
       // var_dump($images);exit;
        
        $ret['body'] = str_replace('%Images%', $images, $ret['body']);
        
        return $r->send($ret);
    }
    
}

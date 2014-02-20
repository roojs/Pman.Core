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
        $tn = $this->tableName();
        
        if(!empty($q['search']['nameortitle'])){
            $this->whereAdd("
                $tn.name LIKE '%{$this->escape($q['search']['nameortitle'])}%'
                OR
                $tn.subject LIKE '%{$this->escape($q['search']['nameortitle'])}%'
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
            if (preg_match("/Images\/([0-9]+)\/([^#]+)\#image\-([0-9]+)$/", $href, $matches)) {
                 
                $oid = $matches[1];
                
                if (!isset($map[$oid])) {
                    //echo "skip no new id for $oid";
                    continue;
                }
                $nid = $map[$oid];
                $nstr = "/Images/$nid/{$matches[2]}/#image-{$nid}";
                
                $img->setAttribute('src',  str_replace($matches[0], $nstr, $href ));
                    
                 
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
        $i->onid = 0;
        $i->ontable = $this->tableName();
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
        
        $this->bodytext = $doc->saveHTML();
        
        libxml_use_internal_errors (false);
        
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
        
        $templateDir = session_save_path() . '/email-cache-' . getenv('APACHE_RUN_USER') ;
        $r = new Pman_Core_Mailer(array(
            'template'=> $q->id,
            'templateDir' => $templateDir,
            'page' => $q,
            'contents' => $contents
            
        ));
        
        $ret = $r->toData();
        
        $images = file_get_contents(session_save_path() . '/email-cache-' . getenv('APACHE_RUN_USER') . '/mail/' . $q->id . '-images.txt');
        
        $ret['body'] = str_replace('%Images%', $images, $ret['body']);
        
        return $r->send($ret);
    }
    
}

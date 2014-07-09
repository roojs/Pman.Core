<?php
/**
 * Table Definition for core_email
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Core_email extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */
  
    public $__table = 'core_email';    // table name
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
    
    function onInsert($q,$roo)
    {   
        $i = DB_DataObject::factory('Images');
        $i->onid = 0;
        $i->ontable = $this->tableName();
        $i->find();
        while ($i->fetch()){
            $ii = clone ($i);
            $i->onid = $this->id;
            $i->update($ii);
        }
        
        $this->cachedMailWithOutImages(true, (get_class($this) == 'Pman_Core_DataObjects_Core_email') ? false : true);
//        $this->cachedMailWithOutImages(true, false);
       
    }
    
    function onUpdate($old, $q,$roo)
    {
        $this->cachedMailWithOutImages(true, (get_class($this) == 'Pman_Core_DataObjects_Core_email') ? false : true);
    }


    function attachmentIds()
    {
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
        
        return $ret;
    }
    /**
     * process replacements is run to generate a template - not the final content..
     *
     */
    
    function processRelacements($replace_links = true)
    {   
        $cfg = isset(HTML_FlexyFramework::get()->Pman_Crm) ? HTML_FlexyFramework::get()->Pman_Crm : false;
        
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
        
        $unsubscribe = false;
        
        if(!empty($cfg)){
            $unsubscribe = $cfg ['server_baseurl'] . '/Crm/Unsubscribe/' . $this->id . '/{person.id}';
        }
        print_r($unsubscribe);exit;
        foreach ($xpath->query('//a[@href]') as $a) { 
            
            $href = $a->getAttribute('href');
            
            if(preg_match('/#unsubscribe/', $href) && !empty($unsubscribe)){
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
        
        if(!empty($unsubscribe)){
            $element = $doc->createElement('img');
        
            $element->setAttribute('src', $cfg ['server_baseurl']  . '/Crm/Open/' . $this->id . '/{person.id}.html');
            $element->setAttribute('width', '1');
            $element->setAttribute('height', '1');

            $html = $doc->getElementsByTagName('html');
            $html->item(0)->appendChild($element);
            
            $this->plaintext = str_replace("{unsubscribe_link}", $unsubscribe, $this->plaintext);
        }
        
        
        $this->bodytext = $doc->saveHTML();
        
        libxml_use_internal_errors (false);
        
        $this->bodytext = str_replace('%7B', '{', $this->bodytext ); // kludge as template is not interpretated as html.
        $this->bodytext = str_replace('%7D', '}', $this->bodytext ); // kludge as template is not interpretated as html.
         
        return;
    }
    
    function send($obj, $force = true, $send = true)
    {   
        $this->processRelacements(true);
        exit;
        $contents = (array)$obj;
        
        if(empty($this->id)){
            $this->get('name', $contents['template']);
        }
        
        if(empty($this->id)){
            return PEAR::raiseError("template [{$contents['template']}]has not been set");
        }
        if(empty($contents['subject'])){
           $contents['subject'] = $this->subject; 
        }
        
        $ui = posix_getpwuid(posix_geteuid());
        
        $cachePath = session_save_path() . '/email-cache-' . $ui['name'] . '/mail/' . $this->tableName() . '-' . $this->id . '.txt';
        
        if($force || !$this->isGenerated($cachePath)){
            $this->cachedMailWithOutImages($force, empty($contents['replace_links']) ? false : $contents['replace_links']);
        }
         
        require_once 'Pman/Core/Mailer.php';
        
        $templateDir = session_save_path() . '/email-cache-' . $ui['name'] ;
        
        $r = new Pman_Core_Mailer(array(
            'template'=> $this->tableName() . '-' . $this->id,
            'templateDir' => $templateDir,
            'page' => $this,
            'contents' => $contents
        ));
        
        $imageCache = session_save_path() . '/email-cache-' . $ui['name'] . '/mail/' . $this->tableName() . '-' . $this->id . '-images.txt';
        
        if(file_exists($imageCache) && filesize($imageCache)){
            $images = json_decode(file_get_contents($imageCache), true);
            $r->images = $images;
        }
        
        $ret = $r->toData();
        
        if(!$send){
            return $ret;
        }
        
        return $r->send($ret);
    }
    
    function cachedMailWithOutImages($force = false, $replace_links = true)
    {  
        
        
        $ui = posix_getpwuid(posix_geteuid());
        
        $cachePath = session_save_path() . '/email-cache-' . $ui['name'] . '/mail/' . $this->tableName() . '-' . $this->id . '.txt';
          
        if ($this->isGenerated($cachePath)) {
            return;
        }
        
        if (!file_exists(dirname($cachePath))) {
            mkdir(dirname($cachePath), 0700, true);
        }
        
        $random_hash = md5(date('r', time()));
        
        $this->cachedImages();
        
        $fh = fopen($cachePath, 'w');

        fwrite($fh, implode("\n", array(
            "From: {if:t.messageFrom}{t.messageFrom:h}{else:}{t.messageFrom():h}{end:}",
            "To: {if:t.person}{t.person.getEmailFrom():h}{else:}{foreach:rcpts,v}{v:h},{end:}{end:}",
            "{if:t.replyTo}Reply-To: {t.replyTo:h}{end:}",
            "Subject: {t.subject} ",
            "X-Message-ID: {t.id} "
        ))."\n");
        
        
// note the extra space to finish the last line..
        fwrite($fh, " " . "
Content-Type: multipart/alternative; boundary=alt-{$random_hash}

--alt-{$random_hash}
Content-Type: text/plain; charset=utf-8; format=flowed
Content-Transfer-Encoding: 7bit

{$this->plaintext}

");
        fclose($fh);
        
        // cache body
        
        $this->processRelacements($replace_links);
        
        $cachePath = session_save_path() . '/email-cache-' . $ui['name'] . '/mail/' . $this->tableName() . '-' . $this->id . '.body.html';
        
        if (!file_exists(dirname($cachePath))) {
            mkdir(dirname($cachePath), 0700, true);
        }
        
        file_put_contents($cachePath, $this->bodytext);
        
    }
    
    function cachedImages()
    {
        $ui = posix_getpwuid(posix_geteuid());
        
        $imageCache = session_save_path() . '/email-cache-' . $ui['name'] . '/mail/' . $this->tableName() . '-' . $this->id . '-images.txt';
        
        $ids = $this->attachmentIds();
        
         
        $fh = fopen($imageCache, 'w');
        
        $i = DB_DataObject::factory('Images');
        $i->onid = $this->id;
        $i->ontable = $this->tableName();
        $i->whereAddIn('id', $ids, 'int');
        $i->find();
        
        $images = array();
        
        require_once 'File/MimeType.php';
        $y = new File_MimeType();
        
        while ($i->fetch()){
            if (!file_exists($i->getStoreName()) || !filesize($i->getStoreName())) {
                continue;
            }
            
            $images["attachment-$i->id"] = array(
                'file' => $i->getStoreName(),
                'mimetype' => $i->mimetype,
                'ext' => $y->toExt($i->mimetype),
                'contentid' => "attachment-$i->id"
            );
        }
            
        file_put_contents($imageCache, json_encode($images));
        
    }
    
    function isGenerated($cachePath)
    {
        if (!file_exists($cachePath) || !filesize($cachePath)) {
            return false;
        }
        
        
        $ctime = filemtime($cachePath);
        $mtime = array();
        $mtime[] = $this->updated_dt;
        $i = DB_DataObject::factory('Images');
        $i->onid = $this->id;
        $i->ontable = $this->tableName();
        $i->selectAdd();
        $i->selectAdd('max(created) as created');
        $i->find(true);
        $mtime[] = $i->created;
        if($ctime >= strtotime(max($mtime))){
            return true;
        }
        
        return false;
    }
    
    function messageFrom()
    {
        return '"' . addslashes($this->from_name) . '" <' . $this->from_email. '>'  ;
    }
    
    function formatDate($dt, $format = 'd/M/Y')
    {
        return date($format, strtotime($dt));
    } 
    
    
    
}

<?php
/**
 * Table Definition for core_email
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

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
    public $active;
    public $bcc_group_id;
    public $test_class;
     
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
        $cgm = DB_DataObject::Factory('core_group_member')->tableName();;
      
        $this->selectAdd("
            (
                SELECT 
                    count(id) 
                FROM 
                    {$cgm}
                WHERE 
                    to_group_id = {$cgm}.group_id
            )  AS group_member_count,
            
            (
                SELECT 
                    count(id) 
                FROM 
                    {$cgm}
                WHERE 
                    bcc_group_id = {$cgm}.group_id
            )  AS bcc_group_member_count
        ");

	
	if (!empty($_REQUEST['_hide_system_emails'])) {
	    $this->whereAddIn("!{$this->tableName()}.name", array('EVENT_ERRORS_REPORT'), 'string');
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
        
        if ($this->to_group_id != -1) {
        	   
            $c = DB_DataObject::factory('core_group_member');            
            $c->group_id = $this->to_group_id;

            $cg = DB_DataObject::factory('core_group');
                        
            if (($cg->get($this->to_group_id) && $cg->name == 'Empty Group') || !$c->count() && empty($request['_ignore_group_count'])) {
                $roo->jerr('Failed to create email template - No member found in recieptent group',array('errcode'=> 100));
            }
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
            $hash = explode('#', $href);
            // we name all our cid's as attachment-*
            // however the src url may be #image-*
            
            
            if(!isset($hash[1])){
                continue;
            }
            $cid = explode('-', $hash[1]);
            if(!empty($cid[1])){
                $img->setAttribute('src', 'cid:attachment-' . $cid[1]);
            }
        }
        
        $unsubscribe = $this->unsubscribe_url();
        
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
	    if (empty($cfg)) {
		continue;
	    }
	    // not available if server_baseurl not set... and crm module not used.
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
        
        if(!empty($unsubscribe) && !empty($cfg)){
            $element = $doc->createElement('img');
            $element->setAttribute('mailembed', 'no');
            $element->setAttribute('src', $cfg ['server_baseurl']  . '/Crm/Open/' . $this->id . '/{person.id}.html');
            $element->setAttribute('width', '1');
            $element->setAttribute('height', '1');

            $html = $doc->getElementsByTagName('html');
            if ($html->length) {
                $html->item(0)->appendChild($element);
            }
            
            $this->plaintext = str_replace("{unsubscribe_link}", $unsubscribe, $this->plaintext);
        }
        
        
        $this->bodytext = $doc->saveHTML();
        
        libxml_use_internal_errors (false);
        
        $this->bodytext = str_replace('%7B', '{', $this->bodytext ); // kludge as template is not interpretated as html.
        $this->bodytext = str_replace('%7D', '}', $this->bodytext ); // kludge as template is not interpretated as html.
         
        return;
    }
    
    function unsubscribe_url()
    {
        $unsubscribe = false;
        
        $cfg = isset(HTML_FlexyFramework::get()->Pman_Crm) ? HTML_FlexyFramework::get()->Pman_Crm : false;
        
        if(!empty($cfg)){
            $unsubscribe = $cfg ['server_baseurl'] . '/Crm/Unsubscribe/' . $this->id . '/{person.id}';
        }
        
        return $unsubscribe;
    }
    
    /**
     * convert email with contents into a core mailer object. - ready to send..
     * @param Object|Array $obj Object (or array) to send @see Pman_Core_Mailer
     *    + subject
     *    + rcpts || person   << if person is set - then it goes to them...
     *    + rcpts_group (string) << name of group - normally to send admin emails.. (if set, then bcc_group is ignored.)
     *    + replace_links
     *    + template
     *    + mailer_opts
     *    + person << who it actually goes to..
     *    
     * @param bool $force - force re-creation of cached version of email.
     *
     * @returns Pman_Core_Mailer||PEAR_Error
     */
    
    function toMailer($obj,$force=false)
    {
        require_once 'PEAR.php';
        
        $p = new PEAR();
        $contents = (array)$obj;
        
        if(empty($this->id) && !empty($contents['template'])){
            $this->get('name', $contents['template']);
        }
             
        
        if(empty($this->active)){
            return $p->raiseError("template [{$contents['template']}] is Disabled");
        }
        
        
        if(empty($this->id)){
            return $p->raiseError("template [{$contents['template']}] has not been set");
        }
        
        // fill in BCC
        
        if (!empty($this->bcc_group_id) && $this->bcc_group_id > 0 && empty($contents['bcc']) && empty($contents['rcpts_group'])) {
            $admin_grp = DB_DAtaObject::Factory('core_group')->load($this->bcc_group_id);
	    
	    $admin = $admin_grp ?  $admin_grp->members('email') : false;
            
            if (empty($admin) && $admin_grp->name != 'Empty Group') { // allow 'empty group mname'
                return $p->raiseError("template [{$contents['template']}] - bcc group is empty");
            }
            
            $contents['bcc'] = $admin ;
        }
        if (!empty($contents['rcpts_group'])) {
            
            $admin = DB_DAtaObject::Factory('core_group')->lookupMembers($contents['rcpts_group'],'email');
            
            if (empty($admin)) {
                return $p->raiseError("Trying to send to {$contents['rcpts_group']} - group is empty");
            }
            $contents['rcpts'] = $admin;
        }
        if (empty($contents['rcpts']) && $this->to_group_id > 0) {
	    $members = $this->to_group()->members();
	    $contents['rcpts'] = array();
	    foreach($this->to_group()->members() as $m) {
		$contents['rcpts'][] = $m->email;
	    }
	    //var_dump($contents['rcpts']);
	    
	}
        //subject replacement
        if(empty($contents['subject'])){
           $contents['subject'] = $this->subject; 
        }
        
        if (!empty($contents['subject_replace'])) {
            
            // do not use the mapping 
            if (isset($contents['mapping'])) {
                foreach ($contents['mapping'] as $pattern => $replace) {
                    $contents['subject'] = preg_replace($pattern,$replace,$contents['subject']);
                }
            }
            
            foreach ($contents as $k => $v) {
                if (is_string($v)) {
                    $contents['subject'] = str_replace('{'. $k . '}', $v, $contents['subject']);
                }
            }
        }
        
        if(!empty($contents['rcpts']) && is_array($contents['rcpts'])){
            $contents['rcpts'] = implode(',', $contents['rcpts']);
        }     
        
        $ui = posix_getpwuid(posix_geteuid());
        
        $cachePath = session_save_path() . '/email-cache-' . $ui['name'] . '/mail/' . $this->tableName() . '-' . $this->id . '.txt';
        
        if($force || !$this->isGenerated($cachePath)){
            $this->cachedMailWithOutImages($force, empty($contents['replace_links']) ? false : $contents['replace_links']);
        }
         
        require_once 'Pman/Core/Mailer.php';
        
        $templateDir = session_save_path() . '/email-cache-' . $ui['name'] ;
        
        $cfg = array(
            'template'=> $this->tableName() . '-' . $this->id,
            'templateDir' => $templateDir,
            'page' => $this,
            'contents' => $contents,
            'css_embed' => true, // we should always try and do this with emails...
        );
        
        if (isset($contents['rcpts'])) {
            $cfg['rcpts'] = $contents['rcpts'];
        }
        
        if (isset($contents['attachments'])) {
            $cfg['attachments'] = $contents['attachments'];
        }
        
        if (isset($contents['mailer_opts']) && is_array($contents['mailer_opts'])) {
            $cfg = array_merge($contents['mailer_opts'], $cfg);
        }
        
        if(isset($contents['css_inline'])){
            $cfg['css_inline'] = $contents['css_inline'];
        }
        
        $r = new Pman_Core_Mailer($cfg);
        
        $imageCache = session_save_path() . '/email-cache-' . $ui['name'] . '/mail/' . $this->tableName() . '-' . $this->id . '-images.txt';
        
        if(file_exists($imageCache) && filesize($imageCache)){
            $images = json_decode(file_get_contents($imageCache), true);
            $r->images = $images;
        }
        
        return $r;
    }
    function toMailerData($obj,$force=false)
    {   
        $r = $this->toMailer($obj, $force);
        if (is_a($r, 'PEAR_Error')) {
            return $r;
        }
        return $r->toData();
    }
    
    /**
     *
     * DEPRICATED !!! - DO NOT USE THIS !!!
     *
     * use: toMailerData() -- to return the email data..
     * or
     * $mailer = $core_email->toMailer($obj, false);
     * $sent = is_a($mailer,'PEAR_Error') ? false : $mailer->send();

     * toMailer($obj, false)->send()
     *
     * 
     */
    
    function send($obj, $force = true, $send = true)
    {   
        if (!$send) {
            return $this->toMailerData($obj,$force);
        }
        
        $r = $this->toMailer($obj, $force);
        if (is_a($r, 'PEAR_Error')) {
            return $r;
        }
        
        return $r->send();
    }
    
    function cachedMailWithOutImages($force = false, $replace_links = true)
    {  
        
        $ui = posix_getpwuid(posix_geteuid());
        
        $cachePath = session_save_path() . '/email-cache-' . $ui['name'] . '/mail/' . $this->tableName() . '-' . $this->id . '.txt';
          
        if (!$force && $this->isGenerated($cachePath)) {
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
            "To: {if:t.person}{t.person.getEmailFrom():h}{else:}{rcpts:h}{end:}",
            "Subject: {t.subject:h} ",
            "X-Message-ID: {t.id} ",
            "{if:t.replyTo}Reply-To: {t.replyTo:h}{end:}",
            "{if:t.mailgunVariables}X-Mailgun-Variables: {t.mailgunVariables:h}{end:}"
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
        
        if (empty($this->use_file)) {
            file_put_contents($cachePath, $this->bodytext);
            return;
        }
        // use-file -- uses the original template...
        $mailtext = file_get_contents($this->use_file);        
         
        require_once 'Mail/mimeDecode.php';
        require_once 'Mail/RFC822.php';
        
        $decoder = new Mail_mimeDecode($mailtext);
        $parts = $decoder->getSendArray();
        file_put_contents($cachePath,$parts[2]);
         
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
            
            $images["attachment-{$i->id}"] = array(
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
        
        if (!empty($this->use_file)) {
            $ctime = filemtime($cachePath);
            $mtime = filemtime($this->use_file);
            if($ctime >= $mtime){
                return true;
            }
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
        if (empty($this->from_name)) {
            return trim($this->from_email);
        }
        return trim('"' . addslashes($this->from_name) . '" <' . $this->from_email. '>')  ;
    }
    
    function formatDate($dt, $format = 'd/M/Y')
    {
        return date($format, strtotime($dt));
    } 
    
    
     // fixme - this is now in core/udatedatabase..
    
    function initMail($mail_template_dir,  $name, $master='')
    {
        $cm = DB_DataObject::factory('core_email');
        if ($cm->get('name', $name)) {
            return;
        }
        
//        $basedir = $this->bootLoader->rootDir . $mail_template_dir;
        
        $opts = array();
        
        $opts['file'] = $mail_template_dir. $name .'.html';
        if (!empty($master)) {
            $opts['master'] = $mail_template_dir . $master .'.html';
        }
        //print_r($opts);
        require_once 'Pman/Core/Import/Core_email.php';
        $x = new Pman_Core_Import_Core_email();
        $x->get('', $opts);
         
    }
    
    
    function testData($person, $dt , $core_notify)
    {
	 
	// should return the formated email???
	$pg = HTML_FlexyFramework::get()->page;
	
	 
	
	
        if(empty($this->test_class)){
            $pg->jerr("[{$this->name}] does not has test class");
        }
        
        require_once "{$this->test_class}.php";
        
        $cls = str_replace('/', '_', $this->test_class);
        
        $x = new $cls;
        
        $method = "test_{$this->name}";
        
        if(!method_exists($x, $method)){
            $pg->jerr("{$method} does not exists in {$cls}");
        }
        
        $content = $x->{$method}($this, $person);
        $content['to'] = $person->getEmailFrom();

        $content['bcc'] = array();
	$data = $this->toMailerData($content);
    print_r($data);
    die('a');
 	return $data;
        
           
    }
    
    function to_group()
    {
	$g = DB_DataObject::Factory('core_group');
	$g->get($this->to_group_id);
	return $g;
    }
}

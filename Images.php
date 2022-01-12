<?php
/**
 * Deal with image delivery and HTML replacement of image links in body text.
 *
 *
 * NOTE THIS WAS NEVER INTENDED FOR PUBLIC IMAGE DISTRIBUTION - we need to create a seperate file for that...
 *
 * $str = Pman_Core_Images::replaceImg($str); // < use with HTML
 *
 * or
 *
 * Deliver image /file etc..
 * 
 * Use Cases:
 * 
 * args: ontable request
 *      ontable (req) tablename.
 *      filename
 *      (other table args)
 *      as (serve as a type) = eg. ?as=audio/mpeg 
 * 
 * args: generic
 *     as :(serve as a type) = eg. mimetype.
 * 
 * Images/{ID}/fullname.xxxx
 * 
 * (valid thumbs 200, 400)...?
 * Images/Thumb/200/{ID}/fullname.xxxx
 * Images/Download/{ID}/fullname.xxxx
 *
 *
 *
 * 
 * Used to be in Base... now in core..
 *
 * 
 * view permission should be required on the underlying object...
 * 
 */
require_once  'Pman.php';
class Pman_Core_Images extends Pman
{
    
    // tables that do not need authentication checks before serving.
    var $public_image_tables = array(
        'crm_mailing_list_message'   // we know these are ok...
    );
    
    var  $sizes = array(
                '100', 
                '100x100', 
                '150', 
                '150x150', 
                '200', 
                '200x0',
                '200x200',  
                '400x0',
                '300x100',
                '500'
            );
    function getAuth()
    {
        parent::getAuth(); // load company!
        //return true;
        $au = $this->getAuthUser();
        
        if (!$au) {
            $this->authUser = false;
            return true;//die("Access denied");
        }
        
        $this->authUser = $au;
        
        return true;
    }
    var $thumb = false;
    var $as_mimetype = false;
    var $method = 'inline';
    var $page = false;
    var $is_local = false;
    
    function get($s, $opts=array()) // determin what to serve!!!!
    {
        // for testing only.
        //if (!empty($_GET['_post'])) {
        //   return $this->post();
        //}
        
        $this->is_local = (!empty($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'localhost') ? true : false;
        
        $this->as_mimetype = empty($_REQUEST['as']) ? '' : $_REQUEST['as'];
        
        $this->page = empty($_REQUEST['page']) ? false : (int) $_REQUEST['page'];
        
        $bits= explode('/', $s);
        $id = 0;
//        var_dump($bits);die('in');
        // without id as first part...
        if (!empty($bits[0]) && $bits[0] == 'Thumb') {
            $this->thumb = true;
            $this->as_mimetype = 'image/jpeg';
            $this->size = empty($bits[1]) ? '0x0' : $bits[1];
            $id = empty($bits[2]) ? 0 :   $bits[2];
            
        } else if (!empty($bits[0]) && $bits[0] == 'Download') {
            $this->method = 'attachment';
            $id = empty($bits[1]) ? 0 :   $bits[1];
            
        } else  if (!empty($bits[1]) && $bits[1] == 'Thumb') { // with id as first part.
            $this->thumb = true;
            $this->as_mimetype = 'image/jpeg';
            $this->size = empty($bits[2]) ? '0x0' : $bits[2];
            $id = empty($bits[3]) ? 0 :   $bits[3];
            
        } else if (!empty($bits[0]) && $bits[0] == 'events') {
            if (!$this->authUser) {
                $this->imgErr("no-authentication-events",$s);
            }
            $this->downloadEvent($bits);
            $this->imgErr("unknown file",$s);
            
            
        } else {
        
            $id = empty($bits[0]) ? 0 :  $bits[0];
        }
        
        if (strpos($id,':') > 0) {  // id format  tablename:id:-imgtype
            
            if (!$this->authUser) {
                $this->imgErr("not-authenticated-using-colon-format",$s);
                
            }
            
            $onbits = explode(':', $id);
            if ((count($onbits) < 2)   || empty($onbits[1]) || !is_numeric($onbits[1]) || !strlen($onbits[0])) {
                $this->imgErr("bad-url",$s);
                
            }
            //DB_DataObject::debugLevel(1);
            $img = DB_DataObject::factory('Images');
            $img->ontable = $onbits[0];
            $img->onid = $onbits[1];
            if (empty($_REQUEST['anytype'])) {
                $img->whereAdd("mimetype like 'image/%'");
            }
            $img->orderBy('title ASC'); /// spurious ordering... (curretnly used by shipping project)
            if (isset($onbits[2])) {
                $img->imgtype = $onbits[2];
            }
            $img->limit(1);
            if (!$img->find(true)) {
                $this->imgErr("no images for that item: " . htmlspecialchars($id),$s);
                
            }
            
            $id = $img->id;
            
            
        }
        $id = (int) $id;
        
        // depreciated - should use ontable:onid:type here...
        if (!empty($_REQUEST['ontable'])) {
            
            if (!$this->authUser) {
                die("authentication required");
            }
            
            //DB_DataObjecT::debugLevel(1);
            $img = DB_DataObject::factory('Images');
            $img->setFrom($_REQUEST);
           
            
            
            $img->limit(1);
            if (!$img->find(true)) {
                $this->imgErr("No file exists",$s);
            } 
            $id = $img->id;
            
        }
        
        $img = DB_DataObjecT::factory('Images');
         
        if (!$id || !$img->get($id)) {
            $this->imgErr("image has been removed or deleted.",$s);
        }
        
        if($this->is_local) {
            return $this->serve($img);
        }
        
        if (!$this->authUser && !in_array($img->ontable,$this->public_image_tables)) {
            
            if ($img->ontable != 'core_company') {
                $this->imgErr("not-authenticated {$img->ontable}",$s);
            }
            if ($img->imgtype != 'LOGO') {
                $this->imgErr("not-logo",$s);
            }
            $comp  = $img->object();
            if ($comp->comptype != 'OWNER') {
                $this->imgErr("not-owner-company",$s);
            }
            
            return $this->serve($img);
            
        }
        
        if(!$this->hasPermission($img)){
            $this->imgErr("access to this image/file has been denied.",$s);
        }
        
        $this->serve($img);
        exit;
    }
    
    function imgErr($reason,$path) {
        header('Location: ' . $this->rootURL . '/Pman/templates/images/file-broken.png?reason=' .
            urlencode($reason) .'&path='.urlencode($path));
        exit;
    }
    
    function hasPermission($img) 
    {
        return true;
    }
    
    function post($v)
    {
        if (!empty($_REQUEST['_get'])) {
            return $this->get($v);
        }
        
        if (!$this->authUser) {
            $this->jerr("image conversion only allowed by registered users");
        }
        // converts a posted string (eg.svg)
        // into another type..
        if (empty($_REQUEST['as'])) {
           $this->jerr("missing target type");
        }
        if (empty($_REQUEST['mimetype'])) {
            $this->jerr("missing mimetype");
        }
        if (empty($_REQUEST['data'])) {
            $this->jerr("missing data");
        }
        
        
        $this->as_mimetype = $_REQUEST['as'];
        $this->mimetype = $_REQUEST['mimetype'];
        require_once 'File/MimeType.php';
        $y = new File_MimeType();
        $src_ext = $y->toExt( $this->mimetype );
        
        
        $tmp = $this->tempName($src_ext);
        file_put_contents($tmp, $_REQUEST['data']);
        
        require_once 'File/Convert.php';
        $cv = new File_Convert($tmp, $this->mimetype);
        
        $fn = $cv->convert(
                $this->as_mimetype ,
                empty($_REQUEST['width']) ? 0 : $_REQUEST['width'],
                empty($_REQUEST['height']) ? 0 : $_REQUEST['height']
        );
        if (!empty($_REQUEST['as_data'])) {
            $this->jok(base64_encode(file_get_contents($fn)));
        }
        
        $cv->serve('attachment');
        exit;
        
        
        
    }
    
    
 
    function serve($img)
    {
        $this->sessionState(0); // turn off session... - locking...
        
        require_once 'File/Convert.php';
        if (!file_exists($img->getStoreName())) {
//            print_r($img);exit;
            header('Location: ' . $this->rootURL . '/Pman/templates/images/file-broken.png?reason=' .
                urlencode("Original file was missing : " . $img->getStoreName()));
    
        }
//        print_r($img);exit;
        $x = $img->toFileConvert();
        if (empty($this->as_mimetype) || $img->mimetype == 'image/gif') {
            $this->as_mimetype  = $img->mimetype;
        }
        if (!$this->thumb) {
            if ($x->mimetype == $this->as_mimetype) {
                $x->serveOnly($this->method);
                exit;
            }
            $x->convert( $this->as_mimetype);
            $x->serve($this->method);
            exit;
        }
        //echo "SKALING?  $this->size";
        // acutally if we generated the image, then we do not need to validate the size..
        
        // if the mimetype is not converted..
        // then the filename should be original.{size}.jpeg
        $fn = $img->getStoreName() . '.'. $this->size . '.jpeg'; // thumbs are currenly all jpeg.!???
        
        if($img->mimetype == 'image/gif'){
            $fn = $img->getStoreName() . '.'. $this->size . '.gif';
        }
        
        if (!file_exists($fn)) {
            $fn = $img->getStoreName()  . '.'. $this->size . '.'. $img->fileExt();
            // if it's an image, convert into the same type for thumbnail..
            if (preg_match('#^image/#', $img->mimetype)) {
               $this->as_mimetype = $img->mimetype;
            }
        }
        
        if (!file_exists($fn)) {    
            $this->validateSize();
        }
        
        if(!empty($this->page) && !is_nan($this->page * 1)){
            $x->convert( $this->as_mimetype, $this->size, 0, $this->page);
        } else {
            $x->convert( $this->as_mimetype, $this->size);
        }
        
        $x->serve();
        exit;
        
        
        
        
    }
    function validateSize()
    {
        if($this->is_local) {
            return true;
        }
        
        if (($this->authUser && !empty($this->authUser->company_id) && $this->authUser->company()->comptype=='OWNER')
            || $_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR']) {
            return true;
        }
        
        
        $ff = HTML_FlexyFramework::get();
        
        $sizes= $this->sizes;
        
        $cfg = isset($ff->Pman_Images) ? $ff->Pman_Images :
                (isset($ff->Pman_Core_Images) ? $ff->Pman_Core_Images : array());
        
        if (!empty($cfg['sizes'])) {
            $sizes = array_merge($sizes , $cfg['sizes']);
        }
        
        $project = $ff->project;
        
        require_once $ff->project . '.php';
        
        $project = str_replace('/', '_', $project);
         
        $pr_obj = new $project;
         
       // var_dump($pr_obj->Pman_Core_Images_Size);
        if(isset($pr_obj->Pman_Core_Images_Size)){
            $sizes = $pr_obj->Pman_Core_Images_Size;
            
            
        }
        
        if (!in_array($this->size, $sizes)) {
            die("invalid scale - ".$this->size);
        }
    }
    /**
     * replace image urls
     *
     * The idea of this code was to replace urls for images when you have an admin
     * and a distribution page. with different urls.
     *
     * it may be usefull later if things like embedded images in emails. but
     * I think it's proably better not to use this.
     *
     * The key problem being how to determine if we are replacing 'our' images or some external one..
     * 
     *
     */
    
    
    static function replaceImageURLS($html, $obj = false)
    {
        
        $ff = HTML_FlexyFramework::get();
        if (!isset($ff->Pman_Images['public_baseURL'])) {
            return $html;
        }
        //var_dump($ff->Pman_Images['public_baseURL']);
        $baseURL = $ff->Pman_Images['public_baseURL'];
        
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML("<?xml encoding='utf-8'?> <div id='tmp_dom_wrapper'>{$html}</div>");
        $imgs = $dom->getElementsByTagName('img');
       
        
        foreach($imgs as $img) {
            $src = $img->getAttribute('src');
            if (!$src|| !strlen(trim($src))) {
                continue;
            }
             
            if (0 === strpos($src, 'data:')) {
                if (!$obj) {
                    HTML_FlexyFramework::get()->page->jerr("no object to attach data url");
                }
                
                self::replaceDataUrl($baseURL, $img, $obj);
                continue;
            }
            
            
            if (false !== strpos($src, '//') && false === strpos($src, $baseURL)) {
                // contains an absolute path.. and not our baseURL.
                continue;
            }
             
            $img->setAttribute('src', self::domImgUrl($baseURL, $img));
              
            // what about mailto or data... - just ignore?? for images...
            
            
        }
        
        $anchors = $dom->getElementsByTagName('a');
        $result = array();
        preg_match_all('/<a\s+[^>]+>/i',$html, $result); 

        $matches = array_unique($result[0]);
        foreach($anchors as $anc) {
            $href = $anc->getAttribute('href');
            if (!empty($href) || 0 !== strpos($href, $baseURL)) { 
                continue;
            }
            $anc->setAttribute('href', self::domImgUrl($baseURL, $href));
        }
        
        
        $inner = $dom->getElementById("tmp_dom_wrapper");
        $html = '';
        foreach ($inner->childNodes as $child) {
            $html .= ($dom->saveHTML($child));
        }
        return $html;
    }
    
    static function domImgUrl($baseURL, $dom) 
    {
        $url = $dom;
        if (!is_string($url)) {
            $url = $dom->getAttribute('src');
        }
         $umatch  = false;
        if(!preg_match('#/(Images|Images/Thumb/[a-z0-9]+|Images/Download)/([0-9]+)/(.*)$#', $url, $umatch))  {
            return $url;
        }
        $id = $umatch[2];
        $hash = '';
        
        if (!empty($umatch[3]) && strpos($umatch[3],'#')) {
            $hh = explode('#',$umatch[3]);
            $hash = '#'. array_pop($hh);
        }
        
        
        $img = DB_DataObject::factory('Images');
        if (!$img->get($id)) {
            return $url;
        }
        $type = explode('/', $umatch[1]);
        $thumbsize = -1;
         
        if (count($type) > 2 && $type[1] == 'Thumb') {
            $thumbsize = $type[2];
            $provider = '/Images/Thumb';
        } else {
            $provider = '/'.$umatch[1];
        }
        
        $w =  is_string($dom) ? false : $dom->getAttribute('width');
        $h =  is_string($dom) ? false : $dom->getAttribute('width');
        
        if (!is_string($dom) && (!empty($w) || !empty($h)) )
        {
            // no support for %...
            $thumbsize =
                (empty($w) ? '0' : $w * 1) .
                'x' .
                (empty($h) ? '0' : $h * 1);
             $provider = '/Images/Thumb';
            
        }
        
        if ($thumbsize !== -1) {
            // change in size..
            // need to regenerate it..
            
            $type = array('Images', 'Thumb', $thumbsize);
                
            $fc = $img->toFileConvert();
            // make sure it's available..
            $fc->convert($img->mimetype, $thumbsize);
            
            
        } else {
            $provider = $provider == 'Images/Thumb' ? 'Images' : $provider; 
        }
        
        
        // finally replace the original TAG with the new version..
        
        return $img->URL($thumbsize, $provider, $baseURL) . $hash ;
        
         
    }
    
    static function replaceDataUrl($baseURL, $img, $obj)
    {
        $d = DB_DataObject::Factory('Images');
        $d->object($obj);
        
        
        $d->createFromData($img->getAttribute('src'));
        $img->setAttribute('src', $d->URL(-1, '/Images' , $baseURL));
    }
    
    static function replaceImgUrl($html, $baseURL, $tag, $attr, $attr_name) 
    {
        
        //print_R($attr);
        // see if it's an image url..
        // Images/{ID}/fullname.xxxx
        // Images/Thumb/200/{ID}/fullname.xxxx
        // Images/Download/{ID}/fullname.xxxx
        
        $attr_url = $attr[$attr_name];
        $umatch  = false;
        if(!preg_match('#/(Images|Images/Thumb/[a-z0-9]+|Images/Download)/([0-9]+)/(.*)$#', $attr_url, $umatch))  {
            return $html;
        }
        
        $id = $umatch[2];
        $hash = '';
        if (!empty($umatch[3]) && strpos($umatch[3],'#')) {
            $hh = explode('#',$umatch[3]);
            $hash = '#'. array_pop($hh);
        }
        
        
        $img = DB_DataObject::factory('Images');
        if (!$img->get($id)) {
            return $html;
        }
        $type = explode('/', $umatch[1]);
        $thumbsize = -1;
         
        if (count($type) > 2 && $type[1] == 'Thumb') {
            $thumbsize = $type[2];
            $provider = '/Images/Thumb';
        } else {
            $provider = '/'.$umatch[1];
        }
        
        if (!empty($attr['width']) || !empty($attr['height']) )
        {
            // no support for %...
            $thumbsize =
                (empty($attr['width']) ? '0' : $attr['width'] * 1) .
                'x' .
                (empty($attr['height']) ? '0' : $attr['height'] * 1);
             $provider = '/Images/Thumb';
            
        }
        
        if ($thumbsize !== -1) {
            // change in size..
            // need to regenerate it..
            
            $type = array('Images', 'Thumb', $thumbsize);
                
            $fc = $img->toFileConvert();
            // make sure it's available..
            $fc->convert($img->mimetype, $thumbsize);
            
            
        } else {
            $provider = $provider == 'Images/Thumb' ? 'Images' : $provider; 
        }
        
        
        // finally replace the original TAG with the new version..
        
        $new_tag = str_replace(
            $attr_name. '="'. $attr_url . '"',
            $attr_name .'="'. htmlspecialchars($img->URL($thumbsize, $provider, $baseURL)) . $hash .'"',
            $tag
        );
        
        
        return str_replace($tag, $new_tag, $html);
         
    }
    
    function downloadEvent($bits)
    {
        $ev = DB_DAtaObject::Factory('events');
        if (!$ev->get($bits[1])) {
            die("could not find event id");
        }
        // technically same user only.. -- normally www-data..
        if (function_exists('posix_getpwuid')) {
            $uinfo = posix_getpwuid( posix_getuid () ); 
            $user = $uinfo['name'];
        } else {
            $user = getenv('USERNAME'); // windows.
        }
        $ff = HTML_FlexyFramework::get();
        
        $file = $ev->logDir() . date('/Y/m/d/',strtotime($ev->event_when)). $ev->id . ".json";
        
        if(!$file || !file_exists($file)){
            die("file was not saved");
        }
        
        $filesJ = json_decode(file_get_contents($file));

        foreach($filesJ->FILES as $k=>$f){
            if ($f->tmp_name != $bits[2]) {
                continue;
            }

            $src = $file = $ev->logDir() . date('/Y/m/d/', strtotime($ev->event_when)).  $f->tmp_name ;
            
            if (!$src || !file_exists($src)) {
                die("file was not saved");
            }
            header ('Content-Type: ' . $f->type);

            header("Content-Disposition: attachment; filename=\"".basename($f->name)."\";" );
            @ob_clean();
            flush();
            readfile($src);
            exit;
        }
    }
    
}

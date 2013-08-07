<?php
/**
 * Deal with image delivery and HTML replacement of image links in body text.
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
    function getAuth()
    {
        parent::getAuth(); // load company!
        //return true;
        $au = $this->getAuthUser();
        //if (!$au) {
        //    die("Access denied");
       // }
        $this->authUser = $au;
        
        return true;
    }
    var $thumb = false;
    var $as_mimetype = false;
    var $method = 'inline';
    
    function get($s) // determin what to serve!!!!
    {
        $this->as_mimetype = empty($_REQUEST['as']) ? '' : $_REQUEST['as'];
        
        $bits= explode('/', $s);
        $id = 0;
        
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
            
            $popts = PEAR::getStaticProperty('Pman','options');
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
            $file = $ff->Pman['event_log_dir']. '/'. $user. date('/Y/m/d/',strtotime($ev->event_when)). $ev->id . ".json";
            $filesJ = json_decode(file_get_contents($file));
         
            //print_r($filesJ);
         
            foreach($filesJ->FILES as $k=>$f){
                if ($f->tmp_name != $bits[2]) {
                    continue;
                }
                
                $src = $ff->Pman['event_log_dir']. '/'. $user. date('/Y/m/d/', strtotime($ev->event_when)).  $f->tmp_name ;
                if (!file_exists($src)) {
                    die("file was not saved");
                }
                header ('Content-Type: ' . $f->type);
            
                header("Content-Disposition: attachment; filename=\"".basename($f->name)."\";" );
                @ob_clean();
                flush();
                readfile($src);
                exit;
            }
            die ("unknown file?"); 
        } else {
        
            $id = empty($bits[0]) ? 0 :  $bits[0];
        }
        
        if (strpos($id,':') > 0) {  // id format  tablename:id:-imgtype
            $onbits = explode(':', $id);
            if ((count($onbits) < 2)   || empty($onbits[1]) || !is_numeric($onbits[1]) || !strlen($onbits[0])) {
                die("Bad url");
            }
            //DB_DataObject::debugLevel(1);
            $img = DB_DataObject::factory('Images');
            $img->ontable = $onbits[0];
            $img->onid = $onbits[1];
            if (empty($_REQUEST['anytype'])) {
                $img->whereAdd("mimetype like 'image/%'");
            }
            
            if (isset($onbits[2])) {
                $img->imgtype = $onbits[2];
            }
            $img->limit(1);
            if (!$img->find(true)) {
                header('Location: ' . $this->rootURL . '/Pman/templates/images/file-broken.png?reason=' .
                urlencode("no images for that item: " . htmlspecialchars($id)));
            }
            
            $id = $img->id;
            
            
        }
        $id = (int) $id;
        
        // depreciated - should use ontable:onid:type here...
        if (!empty($_REQUEST['ontable'])) {

            //DB_DataObjecT::debugLevel(1);
            $img = DB_DataObjecT::factory('Images');
            $img->setFrom($_REQUEST);
            // use imgtype now...
           // if (!empty($_REQUEST['query']['filename'])){
           //     $img->whereAdd("filename LIKE '". $img->escape($_REQUEST['query']['filename']).".%'");
           // }
            
            
            $img->limit(1);
            if (!$img->find(true)) {
                header('Location: ' . $this->rootURL . '/Pman/templates/images/file-broken.png?reason='. 
                    urlencode("No file exists"));
            } 
            $id = $img->id;
            
        }
        
        
       
        $img = DB_DataObjecT::factory('Images');
        if (!$id || !$img->get($id)) {
             
            header('Location: ' . $this->rootURL . '/Pman/templates/images/file-broken.png?reason=' .
                urlencode("image has been removed or deleted."));
        }
        $this->serve($img);
        exit;
    }
    
    
    function post()
    {
        
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
        $test_temp = '/var/lib/php5/ClippingTMPh3cFeo.svg';
        
        $xml = simplexml_load_string($_REQUEST['data']);
        
        print_r($xml->asXML());exit;
        
        $tmp = $this->tempName($src_ext);
        file_put_contents($tmp, $_REQUEST['data']);
        
        
        
        require_once 'File/Convert.php';
        $cv = new File_Convert($test_temp, $this->mimetype);
        
        $fn = $cv->convert(
                $this->as_mimetype ,
                empty($_REQUEST['width']) ? 0 : $_REQUEST['width'],
                empty($_REQUEST['height']) ? 0 : $_REQUEST['height']
        );
//        print_r($tmp);exit;
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
            //print_r($img);exit;
            header('Location: ' . $this->rootURL . '/Pman/templates/images/file-broken.png?reason=' .
                urlencode("Original file was missing : " . $img->getStoreName()));
    
        }
        
        $x = $img->toFileConvert();
        if (empty($this->as_mimetype)) {
            $this->as_mimetype  = $img->mimetype;
        }
        if (!$this->thumb) {
            $x->convert( $this->as_mimetype);
            $x->serve($this->method);
            exit;
        }
        //echo "SKALING?  $this->size";
        // acutally if we generated the image, then we do not need to validate the size..
        
        
        
        // if the mimetype is not converted..
        // then the filename should be original.{size}.jpeg
        $fn = $img->getStoreName() . '.'. $this->size . '.jpeg'; // thumbs are currenly all jpeg.!???
        
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
        
        $x->convert( $this->as_mimetype, $this->size);
        $x->serve();
        exit;
        
        
        
        
    }
    function validateSize()
    {
        
        if ($this->authUser && $this->authUser->company_id && $this->authUser->company()->comptype=='OWNER') {
            return true;
        }
        
        // DEFAULT allowed - override with $cfg['sizes'];
        
        $sizes = array(
                '100', 
                '100x100', 
                '150', 
                '150x150', 
                '200', 
                '200x0',
                '200x200',  
                '400x0',
                '300x100', // logo on login.
                '500'
            );
        
        // this should be configurable...
        $ff = HTML_FlexyFramework::get();
        $cfg = isset($ff->Pman_Images) ? $ff->Pman_Images :
                (isset($ff->Pman_Core_Images) ? $ff->Pman_Core_Images : array());
        
        
        
        if (!empty($cfg['sizes'])) {
            $sizes = array_merge($sizes , $cfg['sizes']);
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
    
    
    static function replaceImageURLS($html)
    {
        
        $ff = HTML_FlexyFramework::get();
        if (!isset($ff->Pman_Images['public_baseURL'])) {
            return $html;
        }
        //var_dump($ff->Pman_Images['public_baseURL']);
        $baseURL = $ff->Pman_Images['public_baseURL'];
        
        preg_match_all('/<img\s+[^>]+>/i',$html, $result); 
        //print_r($result);
        $matches = array_unique($result[0]);
        foreach($matches as $img) {
            $imatch = array();
            preg_match_all('/(width|height|src)="([^"]*)"/i',$img, $imatch);
            // build a keymap
            $attr =  array();
            
            foreach($imatch[1] as $i=>$key) {
                $attr[$key] = $imatch[2][$i];
            }
            if (!isset($attr['src']) || 0 !== strpos($attr['src'], $baseURL)) {
                continue;
            }
            $html = self::replaceImgUrl($html, $baseURL, $img, $attr,  'src' );
        }
        
        $result = array();
        preg_match_all('/<a\s+[^>]+>/i',$html, $result); 

        $matches = array_unique($result[0]);
        foreach($matches as $img) {
            $imatch = array();
            preg_match_all('/(href)="([^"]*)"/i',$img, $imatch);
            // build a keymap
            $attr =  array();
            
            foreach($imatch[1] as $i=>$key) {
                $attr[$key] = $imatch[2][$i];
            }
            if (!isset($attr['href']) || 0 !== strpos($attr['href'], $baseURL)) { 
                continue;
            }
            $html = self::replaceImgUrl($html, $baseURL, $img, $attr, 'href' );
        }
        
        return $html;
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
            $attr_name .'="'. htmlspecialchars($img->URL($thumbsize, $provider, $baseURL)) . '"',
            $tag
        );
        
        
        return str_replace($tag, $new_tag, $html);
         
    }
    
}

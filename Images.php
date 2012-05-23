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
 *      as (serve as a type) = eg. mimetype.
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
 
    function serve($img)
    {
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
        $this->validateSize();
        
        $x->convert( $this->as_mimetype, $this->size);
        $x->serve();
        exit;
        
        
        
        
    }
    function validateSize()
    {
        
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
    
    
    
    function replaceImg($html)
    {
        preg_match_all('/<img[^>]+>/i',$html, $result); 

        $matches = array_unique($result[0]);
        foreach($matches as $img) {
            $imatch = array();
            preg_match_all('/(width|height|src)="([^"]*)"/i',$img, $imatch);
            // build a keymap
            $attr =  array();
            
            foreach($imatch[1] as $i=>$key) {
                $attr[$key] = $imatch[2][$i];
            }
            print_R($attr);
            // see if it's an image url..
            // Images/{ID}/fullname.xxxx
            // Images/Thumb/200/{ID}/fullname.xxxx
            // Images/Download/{ID}/fullname.xxxx
            $umatch  = false;
            if(!preg_match('#/(Images|Images/Thumb/[a-z0-9]+|Images/Download)/([0-9]+)/(.*)$#', $attr['src'], $umatch))  {
                continue;
            }
            $id = $umatch[2];
            $imgObj = DB_DataObject::factory('Images');
            if (!$imgObj->get($id)) {
                continue;
            }
            $type = explode('/', $umatch[1]);
            $thumbsize = false;
            $new_thumbsize = false;
            
            if (count($type) > 2 && $type[1] == 'Thumb') {
                $thumbsize = $type[2];
            }
            
            if (!empty($attr['width']) || !empty($attr['height']) )
            {
                // no support for %...
                $new_thumbsize =
                    (empty($attr['width']) ? '0' : $attr['width'] * 1) .
                    'x' .
                    (empty($attr['height']) ? '0' : $attr['height'] * 1);
                
                
            }
            if ($new_thumbsize != $thumbsize) {
                // change in size..
                // need to regenerate it..
                if (!$new_thumbsize) {
                    $type = array('Image');
                } else {
                    $type = array('Image', 'Thumb', $new_thumbsize);
                    
                    $fc = $img->toFileConvert();
                    $fc = $img->convert($img->mimetype, $new_thumbsize);
                    
                    
                }
                
                
            }
            
            
            
            // make an image url..
            print_R($umatch);  
        }
        return $html;
        
    }
    
}

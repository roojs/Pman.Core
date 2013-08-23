<?php
/**
 * Table Definition for Images
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_Images extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'Images';                          // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $filename;                        // string(255)  not_null
    public $ontable;                         // string(32)  not_null multiple_key
    public $onid;                            // int(11)  not_null
    public $mimetype;                        // string(64)  not_null
    public $width;                           // int(11)  not_null
    public $height;                          // int(11)  not_null
    public $filesize;                        // int(11)  not_null
    public $displayorder;                    // int(11)  not_null
    public $language;                        // string(6)  not_null
    public $parent_image_id;                 // int(11)  not_null
    public $created;                         // datetime(19)  not_null binary
    public $imgtype;                         // string(32)  not_null
    public $linkurl;                         // string(254)  not_null
    public $descript;                        // blob(65535)  not_null blob
    public $title;                           // string(128)  not_null
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function checkPerm($perm, $au)
    {
        // default permissons are to
        // allow create / edit / if the user has
        
        if (!$au) {
            
          
            
            return false;
        }
        
        $o = $this->object();
        //print_r($o);
        if (method_exists($o, 'hasPerm')) {
            // edit permissions on related object needed...
            return $o->hasPerm( $perm == 'S' ? 'S' : 'E' , $au);
            
        }
        
        return true; //// ??? not really that safe...
        
    }
    
    function beforeInsert($q, $roo) 
    {
        if (isset($q['_remote_upload'])) {
            require_once 'System.php';
            
            $tmpdir  = System::mktemp("-d remote_upload");
            
            $path = $tmpdir . '/' . basename($q['_remote_upload']);
            
            if(!file_exists($path)){
               file_put_contents($path, file_get_contents($q['_remote_upload'])); 
            }
            
            $imageInfo = getimagesize($path);
            
            require_once 'File/MimeType.php';
            $y = new File_MimeType();
            $ext = $y->toExt(trim((string) $imageInfo['mime'] ));
            
            if (!preg_match("/\." . $ext."$/", $path, $matches)) {
                rename($path,$path.".".$ext);
                $path.= ".".$ext;
            }
            
            if (!$this->createFrom($path)) {
                $roo->jerr("erro making image" . $q['_remote_upload']);
            }
            
            $roo->addEvent("ADD", $this, $this->toEventString());
        
            $r = DB_DataObject::factory($this->tableName());
            $r->id = $this->id;
            $roo->loadMap($r);
            $r->limit(1);
            $r->find(true);
            $roo->jok($r->URL(-1,'/Images') . '#attachment-'.  $r->id);
        }
        
    }
    
    
    /**
     * create an email from file.
     * these must have been set first.
     * ontable / onid.
     * 
     */
    function createFrom($file, $filename=false)
    {
        // copy the file into the storage area..
        if (!file_exists($file) || !filesize($file)) {
            return false;
        }
        
        $filename = empty($filename) ? $file : $filename;
        
        if (empty($this->mimetype)) {
            require_once 'File/MimeType.php';
            $y = new File_MimeType();
            $this->mimetype = $y->fromFilename($filename);
        }
        
        $this->mimetype= strtolower($this->mimetype);
        
        if (array_shift(explode('/', $this->mimetype)) == 'image') { 
        
            $imgs = @getimagesize($file);
            
            if (empty($imgs) || empty($imgs[0]) || empty($imgs[1])) {
                // it's a file!!!!
            } else {
                list($this->width , $this->height)  = $imgs;
            }
        }
        
        $this->filesize = filesize($file);
        $this->created = date('Y-m-d H:i:s');
         
        
        if (empty($this->filename)) {
            $this->filename = basename($filename);
        }
        
        //DB_DataObject::debugLevel(1);
        if (!$this->id) {
            $this->insert();
        } else {
            $this->update();
        }
        
        
        
        $f = $this->getStoreName();
        $dest = dirname($f);
        if (!file_exists($dest)) {
            // currently this is 0775 due to problems using shared hosing (FTP)
            // it makes all the files unaccessable..
            // you can normally solve this by giving the storedirectory better perms
            // if needed on a dedicated server..
            $oldumask = umask(0);
            mkdir($dest, 0775, true);
            umask($oldumask);  
        }
        
        copy($file,$f);
        
        // fill in details..
        
        /* thumbnails */
        
     
       // $this->createThumbnail(0,50);
        return true;
        
    }

    /**
     * Calculate target file name
     *
     * @return - target file name
     */
    function getStoreName() 
    {
        $opts = HTML_FlexyFramework::get()->Pman;
        $fn = preg_replace('/[^a-z0-9\.]+/i', '_', $this->filename);
        return implode( '/', array(
            $opts['storedir'], '_images_', date('Y/m', strtotime($this->created)), $this->id . '-'. $fn
        ));
          
    }
     
    /**
     * deletes all the image instances of it...
     * 
     * 
     */
    function beforeDelete()
    {
        $fn = $this->getStoreName();
        if (file_exists($fn)) {
            unlink($fn);
        }
        // delete thumbs..
        $b = basename($fn);
        $d = dirname($fn);
        if (file_exists($d)) {
                
            $dh = opendir($d);
            while (false !== ($fn = readdir($dh))) {
                if (substr($fn, 0, strlen($b)) == $b) {
                    unlink($d. '/'. $fn);
                }
            }
        }
        
    }
    /**
     * check mimetype against type
     * - eg. img.is(#image#)
     *
     */
    function is($type)
    {
        if (empty($this->mimetype)) {
            return false;
        }
        return 0 === strcasecmp($type, array_shift(explode('/',$this->mimetype)));
    }
  
    /**
     * onUpload (singlely attached image to a table)
     */
    
    function onUploadWithTbl($tbl,  $fld)
    {
        if ( $tbl->__table == 'Images') {
            return; // not upload to self...
        }
        if (empty($_FILES['imageUpload']['tmp_name']) || 
            empty($_FILES['imageUpload']['name']) || 
            empty($_FILES['imageUpload']['type'])
        ) {
            return false;
        }
        if ($tbl->$fld) {
            $image = DB_DataObject::factory('Images');
            $image->get($tbl->$fld);
            $image->beforeDelete();
            $image->delete();
        }
        
        $image = DB_DataObject::factory('Images');
        $image->onid = $tbl->id;
        $image->ontable = $tbl->__table;
        $image->filename = $_FILES['imageUpload']['name']; 
        $image->mimetype = $_FILES['imageUpload']['type'];
       
        if (!$image->createFrom($_FILES['imageUpload']['tmp_name'])) {
            return false;
        }
        $old = clone($tbl);
        $tbl->$fld = $image->id;
        $tbl->update($old);
         
    }
    
    // direct via roo...
    /// ctrl not used??
    function onUpload($roo)
    {
        
        if (empty($_FILES['imageUpload']['tmp_name']) || 
            empty($_FILES['imageUpload']['name']) || 
            empty($_FILES['imageUpload']['type'])
        ) {
            $this->err = "Missing file details";
            return false;
        }
        
        if ($this->id) {
            $this->beforeDelete();
        }
        if ( empty($this->ontable)) {
            $this->err = "Missing  ontable";
            return false;
        }
        
        if (!empty($this->imgtype) && $this->imgtype[0] == '-' && !empty($this->onid)) {
            // then its an upload 
            $img  = DB_DataObject::factory('Images');
            $img->onid = $this->onid;
            $img->ontable = $this->ontable;
            $img->imgtype = $this->imgtype;
            
            $img->find();
            while ($img->fetch()) {
                $img->beforeDelete();
                $img->delete();
            }
            
        }
        
        
        
        require_once 'File/MimeType.php';
        $y = new File_MimeType();
        $this->mimetype = $_FILES['imageUpload']['type'];
        if (in_array($this->mimetype, array(
                        'text/application',
                        'application/octet-stream',
                        'image/x-png',  // WTF does this?
                        'image/pjpeg',  // WTF does this?
                        'application/x-apple-msg-attachment', /// apple doing it's magic...
                        'application/vnd.ms-excel',   /// sometimes windows reports csv as excel???
                        'application/csv-tab-delimited-table', // windows again!!?
                ))) { // weird tyeps..
            $inf = pathinfo($_FILES['imageUpload']['name']);
            $this->mimetype  = $y->fromExt($inf['extension']);
        }
        
        
        $ext = $y->toExt(trim((string) $this->mimetype ));
        
        $this->filename = empty($this->filename) ? 
            $_FILES['imageUpload']['name'] : ($this->filename .'.'. $ext); 
        
        
        
        if (!$this->createFrom($_FILES['imageUpload']['tmp_name'])) {
            return false;
        }
        return true;
         
    }
     
    
    
    /**
     * return a list of images for an object, optionally with a mime regex.
     * eg. '%/pdf' or 'image/%'
     *
     * usage:
     *
     * $i = DB_DataObject::factory('Images');
     * $i->imgtype = 'LOGO';
     * $ar = $i->gather($somedataobject, 'image/%');
     * 
     * @param {DB_DataObject} dataobject  = the object to gather data on.
     * @param {String} mimelike  LIKE query to use for search
     
     */
    function gather($obj, $mime_like='', $opts=array())
    {
        //DB_DataObject::debugLevel(1);
        if (empty($obj->id)) {
            return array();
        }
        
        $c = clone($this);
        $c->ontable = $obj->tableName();
        $c->onid = $obj->id;
        $c->autoJoin();
        if (!empty($mime_like)) {
            $c->whereAdd("Images.mimetype LIKE '". $c->escape($mime_like) ."'");
        }

        return $c->fetchAll();
    }
     
    
    /**
    * set or get the dataobject this image is associated with
    * @param DB_DataObject $obj An object to associate this image with
    *        (does not store it - you need to call update() to do that)
    * @return DB_DataObject the dataobject this image is attached to.
    */
    function object($obj=false)
    {
        if ($obj === false) {
            if (empty($this->ontable) || empty($this->onid)) {
                return false;
            }
            $ret = DB_DataObject::factory($this->ontable);
            $ret->get($this->onid);
            return $ret;
        }
        
        
        $this->ontable = $obj->tableName();
        $this->onid = $obj->id; /// assumes our nice standard of using ids..
        return $obj;
    }
    
     
    function toRooArray($req = array()) {
      //  echo '<PRE>';print_r($req);exit;
        $ret= $this->toArray();
      
        static $ff = false;
        if (!$ff) {
            $ff = HTML_FlexyFramework::get();
        }
        
        $ret['public_baseURL'] = isset($ff->Pman_Images['public_baseURL']) ?
                    $ff->Pman_Images['public_baseURL'] : $ff->baseURL;
        
        if (!empty($req['query']['imagesize'])) {
             $baseURL = isset($req['query']['imageBaseURL']) ? $req['query']['imageBaseURL'] : false;
            
            $ret['url'] = $this->URL(-1, '/Images/Download',$baseURL);
            
            $ret['url_view'] = $this->URL(-1, '/Images',$baseURL);    
            
            if (!empty($req['query']['imagesize'])) {
                $ret['url_thumb'] = $this->URL($req['query']['imagesize'], '/Images/Thumb',$baseURL);
            }
        }
        
         
         
        return $ret;
    }
    
    /**
     * URL - create  a url for the image.
     * size - use -1 to show full size.
     * provier = baseURL + /Images/Thumb ... use '/Images/' for full
     * 
     * 
     */
    function URL($size , $provider = '/Images/Thumb', $baseURL=false)
    {
        if (!$this->id) {
            return 'about:blank';
            
        }

        $ff = HTML_FlexyFramework::get();
        $baseURL = $baseURL ? $baseURL : $ff->baseURL ;
        if (preg_match('#^http[s]*://#', $provider)) {
            $baseURL = '';
        }
       
        if ($size < 0) {
            $provider = preg_replace('#/Thumb$#', '', $provider);
            
            return $baseURL . $provider . "/{$this->id}/{$this->filename}";
        }
        //-- max?
        //$size = max(100, (int) $size);
        //$size = min(1024, (int) $size);
        // the size should 200x150 to convert
        $sizear = preg_split('/(x|c)/', $size);
        if(empty($sizear[1])){
            $sizear[1] = 0;
        }
        $size = implode(strpos($size,'c') > -1 ? 'c' : 'x', $sizear);
//        print_r($size);
        $fc = $this->toFileConvert();
//        print_r($size);
//        exit;
        $fc->convert($this->mimetype, $size);
        
        
        return $baseURL . $provider . "/$size/{$this->id}/{$this->filename}";
    }
    /**
     * size could be 123x345
     * 
     * 
     */
    function toHTML($size, $provider = '/Images/Thumb') 
    {
        
        
        
        $sz = explode('x', $size);
        $sx = $sz[0];
        //var_dump($sz);
        if (!$this->id || empty($this->width)) {
            $this->height = $sx;
            $this->width = empty($sz[1]) ? $sx : $sz[1];
            $sy = $this->width ;
        }
        if (empty($sz[1])) {
            $ratio =  empty($this->width) ? 1 : $this->height/ ($this->width *1.0);
            $sy = $ratio * $sx;
        } else {
            $sy = $sz[1];
        }
        // create it?
         
        return '<img src="' . $this->URL($size, $provider) . '" width="'. $sx . '" height="'. $sy . '">';
        
        
    }
     
    /**
     * to Fileconvert object..
     *
     *
     *
     */
    function toFileConvert()
    {
        require_once 'File/Convert.php';
        $fc = new File_Convert($this->getStoreName(), $this->mimetype);
        return $fc;
        
    }
    
    function fileExt()
    {
        require_once 'File/MimeType.php';
        
        $y = new File_MimeType();
        return  $y->toExt($this->mimetype);
        
        
    }
    
    /**
     *
     *
     *
     */
    
    
    function setFromRoo($ar, $roo)
    {
        // not sure why we do this.. 
        
        
        
        
        
        // if imgtype starts with '-' ? then we set the 'old' (probably to delete later)
        if (!empty($ar['imgtype']) && !empty($ar['ontable']) && !empty($ar['onid']) && ($ar['imgtype'][0] == '-')) {
            $this->setFrom($ar);
            $this->limit(1);
            if ($this->find(true)) {
                $roo->old = clone($this);
            }
        }   
            
        
        if (!empty($ar['_copy_from'])) {
            
            if (!$this->checkPerm( 'A' , $roo->authUser))  {
                $roo->jerr("IMAGE UPLOAD PERMISSION DENIED");
            }
            
            $copy = DB_DataObject::factory('Images');
            $copy->get($ar['_copy_from']);
            $this->setFrom($copy->toArray());
            $this->setFrom($ar);
            $this->createFrom($copy->getStoreName());
            
            $roo->addEvent("ADD", $this, $this->toEventString());
            
            $r = DB_DataObject::factory($this->tableName());
            $r->id = $this->id;
            $roo->loadMap($r);
            $r->limit(1);
            $r->find(true);
            $roo->jok($r->toArray());
            
            
        }
        
         
        
        // FIXME - we should be checking perms here...
       
        // this should be doign update
        $this->setFrom($ar);
         
        if (!$this->checkPerm($this->id ? 'A' : 'E', $roo->authUser))  {
            $roo->jerr("IMAGE UPLOAD PERMISSION DENIED");
        }
        
        
        
        if (!isset($_FILES['imageUpload'])) {
            return; // standard update...
        }
        
        if ( !$this->onUpload($this)) {
            $roo->jerr("File upload failed : ". $this->err);
        }
        
        $roo->addEvent("ADD", $this, $this->toEventString());
        
        $r = DB_DataObject::factory($this->tableName());
        $r->id = $this->id;
        $roo->loadMap($r);
        $r->limit(1);
        $r->find(true);
        $roo->jok($r->toArray());
         
    }
    
    function toEventString()
    {
        
        //$p = DB_DataObject::factory($this->ontable);
        //if (!is_$p) {
        //    return "ERROR unknown table? {$this->ontable}";
       // }
        //$p->get($p->onid);
        
        return $this->filename .' - on ' . $this->ontable . ':' . $this->onid;
        //$p->toEventString();
    }
 }

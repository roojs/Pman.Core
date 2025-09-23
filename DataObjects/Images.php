<?php
/**
 * Table Definition for Images
 */
class_exists('DB_DataObject') ? '' : require_once 'DB/DataObject.php';

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
    function applyFilters($q, $au, $roo)
    {
        $tn = $this->tableName();
        
        if(!empty($q['search']['filename'])){
            $this->whereAdd("
                $tn.filename LIKE '%{$this->escape($q['search']['filename'])}%' 
                OR 
                $tn.title LIKE '%{$this->escape($q['search']['filename'])}%'
            ");
        }

        if(!empty($q['_to_base64']) && !empty($q['image_id'])) {
            $i = DB_DataObject::factory($tn);
            $i->get($q['image_id']);
            $roo->jok($i->toBase64());
        }

        if(!empty($q['query']['min_width'])) {
            $this->whereAdd("{$tn}.width >= " . intval($q['query']['min_width']));
        }

        if(!empty($q['query']['min_height'])) {
            $this->whereAdd("{$tn}.height >= " . intval($q['query']['min_height']));
        }
    }
    
    function checkPerm($lvl, $au)
    {
        // default permissons are to
        // allow create / edit / if the user has
        
        if (!$au) {
            return false;
        }
        
        $o = $this->object();
        //print_r($o);
        if ($o &&  method_exists($o, 'checkPerm')) {
            // edit permissions on related object needed...
            return $o->checkPerm( $lvl == 'S' ? 'S' : 'E' , $au);
            
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
            
            if(!empty($q['_return_after_create'])){
                return;
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
            $this->err = "File $file did not exist or is 0 size";
            return false;
        }
        
        $filename = empty($filename) ? $file : $filename;
        
        if (empty($this->mimetype)) {
            require_once 'File/MimeType.php';
            $y = new File_MimeType();
            $this->mimetype = $y->fromFilename($filename);
        }
        
        $this->mimetype = strtolower($this->mimetype);
        
        $mta = explode('/', $this->mimetype);
        if (array_shift($mta) == 'image') { 
        
            $imgs = @getimagesize($file);
            
            if (empty($imgs) || empty($imgs[0]) || empty($imgs[1])) {
                // it's a file!!!!
                if($this->mimetype == 'image/svg+xml') {
                    $xml = simplexml_load_file($file);
                    $attr = $xml->attributes();
                    $this->width = $attr->width; 
                    $this->height = $attr->height;
                }
            } else {
                list($this->width , $this->height)  = $imgs;
            }
        }
        
        if($this->mimetype == 'application/pdf'){
            $this->no_of_pages = $this->getPdfPages($file);
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
        return self::staticGetStoreName($this);
          
    }

    static function staticGetStoreName($o)
    {
        $opts = HTML_FlexyFramework::get()->Pman;
        $fn = preg_replace('/[^a-z0-9\.]+/i', '_', $o->filename);
        return implode( '/', array(
            $opts['storedir'], '_images_', date('Y/m', strtotime($o->created)), $o->id . '-'. $fn
        ));
    }
    
    /**
     * does the files exist?
     */
    function exists()
    {
        return self::staticExists($this);
    }

    static function staticExists($o)
    {
        clearstatcache();
        $ret =  file_exists(self::staticGetStoreName($o));
        if (!$ret) {
            return self::staticCanFix($o);
        }
        return $ret;
    }

    /**
     * the getStorename code got changed, and some old files may not end up with the correct name anymore.
     * this tries to fix it.
     *
     */
    function canFix() 
    {
        return self::staticCanFix($this);
    }

    static function staticCanFix($o)
    {
        // look for the image in the folder, with matching id.
        // this is problematic..
        $fn = self::staticGetStoreName($o);
        clearstatcache();
        if (file_exists($fn)) {
            
            if (file_exists($fn . '-really-missing')) {
                unlink($fn . '-really-missing');
                return false;
            }
            
            return true;
        }
        
        if (file_exists($fn . '-really-missing')) {
            return false;
        }
        if (!file_exists(dirname($fn))) {
            return false;
        }
        foreach( scandir(dirname($fn)) as $n) {
            if (empty($n) || $n[0] == '.') {
                continue;
            }
            $bits = explode('-', $n);
            if ($bits[0] != $o->id) {
                continue;
            }
            if (preg_match('/\.[0-9]+x[0-9]]+\.jpeg$/', $n)) {
                continue;
            }
            copy(dirname($fn). '/'.  $n, $fn);
            clearstatcache();
            return true;
        }
        // fixme - flag it as bad
        touch($fn . '-really-missing');
    }
    
    
    /**
     * deletes all the image instances of it...
     * 
     * 
     */
    function beforeDelete($dependants_array, $roo)
    {
        
        if (!empty($dependants_array)) {
            return;
        }
        
        $opts = HTML_FlexyFramework::get()->Pman;
        $deldir = $opts['storedir']. '/_deleted_images_';
        clearstatcache();
        if (!file_exists( $deldir )) {
            @mkdir($deldir, 0755); // not sure why we are erroring here.. after checking - maybe permissions?
        }
            
        $fn = $this->getStoreName();
        $b = basename($fn);
        clearstatcache();
        if (file_exists($fn)) {
            
            if (file_exists($deldir . '/'. $b)) {
                unlink($fn);
            } else {
                rename($fn, $deldir .'/'. $b);
            }
            
            
        }
        // delete thumbs..
        
        $d = dirname($fn);
        if (file_exists($d)) {
                
            $dh = opendir($d);
            while (false !== ($fn = readdir($dh))) {
                if (substr($fn, 0, strlen($b)) == $b) {
                    clearstatcache();
                    if (file_exists($deldir . '/'. $fn)) {
                        unlink($d. '/'. $fn);
                        continue;
                    }
                    if (file_exists($d. '/'. $fn)) {
                        rename($d. '/'. $fn, $deldir .'/'. $fn);
                    }
                    
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
    
    function onUploadWithTbl($tbl,  $fld, $opts = array())
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
        if ($fld != false && $tbl->$fld) {
            HTML_FlexyFramework::get()->page->jerr("updating images is disabled");
            exit;
            $image = DB_DataObject::factory('Images');
            $image->get($tbl->$fld);
            $image->beforeDelete();
            $image->delete();
        }
        
        $image = DB_DataObject::factory('Images');
        $image->onid = $tbl->id;
        $image->ontable = $tbl->__table;
        
        $image->filename = $_FILES['imageUpload']['name'];
        $image->setFrom($opts);

        if  (strlen( $_FILES['imageUpload']['name']) > 128) {
            // although we allow 254 chars.. try and keep it down. - as it seems to cause other problems
             $image->filename = str_replace('.','', substr($_FILES['imageUpload']['name'], 0, 128)) . "." . $ext;
        }
        
        $image->mimetype = $_FILES['imageUpload']['type'];
       
        if (!$image->createFrom($_FILES['imageUpload']['tmp_name'])) {
            return false;
        }
        $old = clone($tbl);
        if ($fld != false) {
            $tbl->$fld = $image->id;
        }
        $tbl->update($old);
         
    }
    
    // direct via roo...
    /// ctrl not used??
    function onUpload($roo, $table = false, $file = false)
    {
        
        if ($table !== false) {
            $this->ontable = $table->tableName();
            $this->onid = $table->pid();
        }
        
        if ($file === false) {
            $file = isset($_FILES['imageUpload']) ? $_FILES['imageUpload'] : array();
        }
        
        //print_r($_FILES); echo $_FILES['imageUpload']['type'];exit;
        if (empty($file['tmp_name']) || 
            empty($file['name']) || 
            empty($file['type'])
        ) {
            
            $emap = array( 
                0=>"There is no error, the file uploaded with success", 
                1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini", 
                2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form" ,
                3=>"The uploaded file was only partially uploaded",
                4=>"No file was uploaded",
                6=>"Missing a temporary folder" 
            ); 
            $estr = (empty($file['error']) ? '?': $emap[$file['error']]);
            $this->err = "Missing file details : Error=". $estr;
            return false;
        }
        
        if ($this->id) {
            HTML_FlexyFramework::get()->page->jerr("updating images is disabled");
            exit;
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
                HTML_FlexyFramework::get()->page->jerr("updating images is disabled");
                exit;
                $img->beforeDelete();
                $img->delete();
            }
            
        }
        
        
        
        require_once 'File/MimeType.php';
        $y = new File_MimeType();
        $this->mimetype = $file['type'];
        if (in_array($this->mimetype, array(
                        'text/application',
                        'application/octet-stream',
                        'image/x-png',  // WTF does this?
                        'image/pjpeg',  // WTF does this?
                        'application/x-apple-msg-attachment', /// apple doing it's magic...
                        'application/vnd.ms-excel',   /// sometimes windows reports csv as excel???
                        'application/csv-tab-delimited-table', // windows again!!?
                ))) { // weird tyeps..
            $inf = pathinfo($file['name']);
            $this->mimetype  = $y->fromExt($inf['extension']);
        }
        
        
        $ext = $y->toExt(trim((string) $this->mimetype ));
        
        if (!empty( $file['name']) && strlen( $file['name']) > 128) {
            // although we allow 254 chars.. try and keep it down. - as it seems to cause other problems
            $file['name'] = str_replace('.','', substr($file['name'], 0, 128)) . "." . $ext;
            
        }
        
        $this->filename = empty($this->filename) ? 
            $file['name'] : ($this->filename .'.'. $ext); 
        
        
        
        if (!$this->createFrom($file['tmp_name'])) {
            $this->err  =  isset($this->err)  ?  $this->err  : "createFrom Image failed";
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
        $c->whereAddIn($this->tableName() . '.ontable', array( $obj->tableName(), $obj->__table) , 'string');
        $c->onid = $obj->id;
        $c->autoJoin();
        if (!empty($mime_like)) {
            $c->whereAdd("Images.mimetype LIKE '". $c->escape($mime_like) ."'");
        }
        $c->orderBy('created ASC');

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
    
     
    function toRooArray($req)
    {
        
        $ret= $this->toArray();
      
         
        $ff = HTML_FlexyFramework::get();
        
        
        $ret['public_baseURL'] = isset($ff->Pman_Images['public_baseURL']) ?
                    $ff->Pman_Images['public_baseURL'] : $ff->baseURL;
        
        if (!empty($req['query']['imagesize'])) {
            // query/imageBaseURL ... depricated...? -- set it in config?
            
            $baseURL = isset($req['query']['imageBaseURL']) ? $req['query']['imageBaseURL'] : $ret['public_baseURL'];
            
            $ret['url'] = $this->URL(-1, '/Images/Download',$baseURL);
            
            $ret['url_view'] = $this->URL(-1, '/Images',$baseURL);    
            
            if (!empty($req['query']['imagesize'])) {
                $ret['url_thumb'] = $this->URL($req['query']['imagesize'], '/Images/Thumb',$baseURL);
            }
            
            
        }
        $ret['shorten_name']   = $this->shorten_name();
        
        return $ret;
    }
    
    /**
     * URL - create  a url for the image.
     * size - use -1 to show full size.
     * provier = baseURL + /Images/Thumb ... use '/Images/' for full
     * 
     * 
     */
    function URL($size , $provider = '/Images/Thumb', $baseURL=false, $to_type=false)
    {
        if (!$this->id) {
            return 'about:blank';
        }
        if (!$this->exists()) {
            return 'about:missing';
        }
        
        $shorten_name = $this->shorten_name();
        
        $ff = HTML_FlexyFramework::get();
        $baseURL = $baseURL ? $baseURL : $ff->baseURL ;
        if (preg_match('#^http[s]*://#', $provider)) {
            $baseURL = '';
        }
       
        if ($size < 0 || $this->mimetype == 'image/svg+xml') {
            $provider = preg_replace('#/Thumb$#', '', $provider);
            
            return $baseURL . $provider . "/{$this->id}/{$shorten_name}"; // -- this breaks the rss feed #image-{$this->id}";
        }
        //-- max?
        //$size = max(100, (int) $size);
        //$size = min(1024, (int) $size);
        // the size should 200x150 to convert
        $sizear = preg_split('/(x|c)/', $size);
        if(!isset($sizear[1])){
            $sizear[1] =   0; // 0x with '0' is a box? why
        }
        
        $size = implode(strpos($size,'c') > -1 ? 'c' : 'x', $sizear);
//        print_r($size);
        $fc = $this->toFileConvert();
//        print_r($size);
//        exit;
        $mt = $to_type === false ? $this->mimetype : $to_type;
        if (!preg_match('#^image/#i',$mt)) {
            $mt = 'image/jpeg';
        }
        
        $cn = $fc->convert($mt, $size);
        $shorten_name = $this->shorten_name(basename($cn));
        
        return $baseURL . $provider . "/$size/{$this->id}/{$shorten_name}"; // -- this breaks the rss feed #image-{$this->id}";
    }
    /**
     *
     * tries to get an image from then URL - not always has based... - also from the normal url
     */
    function getFromHashURL($url)
    {
        $id = false;
        if (preg_match('/#image-([0-9]+)$/', $url, $matches)) {
            $id = $matches[1];
        } else if (preg_match('#Images/Thumb/[^/]+/([0-9]+)/#', $url, $matches)) {
            $id = $matches[1];
        } else if (preg_match('#Images/([0-9]+)/#', $url, $matches)) {
            $id = $matches[1];
        } else if (preg_match('#images[^/]+/([0-9]+)/#i', $url, $matches)) {
            // supports images.xxxxx.com/{number}/name...
            $id = $matches[1];
        } else if (preg_match('#Thumb/[^/]+/([0-9]+)/#', $url, $matches)) {
            $id = $matches[1];
        }
        if ($id === false ||  $id < 1) {
            return false;
        }
        
        $img = DB_DAtaObject::Factory('images');
        if ($img->get($id)) {
            return $img;
        }
        return false;
    }
    
    
    function shorten_name($fn = false)
    {
        if(empty($this->filename)) {
            return;
        }
        $fn = $fn === false ? $this->filename : $fn;
        
        $filename = explode('.', $fn);
        $ext = array_pop($filename);
        $name = preg_replace("/[^A-Z0-9.]+/i", '-', implode('-', $filename)) ;
        
        if(strlen($name) > 32) {
            $name = substr($name, 0, 32);
        }
        
        $shorten_name = "{$name}.{$ext}";
        
        return $shorten_name;
    }
    /**
     * size could be 123x345
     * 
     * 
     */
    function toHTML($size, $provider = '/Images/Thumb', $extra = '') 
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
            $sy = intval($ratio * $sx);
        } else {
            $sy = $sz[1];
        }
        // create it?
       
        if (strlen($this->title)) {
            $extra = ' title="'. htmlspecialchars($this->title) . '"';
        }
        
        return '<img src="' . $this->URL($size, $provider) . '"' .
                $extra .
                ' width="'. $sx . '"' .
                ' height="'. $sy . '">';
        
        
    }
    
    /**
     * 
     * #2142 [new] CMS - image link urls
     * 
     * 
     * 
     */
    function toLinkHTML($size, $provider = '/Images/Thumb')
    {
        if(empty($this->linkurl)){
            return $this->toHTML($size, $provider = '/Images/Thumb');
        }
        
        return '<a href="'.$this->linkurl.'" target="_blank">'.$this->toHTML($size, $provider = '/Images/Thumb').'</a>';
        
    }
    
    
    /**
     * to Fileconvert object..
     *
     *
     *
     */
    function toFileConvert()
    {
        $fn = $this->getStoreName();
        
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
            $roo->jok($r->toRooArray($ar));
            
            
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
        
        
//        print_r(!$this->onUpload($this));
        
        if ( !$this->onUpload($this)) { 
            $roo->jnotice("BADUPLOAD", "File upload failed : error = ". (!empty($this->err) ? $this->err : ''));
        }
        
        $this->addEvent($ar, $roo);
        
        $r = DB_DataObject::factory($this->tableName());
        $r->id = $this->id;
        $roo->loadMap($r);
        $r->limit(1);
        $r->find(true);
        $roo->jok($r->toRooArray($ar));
         
    }
    
    function addEvent($ar, $roo)
    {
        $roo->addEvent("ADD", $this, $this->toEventString());
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
    
    function onUploadFromData($data, $roo)
    {
        if (empty($data)) {
            $this->err = "Missing file details";
            return false;
        }
        
        if ($this->id) {
            HTML_FlexyFramework::get()->page->jerr("updating images is disabled");
            exit;
            $this->beforeDelete();
        }
        
        if (empty($this->ontable)) {
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
                HTML_FlexyFramework::get()->page->jerr("updating images is disabled");
                exit;
                $img->beforeDelete();
                $img->delete();
            }
            
        }
        
        require_once 'File/MimeType.php';
        $y = new File_MimeType();
        
        if (in_array($this->mimetype, array(
                        'text/application',
                        'application/octet-stream',
                        'image/x-png',  // WTF does this?
                        'image/pjpeg',  // WTF does this?
                        'application/x-apple-msg-attachment', /// apple doing it's magic...
                        'application/vnd.ms-excel',   /// sometimes windows reports csv as excel???
                        'application/csv-tab-delimited-table', // windows again!!?
                ))) { // weird tyeps..
            $inf = pathinfo($this->filename);
            $this->mimetype  = $y->fromExt($inf['extension']);
        }
        
        $ext = $y->toExt(trim((string) $this->mimetype ));
        
        $explode_filename = explode('.', $this->filename);
        if(array_pop($explode_filename) != $ext){
            $this->filename = $this->filename .'.'. $ext; 
        }
        
        if (!$this->createFromData($data)) {
            return false;
        }
        
        return true;
         
    }
    
    function createFromData($data)
    {   
        
        if (0 === strpos($data, "data:")) {
            // data:image/png;base64, 
            $data = substr($data,5);
            $bits = explode(";", $data);
            $this->mimetype = $bits[0];
        }
        static $imgid = 1;
        if (empty($this->filename)) {
            require_once 'File/MimeType.php';
            $y = new File_MimeType();
            $this->filename = 'image-'.$imgid++.'.'.$y->toExt($this->mimetype);
        }
        
        
        $this->mimetype = strtolower($this->mimetype);
        if ($this->mimetype == 'image/jpg') {
            $this->mimetype = 'image/jpeg';
        }
        
        
        $explode_mimetype = explode('/', $this->mimetype);
        
        if (array_shift($explode_mimetype) == 'image') { 
        
            $imgs = @getimagesize('data://'. $data);
            
            if (!empty($imgs) && !empty($imgs[0]) && !empty($imgs[1])) {
                list($this->width , $this->height)  = $imgs;
            }
        }
        
        $this->created = date('Y-m-d H:i:s');
        
        if (!$this->id) {
            $this->insert();
        } else {
            $this->update();
        }
        
        $f = $this->getStoreName();
        $dest = dirname($f);
        if (!file_exists($dest)) {
            $oldumask = umask(0);
            mkdir($dest, 0775, true);
            umask($oldumask);  
        }
        
        file_put_contents($f, file_get_contents("data://" . $data));
        //var_dump($f);exit;
        $o = clone($this);
        
        $this->filesize = filesize($f);
        
        if($this->mimetype == 'application/pdf'){
            $this->no_of_pages = $this->getPdfPages($f);
        }
        
        $this->update($o);
        
        return true;
        
    }
    
    function toBase64($rotate = false, $scaleWidth = 0, $scaleHeight = 0)
    {
        if(!preg_match('/^image\//', $this->mimetype)){
            return false;
        }
        
        $file = $this->getStoreName();

        if(!file_exists($file)){
            return false;
        }
        
        $data = file_get_contents($file);
        
        if(!empty($scaleWidth) || !empty($scaleHeight)){
            $data = $this->scale(false, $scaleWidth, $scaleHeight);
        }
        
        if($rotate){
            $data = $this->rotate($data);
        }
        
        $base64 = 'data:' . $this->mimetype . ';base64,' . base64_encode($data);
        
        return $base64;
    }
    
    function getPdfPages($file)
    {
        require_once 'System.php';
        
        $page = 0;

        $pdfinfo = System::which('pdfinfo');

        if (!file_exists($file) || empty($pdfinfo)) {
            return $page;
        }
        
        $cmd = "{$pdfinfo} {$file}";

        $ret = `$cmd`;
        if (empty($ret)) {
            return $page;
        }
        $info = explode("\n", $ret);

        foreach ($info as $i){

            if(!preg_match('/^Pages:[\s]*([0-9]+)/', $i, $matches)){
                continue;
            }
            
            $page = (empty($matches[1])) ? 0 : $matches[1];
        }
        
        return $page;
    }
    
    function rotate($imageBlob = false)
    {
        if(empty($imageBlob)){
            $imagick = new Imagick($this->getStoreName());
        } else {
            $imagick = new Imagick();
            $imagick->readImageBlob($imageBlob);
        }
        
        $orientation = $imagick->getImageOrientation(); 
        
        switch($orientation) { 
            case Imagick::ORIENTATION_BOTTOMRIGHT: 
                $imagick->rotateimage(new ImagickPixel('#00000000'), 180); // rotate 180 degrees 
            break; 

            case Imagick::ORIENTATION_RIGHTTOP: 
                $imagick->rotateimage(new ImagickPixel('#00000000'), 90); // rotate 90 degrees CW 
            break; 

            case Imagick::ORIENTATION_LEFTBOTTOM: 
                $imagick->rotateimage(new ImagickPixel('#00000000'), -90); // rotate 90 degrees CCW 
            break; 
        }
        
        return $imagick->getImageBlob();
    }
    
    function scale($imageBlob = false, $width = 0, $height = 0)
    {
        if(empty($imageBlob)){
            $imagick = new Imagick($this->getStoreName());
        } else {
            $imagick = new Imagick();
            $imagick->readImageBlob($imageBlob);
        }
        
        $imagick->resizeimage($width, $height, Imagick::FILTER_LANCZOS, true, true);
        
        return $imagick->getImageBlob();
        
    }
    
 }

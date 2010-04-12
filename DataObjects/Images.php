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
    
    function createFrom($file)
    {
        // copy the file into the storage area..
        if (!file_exists($file) || !filesize($file)) {
            return false;
        }
        
        
        $imgs = @getimagesize($file);
        
        if (empty($imgs) || empty($imgs[0]) || empty($imgs[1])) {
            // it's a file!!!!
        } else {
            list($this->width , $this->height)  = $imgs;
        }
        
        $this->filesize = filesize($file);
        $this->created = date('Y-m-d H:i:s');
        //DB_DataObject::debugLevel(1);
        if (!$this->id) {
            $this->insert();
        } else {
            $this->update();
        }
        
        
        
        $f = $this->getStoreName();
        $dest = dirname($f);
        if (!file_exists($dest)) {
            
            $oldumask = umask(0);
            mkdir($dest, 0770, true);
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
        $opts = PEAR::getStaticProperty('Pman', 'options');
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
        
        $b = basename($fn);
        $d = dirname($fn);
        $dh = opendir($d);
        while (false !== ($fn = readdir($dh))) {
            if (substr($fn, 0, strlen($b)) == $b) {
                unlink($d. '/'. $fn);
            }
        }
        
        
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
    function onUpload($ctrl)
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
        if (in_array($this->mimetype, array('text/application', 'application/octet-stream'))) { // weird tyeps..
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
            
        
        
        
         
        
        // FIXME - we should be checking perms here...
        //if (method_exists($x, 'checkPerm') && !$x->checkPerm('E', $this->authUser))  {
        //    $this->jerr("PERMISSION DENIED");
        // }
        // this should be doign update
        $this->setFrom($ar);
        
        if (!isset($_FILES['imageUpload'])) {
            return; // standard update...
        }
        
        if ( !$this->onUpload($this)) {
            $this->jerr("File upload failed");
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

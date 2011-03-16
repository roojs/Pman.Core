<?php
/**
 * Table Definition for Images
 */
require_once 'DB/DataObject.php';

class Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Images extends DB_DataObject 
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

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pman_Core_DataObjects_:Pman_Netsuite_DataObjects_Images',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

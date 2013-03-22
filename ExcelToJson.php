<?php

require_once 'Pman/Roo.php';

class Pman_Core_ExcelToJson extends Pman_Roo
{
    function getAuth()
    {
        if (HTML_FlexyFramework::get()->cli) {
            return true;
        }
        return parent::getAuth();
    }
    
    function post()
    {
        $this->transObj = DB_DataObject::Factory('invhist_transfer');
        
        $this->transObj->query('BEGIN');
        print_r('run');exit;
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'onPearError'));
        
        $img = DB_DataObject::Factory('images');
        $img->setFrom(array(
            'onid' => 0,
            'ontable' => 'ipshead'
        ));
        $img->onUpload(false);
        
        require_once 'File/Convert.php';
        $fc = new File_Convert($img->getStoreName(), $img->mimetype );
        $csv = $fc->convert('text/csv');
        $data = $this->importCsv($csv);
        $this->jdata($data);
        
    }
}
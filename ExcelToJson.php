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
        $this->importCsv($csv);
    }
    
    function importCsv($csv)
    {
        ini_set("auto_detect_line_endings", true);
        
        $fh = fopen($csv, 'r');
        if (!$fh) {
            $this->jerr("invalid file");
        }
        
        $req = array(
            'LINE', 'ITEM CODE', 'DESCRIPTION', 'QUANTITY'
        );
        
        $cols = false;
        $rows = array();
        $header = false;
        
        while(false !== ($n = fgetcsv($fh,10000, ',', '"'))) {
            if(!array_filter($n)){
                $header = true;
                continue;
            }
            
            if(!$header){
               $rows[strtoupper($n[0])] = $n[1];
               continue;
            }
            
            if(!$cols){
                $cols = array();
                foreach($n as $k) {
                    $cols[] = strtoupper(trim($k));
                }
                
                if (empty($cols)) {
                    continue;
                }
               
                foreach($req as $r) {
                    if (!in_array($r,$cols)) {
                        $cols = false;
                        break;
                    }
                }
                continue;
            }
            
            foreach($cols as $i=>$k) {
                $row[$k] = $n[$i];
            }
            $rows['TRANSFER ITEMS'][] = $row;
            
        }
        
        if (empty($cols)) {
            $this->jerr("could not find a row with " . implode(' / ', $req));
        }
        
        fclose($fh);
        
        foreach ($rows['TRANSFER ITEMS'] as $r){
            $itemsite = DB_DataObject::factory('itemsite');
            $itemsite->autoJoin();
            print_r($itemsite);exit;
        }
        
        
        
        
        exit;
    }
    
}

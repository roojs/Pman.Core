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
        $data = $this->importCsv($csv);
        $this->jdata($data, false, isset($data['extra']) ? $data['extra'] : array() );
        
    }
    
    function importCsv($csv)
    {
        ini_set("auto_detect_line_endings", true);
        
        $fh = fopen($csv, 'r');
        if (!$fh) {
            $this->jerr("invalid file");
        }
        
         
        $cols = false;
        $header = false;
        $rows = array();
        $ret = array();
        
        while(false !== ($n = fgetcsv($fh,10000, ',', '"'))) {
            if(!array_filter($n)){
                if ($header) {
                    continue;
                }
                $header = true;
                continue;
            }
            
            if(!$header){
               $ret[preg_replace(array('/\s/', '/\:/'), '', $n[0])] = $n[1];
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
               
           
                continue;
            }
            
            foreach($cols as $i=>$k) {
                $row[$k] = $n[$i];
            }
            $rows[] = $row;
            
        }
        fclose($fh);
        
        return array('ret' => $ret, 'rows' => $rows);;
    }
}
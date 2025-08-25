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
    
    function post($v)
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
         $ret = $this->importCsv($csv);
        
       
        
        $this->jdata($ret['data'], false, isset($ret['extra']) ? $ret['extra'] : array() );
        
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
        $extra = array();
        
        while(false !== ($n = fgetcsv($fh,null,",",'"',"\\"))) {
          
            
            if(!strlen(trim(implode('', $n)))){ // blank line;
                if ($header) {
                    continue;
                }
                $header = true;
                continue;
            }
            
            if(!$header){
               $extra[preg_replace(array('/\s/', '/\:/'), '', $n[0])] = $n[1];
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
        
     
        
        return array('extra' => $extra, 'data' => $rows);;
    }
}
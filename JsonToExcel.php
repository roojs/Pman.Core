<?php

/**
 * 
 * MOVE To Core ---
 * 
 * Usage:
 * 
 * POST Values: 
 *  xml = data
 *  format = [empty=xls] , 'gnumeric'  (download format)
 *  debug = true => download xml.
 * 
 * 
 * 
 */

require_once 'Pman.php';
class Pman_Core_JsonToExcel extends Pman
{
    function getAuth()
    {
        $au = $this->getAuthUser();
        if (!$au) {
            die("NOT authenticated");
        }
        $this->authUser = $au;
        return true;
    }

    function get($v, $opts=array())
    {
        $this->jerr("invalid get");
    }
    function post($fname) {
        
        $ml = (int) ini_get('suhosin.post.max_value_length');
        if (empty($_POST['_json'])) {
            header("HTTP/1.0 400 Internal Server Error");
            die(  $ml ? "Suhosin Patch enabled - try and disable it!!!" : 'no JSON sent');
        }
        
        if (empty($_POST['_json'])) {
            header("HTTP/1.0 400 Internal Server Error");
            die("Missing json attribute");
        }
        $json = json_decode($_POST['_json']);
        
        
        require_once 'Spreadsheet/Excel/Writer.php';
        // Creating a workbook
        $outfile2 = $this->tempName('xls');
       // var_dump($outfile2);
        $workbook = new Spreadsheet_Excel_Writer($outfile2);
        //$workbook = new Spreadsheet_Excel_Writer();
        $workbook->setVersion(8);
        // sending HTTP headers
        $worksheet =  $workbook->addWorksheet("Sheet 1");
        if (is_a($worksheet, 'PEAR_Error')) {
            die($worksheet->toString());
        }
        //print_R($worksheet);
        $worksheet->setInputEncoding('UTF-8');
        
        for ($r = 0; $r < count($json); $r++) {
            $row = $json[$r];
            for ($c = 0; $c < count($row); $c++) {
                $worksheet->write($r, $c, $row[$c]);
            }
            
        }
         $workbook->close();
        
        require_once 'File/Convert.php';
        $fc=  new File_Convert($outfile2, "application/vnd.ms-excel");
        $fn = $fc->convert("application/vnd.ms-excel"); 
        $fc->serve('attachment','excel-'.date('Y-m-d-H-i-s').'.xls'); // can fix IE Mess
        unlink($outfile2); 
    }
     
}

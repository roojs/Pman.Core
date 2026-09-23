<?php

/**
 * POST /Core/JsonToExcel/{filename}
 *
 * POST values:
 *  _json       JSON array of arrays (required). First row is typically headers.
 *  _col_widths optional JSON array of Excel character widths, index = column.
 *  _row_bg     optional JSON array of named fill colours, index = row
 *              (silver, yellow, gray, blue, green, red, white, …). No hex.
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

        $colWidths = array();
        if (!empty($_POST['_col_widths'])) {
            $decodedWidths = json_decode($_POST['_col_widths']);
            if (is_array($decodedWidths)) {
                $colWidths = $decodedWidths;
            }
        }
        for ($c = 0; $c < count($colWidths); $c++) {
            if ($colWidths[$c] === '' || $colWidths[$c] === null) {
                continue;
            }
            $worksheet->setColumn($c, $c, $colWidths[$c]);
        }

        $rowBg = array();
        if (!empty($_POST['_row_bg'])) {
            $decodedBg = json_decode($_POST['_row_bg']);
            if (is_array($decodedBg)) {
                $rowBg = $decodedBg;
            }
        }
        $named = array(
            'aqua', 'black', 'blue', 'brown', 'cyan', 'fuchsia', 'gray', 'grey',
            'green', 'lime', 'magenta', 'navy', 'orange', 'purple', 'red',
            'silver', 'white', 'yellow'
        );
        $byColor = array();

        for ($r = 0; $r < count($json); $r++) {
            $row = $json[$r];
            $fmt = false;
            if (isset($rowBg[$r]) && $rowBg[$r] !== '' && $rowBg[$r] !== null) {
                $color = $rowBg[$r];
                if (in_array($color, $named)) {
                    if (!isset($byColor[$color])) {
                        $byColor[$color] = $workbook->addFormat();
                        $byColor[$color]->setFgColor($color);
                    }
                    $fmt = $byColor[$color];
                }
            }
            for ($c = 0; $c < count($row); $c++) {
                if ($fmt) {
                    $worksheet->write($r, $c, $row[$c], $fmt);
                    continue;
                }
                $worksheet->write($r, $c, $row[$c]);
            }
        }
         $workbook->close();
        
        require_once 'File/Convert.php';
        $fc=  new File_Convert($outfile2, "application/vnd.ms-excel");
        $fn = $fc->convert("application/vnd.ms-excel");
        $downloadName = $fname ? $fname : ('excel-' . date('Y-m-d-H-i-s'));
        if (!preg_match('/\.xls$/i', $downloadName)) {
            $downloadName .= '.xls';
        }
        $fc->serve('attachment', $downloadName);
        unlink($outfile2); 
    }
     
}

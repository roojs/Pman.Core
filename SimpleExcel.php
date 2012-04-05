<?php

/**
 * class to generate excel file from rows of data, and a configuration.
 * 
 * cfg:
 *     formats 
 *          name : [ Align : left, .... ]
 *          
 *     workbook : nameof
 *
 *     headdata : [
            [ "a", "b" ]
            
            e : f
        ]
 *     cols :  array(
            array(
                'header'=> "Thumbnail",
                'dataIndex'=> 'id',
                'width'=>  75,
                'renderer' => array($this, 'getThumb')
            ),
            
            
            
            
 */
 
 
 


class Pman_Core_SimpleExcel extends Pman
{
    
    
    
    function Pman_Core_SimpleExcel($data,$cfg)
    {
     //  print_r($cfg);
        require_once 'Spreadsheet/Excel/Writer.php';
        $pman = new Pman();
        // Creating a workbook
        $outfile2 = $pman->tempName('xls');
       // var_dump($outfile2);
        $workbook = new Spreadsheet_Excel_Writer($outfile2);
        //$workbook = new Spreadsheet_Excel_Writer();
        $workbook->setVersion(8);
        // sending HTTP headers
        
        $formats = array();
       
        $cfg['formats'] = isset($cfg['formats']) ? $cfg['formats'] : array();
        foreach($cfg['formats'] as $f=>$fcfg) {
            $formats[$f] = & $workbook->addFormat();
            foreach($fcfg as $k=>$v) {
                $formats[$f]->{'set' . $k}($v);
            }
             
        }
         

        
        // Creating a worksheet
        $worksheet =& $workbook->addWorksheet($cfg['workbook']);
        $worksheet->setInputEncoding('UTF-8'); 
         
         
         
        $start_row = 0;
        
        if (!empty($cfg['head'])) {
            foreach($cfg['head'] as $row) { 
                foreach($row as $c => $col) {
                    $worksheet->write($start_row, $c, $col);
                    
                }
                $start_row++;
            }
            // add a spacer..
            $start_row++;
        }
            
            
            
         
         
         
         
         
        foreach($cfg['cols'] as $c=>$col_cfg) {
            $worksheet->write($start_row, $c, $col_cfg['header']);
            $worksheet->setColumn ( $c, $c, $col_cfg['width'] / 5);
             
        }
           //     DB_DataObject::debugLevel(1);
        foreach($data as $r=>$cl) {
            
            if (isset($cfg['row_height'])) {
                $worksheet->setRow($start_row +1, $cfg['row_height']);
               }
            
            foreach($cfg['cols']  as $c=>$col_cfg) {
                $v = isset($cl[$col_cfg['dataIndex']]) ? $cl[$col_cfg['dataIndex']] : '';
                if (empty($cl[$col_cfg['dataIndex']])) {
                    continue;
                }
                if (isset($col_cfg['txtrenderer'])) {
                    $v = call_user_func($col_cfg['txtrenderer'], 
                            $cl[$col_cfg['dataIndex']], $worksheet, $r+1, $c, $cl);
                    if ($v === false) {
                        continue;
                    }
                  //  var_dump($v);
                }
                if (isset($col_cfg['renderer'])) {
                    continue;
                }
                
                $v = @iconv('UTF-8', 'UTF-8//IGNORE', $v);
                $format = isset($col_cfg['format']) ? $formats[$col_cfg['format']] : false;
                
          //    echo "<PRE>WRITE: ". htmlspecialchars(print_r(array($r+1, $c,$v), true));
                $worksheet->write($start_row+1, $c, $v, $format);
            }
        }
        
        foreach($data as $r=>$cl) {
        
            foreach($cfg['cols']   as $c=>$col_cfg) {
                $v = isset($cl[$col_cfg['dataIndex']]) ? $cl[$col_cfg['dataIndex']] : '';
                if (empty($cl[$col_cfg['dataIndex']])) {
                    continue;
                }
                if (isset($col_cfg['renderer'])) {
                    call_user_func($col_cfg['renderer'], $cl[$col_cfg['dataIndex']], $worksheet, $r+1, $c, $cl);
                    
                }
              //  echo "<PRE>WRITE: ". htmlspecialchars(print_r(array($r+1, $c, $cl[$col_cfg['dataIndex']]), true));
         
            }
        }
           $workbook->close();
        $this->outfile2 = $outfile2;
         
    }
    
    function send($fn)
    {
        
     
       
        require_once 'File/Convert.php';
        $fc=  new File_Convert($this->outfile2, "application/vnd.ms-excel");
        $fn = $fc->convert("application/vnd.ms-excel"); 
        $fc->serve('attachment',$fn); // can fix IE Mess
    }
     
    
}
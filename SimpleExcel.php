<?php

/**
 * class to generate excel file from rows of data, and a configuration.
 *
 * usage :
 *   $x = new Pman_Core_SimpleExcel(array())
 *   $x->send($fn);
 *
 * 
 * cfg:
 *     formats 
 *          name : [ Align : left, .... ]
 *          
 *     workbook : nameof
 *
 *     head  : [
            [ "a", "b" ]
            [],
            [ "A", "B" ]
            [ "a",  ["test", "left"]  ] << sub array [text, formatname]
        ],
 *     cols :  array(
            array(
                'header'=> "Thumbnail",
                'dataIndex'=> 'id',
                'width'=>  75,
                'renderer' => array($this, 'getThumb')
            ),
        
        // if this is set then it will add a tab foreach one.
        workbooks = array(
            workbook ->
            
            
            
 */
 
 
 


class Pman_Core_SimpleExcel extends Pman
{
    
    
    
    function Pman_Core_SimpleExcel($data,$cfg)
    {
       print_r($cfg);
        require_once 'Spreadsheet/Excel/Writer.php';
        // Creating a workbook
        $outfile2 = $this->tempName('xls');
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
         

        if (empty($cfg['workbooks'])) {
            $this->buildpage( $workbook,  $formats , $data,$cfg);
        } else {
            foreach($cfg['workbooks'] as $i =>$wcfg) {
                $this->buildpage( $workbook,  $formats , $data[$i],$wcfg);
            }
            
        }
         
         
         
        
        
        $workbook->close();
        $this->outfile2 = $outfile2;
         
    }
    
    
    static function date($str)
    {
        
        return (strtotime($str . ' UTC') +  (86400 *  25569)) / 86400;
        
    }
    
    
    function buildpage($workbook,  $formats , $data,$cfg)
    {
        //echo '<PRE>';        print_R($cfg);
        
        // Creating a worksheet
        $worksheet =  $workbook->addWorksheet($cfg['workbook']);
        if (is_a($worksheet, 'PEAR_Error')) {
            die($worksheet->toString());
        }
        //print_R($worksheet);
        $worksheet->setInputEncoding('UTF-8'); 
         
         
         
        $start_row = 0;
        
        if (!empty($cfg['head'])) {
            foreach($cfg['head'] as $row) { 
                foreach($row as $c => $col) {
                    if (is_array($col)) {
                        $format = isset($formats[$col[1]] ) ? $formats[$col[1]] : false;
                        $worksheet->write($start_row, $c, $col[0], $format);
                        continue;
                    }
                    
                    $worksheet->write($start_row, $c, $col);
                    
                }
                $start_row++;
            }
            // add a spacer..
            $start_row++;
        }
            
            
            
         
        foreach($cfg['cols'] as $c=>$col_cfg) {
            if (is_string($col_cfg)) {
                $cfg['cols'][$c] = array(
                    'header' => $col_cfg,
                    'dataIndex' => $col_cfg,
                    'width' => 50,
                    
                );
            }
        }
         
         
        foreach($cfg['cols'] as $c=>$col_cfg) {
            
            
            
            $worksheet->write($start_row, $c, $col_cfg['header']);
            $worksheet->setColumn ( $c, $c, $col_cfg['width'] / 5);
             
        }
        $start_row++;
        $hasRender  = false;
           //     DB_DataObject::debugLevel(1);
        foreach($data as $r=>$clo) {
            $cl = $clo;
            if (is_object($clo)) {
                $cl = (array)$clo; // lossless converstion..
            }
            
            if (isset($cfg['row_height'])) {
                $worksheet->setRow($start_row +$r, $cfg['row_height']);
            }
            
            foreach($cfg['cols']  as $c=>$col_cfg) {
                $v = isset($cl[$col_cfg['dataIndex']]) ? $cl[$col_cfg['dataIndex']] : '';
                if (empty($cl[$col_cfg['dataIndex']])) {
                    continue;
                }
                if (isset($col_cfg['txtrenderer'])) {
                    $v = call_user_func($col_cfg['txtrenderer'], 
                            $cl[$col_cfg['dataIndex']], $worksheet, $r+1, $c, $clo);
                    if ($v === false) {
                        continue;
                    }
                  //  var_dump($v);
                }
                if (isset($col_cfg['renderer'])) {
                    $hasRender = true;
                    continue;
                }
                
                $v = @iconv('UTF-8', 'UTF-8//IGNORE', $v);
                $format = isset($col_cfg['format']) ? $formats[$col_cfg['format']] : false;
                
          //    echo "<PRE>WRITE: ". htmlspecialchars(print_r(array($r+1, $c,$v), true));
                $worksheet->write($start_row+$r, $c, $v, $format);
            }
        }
        /// call user render on any that are defined..
        if ($hasRender) {
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
        }
        $start_row += count($data);
        
        if (!empty($cfg['foot'])) {
            foreach($cfg['foot'] as $row) { 
                foreach($row as $c => $col) {
                    // if it's an array? - formated ???
                    if (is_array($col)) {
                        $format = isset($formats[$col[1]] ) ? $formats[$col[1]] : false;
                        $worksheet->write($start_row, $c, $col[0], $format);
                        continue;
                    }
                    $worksheet->write($start_row, $c, $col);
                    
                }
                $start_row++;
            }
            // add a spacer..
            $start_row++;
        }
            
        
        
        
        
    }
    
    function send($fn)
    {
        require_once 'File/Convert.php';
        $fc=  new File_Convert($this->outfile2, "application/vnd.ms-excel");
        $fn = $fc->convert("application/vnd.ms-excel"); 
        $fc->serve('attachment',$fn); // can fix IE Mess
    }
     
    
}
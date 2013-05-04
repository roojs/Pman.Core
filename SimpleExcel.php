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
                'renderer' => array($this, 'getThumb'),
 *              'color' => 'yellow', // set color for the cell which is a header element
 *              'fillBlank' => 'gray', // set the color for the cell which is a blank area
            ),
        
        // if this is set then it will add a tab foreach one.
        workbooks = array(
            workbook ->
            
        'leave_open' => false  
            
 */
 
 
 


class Pman_Core_SimpleExcel extends Pman
{
    
    var $worksheet_cfg = array();
    var $start_row = 0;
    var $formats = array();
    var $workbook = false;
    var $worksheet= false;
    
    function Pman_Core_SimpleExcel($data,$cfg)
    {
      // print_r($cfg);exit;
        require_once 'Spreadsheet/Excel/Writer.php';
        // Creating a workbook
        $outfile2 = $this->tempName('xls');
       // var_dump($outfile2);
        $workbook = new Spreadsheet_Excel_Writer($outfile2);
        //$workbook = new Spreadsheet_Excel_Writer();
        $workbook->setVersion(8);
        // sending HTTP headers
        $this->workbook = $workbook;
            
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
        
        if (!empty($cfg['leave_open'])) {
            $this->outfile2 = $outfile2;
            return;
        }
        
        $workbook->close();
        $this->outfile2 = $outfile2;
         
    }
    
    
    
    
    static function date($str)
    {
        
        return (strtotime($str . ' UTC') +  (86400 *  25569)) / 86400;
        
    }
    
    
    function buildpage($workbook,  $formats , $data, $cfg)
    {
        //echo '<PRE>';        print_R($cfg);
      //  print_r($cfg);exit;
        // Creating a worksheet
        //print_R($cfg);exit;
        // copy the config and alias so that book can be written to..
        $this->worksheet_cfg[$cfg['workbook']] = &$cfg;
        
        $this->formats = $formats;
        
        $worksheet =  $workbook->addWorksheet($cfg['workbook']);
        if (is_a($worksheet, 'PEAR_Error')) {
            die($worksheet->toString());
        }
        //print_R($worksheet);
        $worksheet->setInputEncoding('UTF-8'); 
         
        $this->worksheet = $worksheet;
         
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
            if(!isset($cfg['nonspacer'])){ $start_row++; }
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
            $format = isset($col_cfg['color']) ? $formats[$col_cfg['color']] : false;
            $worksheet->write($start_row, $c, $col_cfg['header'],$format);
            $worksheet->setColumn ( $c, $c, $col_cfg['width'] / 5);
             
        }
        $start_row++;
        $this->start_row = &$start_row;
        
        
        $hasRender  = false;
           //     DB_DataObject::debugLevel(1);
        foreach($data as $r=>$clo) {
            $hasRenderRow = $this->addLine($cfg['workbook'], $clo);
            $hasRender = ($hasRender  || $hasRenderRow) ? true : false;
             
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
                        // not sure if row is correct here...!!!?
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
    
    function addLine($worksheet_name, $clo)
    {
        $cfg        = $this->worksheet_cfg[$worksheet_name];
        $start_row  = $this->start_row;
        $formats    = $this->formats;
        $worksheet  = $this->worksheet;
        
        $hasRender   = false;
        $r = 0;
       
        $cl = $clo;
        if (is_object($clo)) {
            $cl = (array)$clo; // lossless converstion..
        }
        
        if (isset($cfg['row_height'])) {
            $worksheet->setRow($start_row +$r, $cfg['row_height']);
        }
        
        foreach($cfg['cols']  as $c=>$col_cfg) {
            
            if(isset($cl[$col_cfg['dataIndex']])){
                $v = $cl[$col_cfg['dataIndex']];
            }else{
                if(isset($col_cfg['fillBlank'])){
                    $worksheet->write($start_row+$r, $c, '', $formats[$col_cfg['fillBlank']]);
                }
                continue;
            }
            
            if (!isset($cl[$col_cfg['dataIndex']])) {
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
            
            
            $format = isset($col_cfg['format'])  && isset($formats[$col_cfg['format']] )   ? $formats[$col_cfg['format']] : false;
          //  print_R(array($start_row+$r, $c, $v, $format));exit;
          // handle 0 prefixes..
            if (is_numeric($v) &&  strlen($v) > 1 && substr($v,0,1) == '0' && substr($v,1,1) != '.' ) {
                $worksheet->writeString($start_row+$r, $c, $v, $format);
            } else {
          
                $worksheet->write($start_row+$r, $c, $v, $format);
            }
        }
        $this->start_row++;
        
        return $hasRender;
    }
     
    
    function send($fn)
    {
        if (!empty($this->workbook)) {
            $this->workbook->close();
            $this->workbook = false;
        }
        
        require_once 'File/Convert.php';
        $fc=  new File_Convert($this->outfile2, "application/vnd.ms-excel");
        $fn = $fc->convert("application/vnd.ms-excel"); 
        $fc->serve('attachment',$fn); // can fix IE Mess
    }
     
    
}
<?php

/**
 * class to generate excel file from rows of data, and a configuration.
 *
 * usage :
 *   $x = new Pman_Core_SimpleExcel($data_array, array())
 *   $x->send($fn);
 *
 * 
 *
 
new Pman_Core_SimpleExcel($data_array, array(
    'formats' => array(
        'format_name' => array( 'Align' =>  'left' ), // etc...
    ),
    'workbook' => 'name_of_workbook'm
    
    'head' => array(
        array ( 'this', 'is', 'the' , 'first', 'row'), 
        array ( 'this', 'is', 'the' , 'seconde', 'row'),  // ... etc.
        array (  array( 'string', 'format_name') , 'another cell') ,  // with formating..
    ),
   
    'merged_ranges' => array(
                           array($first_row, $first_col, $last_row, $last_col),
                         array($first_row, $first_col, $last_row, $last_col),
    ),
    'cols' =>  array(
            array(
                'header'=> "Thumbnail",
                'dataIndex'=> 'id',
                'dataFormat' => 'string' // to force a string..
                'width'=>  75,
                'renderer' => array($this, 'getThumb'),
                'txtrenderer' => function($value, $worksheet, $row, $col, $row_data) {
                    return $value
                },   // for text content...
                'color' => 'yellow', // set color for the cell which is a header element
                'fillBlank' => 'gray', // set the color for the cell which is a blank area
            ),
            //..... and ther rows...
    ),
    
    // if this is set then it will add a tab foreach one.
    'workbooks' = array(
            workbook => '....' // ???
    ),
    'leave_open' => false,  // if you call addrows?? later..
    'nonspacer' => false, // should add line between head and header row.     
));

    callbacks: renderer
        
        function($value, $worksheet, $row, $col, $row_data)
        
        // callbacks : txtrenderer
        function($value, $worksheet, $row, $col, $row_data)
                        
            
            
 */
 
 
 
require_once 'Pman.php';


class Pman_Core_SimpleExcel extends Pman
{
    
    var $worksheet_cfg = array();
    var $start_row = 0;
    var $formats = array();
    var $workbook = false;
    var $worksheet= false;
    var $postRender = array();
    var $outfile2;
     
    function __construct($data,$cfg)
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
        
        $this->formats['_default_date_format_'] = $workbook->addFormat();;
        $this->formats['_default_date_format_']->setNumFormat('YYYY-MM-DD');
        
        foreach($cfg['formats'] as $f=>$fcfg) {
            
            $this->formats[$f] = & $workbook->addFormat();
            foreach((array)$fcfg as $k=>$v) {
                $this->formats[$f]->{'set' . $k}($v);
            }
            
        }
         
         
        if (!empty($cfg['workbook'])) {
            $this->buildPage(  array(), $data,$cfg);
        } elseif (!empty($cfg['workbooks'])) {
            foreach($cfg['workbooks'] as $i =>$wcfg) {
                $this->buildPage(   array() , $data[$i],$wcfg);
            }
            
        }
        // if workbooks == false - > the user can call buildpage..
        
        
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
    
    
    function buildPage( $formats , $data, $cfg)
    {
        $workbook = $this->workbook;
        //echo '<PRE>';        print_R($cfg);
      //  print_r($cfg);exit;
        // Creating a worksheet
        //print_R($cfg);exit;
        // copy the config and alias so that book can be written to..
        $this->worksheet_cfg[$cfg['workbook']] = &$cfg;
        
        
        //$this->formats = (array)$formats;
        
        foreach($formats as $k=>$fcfg) {
            if (!isset($this->formats[$f])) {
                $this->formats[$f] = & $workbook->addFormat();
            }
            if (is_a($fcfg,'Spreadsheet_Excel_Writer_Format')) {
                continue; // skip!?!?
            }
            // not an object..
            foreach((array)$fcfg as $k=>$v) {
                $this->formats[$f]->{'set' . $k}($v);
            }
        }
        
        if (isset($cfg['formats'])) {
            
            foreach($cfg['formats'] as $f=>$fcfg) {
                if (!isset($this->formats[$f])) {
                    $this->formats[$f] = & $workbook->addFormat();
                }
                foreach((array)$fcfg as $k=>$v) {
                    $this->formats[$f]->{'set' . $k}($v);
                }
                 
            }
            
             
        }
        
        
        
        //var_dump($cfg['workbook']);

        $worksheet =  $workbook->addWorksheet($cfg['workbook']);
        if (is_a($worksheet, 'PEAR_Error')) {
            die($worksheet->toString());
        }
        //print_R($worksheet);
        $worksheet->setInputEncoding('UTF-8'); 
         
        if(!empty($cfg['merged_ranges'])){ // merge cell
            $worksheet->_merged_ranges = $cfg['merged_ranges'];
        }
        
        $this->worksheet = $worksheet;
         
        $start_row = 0;
        
        if (!empty($cfg['head'])) {
            foreach($cfg['head'] as $row) { 
                foreach($row as $c => $col) {
                    if (is_array($col)) {
                        $format = isset($this->formats[$col[1]] ) ?$this->formats[$col[1]] : false;
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
            
            $format = isset($col_cfg['color']) && isset($this->formats[$col_cfg['color']]) ? $this->formats[$col_cfg['color']] : false;
            $worksheet->write($start_row, $c, @$col_cfg['header'],$format);
            $worksheet->setColumn ( $c, $c, $col_cfg['width'] / 5);
        }
        $start_row++;
        $this->start_row = &$start_row;
        
        
        $hasRender  = false;
         
        if (empty($data)) {
            return;
        }
         
        foreach($cfg['cols']  as $c => $col_cfg) {
            if (isset($col_cfg['renderer'])) {
                $hasRender = true;
                break;
            }
        }
        
        
        
        if (is_object($data)) {
            $data_ar = array();
            $count = $data->count();
            $data->find();
            
            while($data->fetch()) {
                $hasRenderRow = $this->addLine($cfg['workbook'], $data);
                $hasRender = ($hasRender  || $hasRenderRow) ? true : false;
            }
            $start_row += $count;
        } else { 
        
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
                        if (!empty($col_cfg['renderer'])) {
                             if (is_a($col_cfg['renderer'], 'Closure')) {
                                $col_cfg['renderer']($cl[$col_cfg['dataIndex']], $worksheet, $r+1, $c, $cl);
                            } else {
                            // not sure if row is correct here...!!!?
                                call_user_func($col_cfg['renderer'], $cl[$col_cfg['dataIndex']], $worksheet, $r+1, $c, $cl);
                            }
                            
                        }
                      //  echo "<PRE>WRITE: ". htmlspecialchars(print_r(array($r+1, $c, $cl[$col_cfg['dataIndex']]), true));
                 
                    }
                }
            }
            $start_row += count($data);

        }
        
        if (!empty($cfg['foot'])) {
            foreach($cfg['foot'] as $row) { 
                foreach($row as $c => $col) {
                    // if it's an array? - formated ???
                    if (is_array($col)) {
                        $format = isset($this->formats[$col[1]] ) ? $this->formats[$col[1]] : false;
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
        $formats    = (array)$this->formats;
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
        
        $line_height = (isset($cfg['line_height'])) ? $cfg['line_height'] : 12;
        $height = 0;
        
        foreach($cfg['cols']  as $c => $col_cfg) {
            
            if(isset($col_cfg['dataIndex']) && isset($cl[$col_cfg['dataIndex']])){
                $v =    $cl[$col_cfg['dataIndex']]  ;
                
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
                
                 if (is_a($col_cfg['txtrenderer'], 'Closure')) {
                     
                    $v =     $col_cfg['txtrenderer']($cl[$col_cfg['dataIndex']], $worksheet, $r+1, $c, $clo);
                } else {
                    $v = call_user_func($col_cfg['txtrenderer'], 
                            $cl[$col_cfg['dataIndex']], $worksheet, $r+1, $c, $clo);
                }
                if ($v === false) {
                    continue;
                }
              //  var_dump($v);
            }
            if (isset($col_cfg['renderer'])) {
                $hasRender = true;
                
                $v = isset($cl[$col_cfg['dataIndex']]) ? $cl[$col_cfg['dataIndex']] : '';
                if (empty($cl[$col_cfg['dataIndex']])) {
                    continue;
                }
                $this->postRender[] = array(
                    $col_cfg['renderer'], $cl[$col_cfg['dataIndex']], $worksheet, $start_row+$r+1, $c, $cl
                );
                  
                
                
                
                continue;
            }
            
            $v = @iconv('UTF-8', 'UTF-8//TRANSLIT//IGNORE', (string)$v);
            
            $dataFormat = empty($col_cfg['dataFormat']) ? '' : $col_cfg['dataFormat'];
              
            
            $format = isset($col_cfg['format'])  && isset($formats[$col_cfg['format']] )   ? $formats[$col_cfg['format']] : false;
          //  print_R(array($start_row+$r, $c, $v, $format));exit;
          // handle 0 prefixes..
          
            if ($dataFormat == 'date' || preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $v)) {
                $dataFormat = 'date';
                $format = empty($format) ? $this->formats['_default_date_format_']: $format;
                $ut_to_ed_diff = 86400 * 25569;
                $gmt = strtotime('1970-01-01');

                $v = (strtotime($v) + $ut_to_ed_diff - $gmt) / 86400;
                // need to +8hrs to get real time..
                
                
            }
          
            if ( (is_numeric($v) &&  strlen($v) > 1 && substr($v,0,1) == '0' && substr($v,1,1) != '.' )
                    || 
                    $dataFormat == 'string' ) {
                //var_dump("Write ( {$r}, {$c} ) = " . $v);
                $worksheet->writeString($start_row+$r, $c, $v, $format);
            } else {
                //var_dump("Write String ( {$r}, {$c} ) = " . $v);
                $worksheet->write($start_row+$r, $c, $v, $format);
            }
            
            if(isset($col_cfg['autoHeight'])){
                $vv = explode("\n", $v);
                $height = MAX(count($vv) * $line_height, $height);;
                $worksheet->setRow($start_row+$r, $height);
            }
        }
        $this->start_row++;
        
        return $hasRender;
    }
     
    
    function send($fname)
    {
        
        if (!empty($this->postRender)) {
            foreach($this->postRender as $ar) {
                 if (is_a($ar[0], 'Closure')) {
                    $ar[0]($ar[1], $ar[2], $ar[3], $ar[4], $ar[5]);
                } else {
                // not sure if row is correct here...!!!?
                    call_user_func($ar[0],$ar[1], $ar[2], $ar[3], $ar[4], $ar[5]);
                }
            }
            
        }
        
        
        if (!empty($this->workbook)) {
            $this->workbook->close();
            $this->workbook = false;
        }
        
        require_once 'File/Convert.php';
        //var_Dump($this->outfile2);
        $fc=  new File_Convert($this->outfile2, "application/vnd.ms-excel");
        $fn = $fc->convert("application/vnd.ms-excel");
        //print_r($fc);
        $fc->serve('attachment',$fname); // can fix IE Mess
    }
     
    
}
